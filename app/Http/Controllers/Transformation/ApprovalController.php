<?php

namespace App\Http\Controllers\Transformation;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\User;
use App\Models\Transfer;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
        $validated = $request->validate([
            'destination'   => 'bail|required|integer|exists:users,id',
            'sent_currency' => 'bail|required|string|in:USD,TRY,EUR',
            'sent_amount'   => 'bail|required|numeric|min:0.01',
            'note'          => 'nullable|string|max:500',
        ]);

        $userId = Auth::id();
        $destinationId = $validated['destination'];
        $currency = $validated['sent_currency'];
        $amount = $validated['sent_amount'];

        // قبل بدء المعاملة، تحقق من الرصيد بشكل مبدئي
        if (!BalanceService::checkBalanceLimit($userId, $currency, $amount, true)) {
            return redirect()->route('dashboard')
                ->withErrors(['error' => 'تم تجاوز المحدودية المسموح بها.']);
        }

        try {
            DB::beginTransaction();

            // تأمين صف المستخدم بقفل لتجنب التعارضات
            $sender = User::where('id', $userId)->lockForUpdate()->first();
            $recipient = User::where('id', $destinationId)->lockForUpdate()->first();

            if (!$sender || !$recipient) {
                throw new \Exception('تعذر العثور على المستخدمين.');
            }

            // تحقق من الصداقة
            $friendRequest = FriendService::checkAcceptedFriendship($userId, $destinationId);
            if (!$friendRequest) {
                throw new \Exception('لا يمكن إتمام الحوالة. لم يتم العثور على علاقة صداقة مقبولة.');
            }

            if ($friendRequest->sender_id == $userId && !$friendRequest->stop_approval_2) {
                throw new \Exception('تم إيقاف اعتماد من الجهة الأخرى.');
            }

            if ($friendRequest->receiver_id == $userId && !$friendRequest->stop_approval_1) {
                throw new \Exception('تم إيقاف اعتماد من الجهة الأخرى.');
            }

            // تحقق من الرصيد بعد القفل للتأكد أنه لم يتغير أثناء التنفيذ
            if (!BalanceService::checkBalanceLimit($userId, $currency, $amount, true)) {
                throw new \Exception('الرصيد غير كافٍ لإتمام العملية.');
            }

            // إنشاء سجل الحوالة
            $transfer = Transfer::create([
                'user_id'           => $userId,
                'destination'       => $destinationId,
                'sent_currency'     => $currency,
                'sent_amount'       => $amount,
                'note'              => $validated['note'] ?? null,
                'recipient_name'    => 'اعتماد',
                'transaction_type'  => 'Credit',
                'recipient_mobile'  => '0',
                'received_amount'   => '1', // لاحقًا يمكن حساب القيمة الحقيقية عبر خدمة تحويل عملات
                'received_currency' => 'TRY',
            ]);

            // سجل بنجاح، ثبّت المعاملة
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('فشل تنفيذ الحوالة:', [
                'error'   => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
                'user_id' => $userId,
                'data'    => $validated,
            ]);

            return redirect()->route('dashboard')->withErrors(['error' => 'حدث خطأ أثناء تنفيذ الحوالة: ' . $e->getMessage()]);
        }

        $currenciesMapping = [
            'USD' => 'دولار أمريكي',
            'TRY' => 'ليرة تركية',
            'EUR' => 'يورو'
        ];

        $destinationUser = User::find($destinationId);
        $transferDetails = [
            'destination'   => $destinationUser ? $destinationUser->id : 'غير معروف',
            'sent_currency' => $currenciesMapping[$currency] ?? $currency,
            'sent_amount'   => $amount,
            'note'          => $validated['note'] ?? ''
        ];

        return redirect()->route('dashboard')
            ->with('transfer', $transferDetails)
            ->with('message', 'تم إرسال الحوالة بنجاح.');
    }
}
