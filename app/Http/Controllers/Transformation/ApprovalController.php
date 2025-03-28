<?php

namespace App\Http\Controllers\Transformation;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\User;
use App\Models\Transfer;
use App\Models\FriendRequest;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Gate;
use App\Services\BalanceService;
use App\Services\FriendService;
class ApprovalController extends Controller
{
    public function create()
    {

        $currencies   = Currency::activeCurrencies();
        $destinations = FriendService::loadDestinations();
        return view('dashboard', compact('currencies', 'destinations'));
    }

    public function storeApproval(Request $request): RedirectResponse
    {
        // التحقق من صلاحيات المستخدم قبل تنفيذ العملية
      

        // التحقق من صحة المدخلات مع استخدام "bail" للتوقف عند أول خطأ
        $validated = $request->validate([
            'destination'   => 'bail|required|integer|exists:users,id',
            'sent_currency' => 'bail|required|string|in:USD,TRY,EUR',
            'sent_amount'   => 'bail|required|numeric|min:0.01',
            'note'          => 'nullable|string|max:500',
        ]);

        // إضافة بعض الحقول الثابتة والمطلوبة لسجل الحوالة
        $validated['user_id']           = Auth::id();
        $totalAmount                    = $validated['sent_amount'];
        $validated['recipient_name']    = 'اعتماد';
        $validated['transaction_type']  = 'Credit';
        $validated['recipient_mobile']  = '0';
        $validated['received_amount']   = '1';
        $validated['received_currency'] = 'TRY';
        $isSender                      = true;

        // التحقق من عدم تجاوز حد الرصيد المسموح به
        if (!BalanceService::checkBalanceLimit(Auth::id(), $validated['sent_currency'], $totalAmount, $isSender)) {
            return redirect()->route('dashboard')
                ->withErrors(['error' => 'تم تجاوز المحدودية المسموح بها.']);
        }

        try {
            // تنفيذ العمليات داخل معاملة واحدة مع إعادة المحاولة لحد أقصى 3 مرات في حال حدوث تعارض
            $transfer = DB::transaction(function () use ($validated) {
                // استخدام خدمة الصداقة للتحقق من وجود علاقة صداقة مقبولة
                $friendRequest = FriendService::checkAcceptedFriendship(Auth::id(), $validated['destination']);

                if (!$friendRequest) {
                    throw new \Exception('لا يمكن إتمام الحوالة. لم يتم العثور على علاقة صداقة مقبولة.');
                }

                // التحقق من حالات الإيقاف الخاصة بالعملية
                if ($friendRequest->sender_id == Auth::id() && !$friendRequest->stop_approval_2) {
                    throw new \Exception('تم إيقاف ,اعتماد. يرجى مراجعة المكتب.');
                }
                if ($friendRequest->receiver_id == Auth::id() && !$friendRequest->stop_approval_1) {
                    throw new \Exception('تم إيقاف اعتماد. يرجى مراجعة المكتب.');
                }

                // إنشاء سجل الحوالة
                return Transfer::create($validated);
            }, 3);
        } catch (\Exception $e) {
            // تسجيل الخطأ دون إفصاح تفاصيل حساسة للمستخدم
            Log::error('خطأ أثناء معالجة الحوالة:', [
                'error'   => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
                'user_id' => Auth::id(),
                'data'    => $validated,
            ]);
            return redirect()->route('dashboard')
    ->withErrors(['error' => $e->getMessage()]);

        }

        // تحويل اسم العملة من الاختصار الإنجليزي إلى الاسم العربي
        $currenciesMapping = [
            'USD' => 'دولار أمريكي',
            'TRY' => 'ليرة تركية',
            'EUR' => 'يورو'
        ];

        // استخراج معلومات الجهة ليتم عرضها في نافذة Swal
        $destinationUser = User::find($validated['destination']);
        $transferDetails = [
            'destination'   => $destinationUser ? $destinationUser->id : 'غير معروف',
            'sent_currency' => $currenciesMapping[$validated['sent_currency']] ?? $validated['sent_currency'],
            'sent_amount'   => $validated['sent_amount'],
            'note'          => $validated['note'] ?? ''
        ];


        return redirect()->route('dashboard')
            ->with('transfer', $transferDetails)
            ->with('message', 'تم إرسال الحوالة بنجاح.');
    }


}
