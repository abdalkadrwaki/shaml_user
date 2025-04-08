<?php

namespace App\Http\Controllers\Transformation;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Currency;
use App\Models\User;
use App\Models\Transfer;
use App\Models\FriendRequest;
use App\Services\BalanceService;
use App\Services\GenerateTransferImageService;
use App\Services\FriendService;
use App\Jobs\GenerateTransferImageJob;
use App\Events\TransferCountUpdated;
use Carbon\Carbon;
use App\Events\UndefinedErrorOccurred;
// عند تحديث العداد (مثلاً بعد حفظ عملية جديدة في قاعدة البيانات)


class TransferController extends Controller
{
    /**
     * عرض صفحة إنشاء الحوالة.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {


        $currencies = Currency::activeCurrencies();
        $destinations = FriendService::loadDestinations();
        return view('dashboard', compact('currencies', 'destinations'));
    }



    /**
     * جلب عنوان الجهة بناءً على ID الجهة.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDestinationAddress(Request $request)
    {
        $destinationId = $request->input('destination_id');
        $user = User::find($destinationId);

        if ($user) {
            return response()->json(['address' => $user->user_address]);
        } else {
            return response()->json(['address' => null], 404);
        }
    }



    /**
 * تنفيذ عملية الحوالة المالية بعد التحقق من كافة الشروط.
 *
 * @param \Illuminate\Http\Request $request
 * @return \Illuminate\Http\JsonResponse
 */
public function store(Request $request)
{
    // التحقق من صحة البيانات الواردة
    $validated = $request->validate([
        'recipient_name'     => 'required|string|max:255',
        'recipient_mobile'   => 'required|numeric|digits_between:1,10',
        'destination'        => 'required|exists:users,id',
        'sent_currency'      => 'required|in:USD,TRY,EUR,SAR,SYP',
        'sent_amount'        => 'required|numeric|min:0.01|max:100000000000',
        'received_currency'  => 'required|in:USD,TRY,EUR,SAR,SYP',
        'received_amount'    => 'required|numeric|min:0.01|max:100000000000',
        'fees'               => 'nullable|numeric|min:0|max:1000',
        'exchange_rate'      => 'nullable|numeric|min:0',
        'note'               => 'nullable|string|max:500'
    ]);

    // حساب المبلغ الكلي يشمل المبلغ المرسل والعمولة (fees) في حالة وجودها
    $fees = $validated['fees'] ?? 0;
    // لنستخدم حساب دقيق (يمكن استخدام BC Math في حال كانت العمليات الحسابية ذات متطلبات دقة عالية)
    $totalAmount = $validated['sent_amount'] + $fees;

    // التأكد من وجود رصيد كاف قبل تنفيذ العملية
    if (!BalanceService::checkBalanceLimit(
        Auth::id(),
        $validated['sent_currency'],
        $totalAmount,
        true
    )) {
        return response()->json(['error' => 'تجاوز الحد المسموح به للرصيد'], 422);
    }

    try {
        // تنفيذ كافة العمليات داخل معاملة واحدة بحيث إذا فشلت أي عملية يتم التراجع عن جميع العمليات
        $result = DB::transaction(function () use ($validated, $totalAmount) {
            // إضافة معرف المستخدم إلى البيانات للتحقق منه لاحقاً
            $validated['user_id'] = Auth::id();

            // الحصول على طلب الصداقة المشترك وقفل الصف (lockForUpdate) لتفادي مشاكل التزامن
            $friendRequest = FriendRequest::where(function ($query) use ($validated) {
                    $query->where('sender_id', Auth::id())
                          ->where('receiver_id', $validated['destination']);
                })
                ->orWhere(function ($query) use ($validated) {
                    $query->where('receiver_id', Auth::id())
                          ->where('sender_id', $validated['destination']);
                })
                ->where('status', 'accepted')
                ->lockForUpdate()
                ->firstOrFail();

            // التحقق من إيقاف الحوالات بحسب إعدادات الصداقة
            if (
                ($friendRequest->sender_id == Auth::id() && !$friendRequest->Stop_movements_2) ||
                ($friendRequest->receiver_id == Auth::id() && !$friendRequest->Stop_movements_1)
            ) {
                throw new \Exception('تم إيقاف الحوالة. يرجى مراجعة المكتب.');
            }
            if (
                ($friendRequest->sender_id == Auth::id() && !$friendRequest->stop_syp_2) ||
                ($friendRequest->receiver_id == Auth::id() && !$friendRequest->stop_syp_1)
            ) {
                throw new \Exception('تم إيقاف العملة السورية. يرجى مراجعة المكتب.');
            }

            // تحديث رصيد الحوالة عند الصديق
            $currencyColumn1 = strtoupper($validated['sent_currency']) . '_1';
            $currencyColumn2 = strtoupper($validated['sent_currency']) . '_2';

            if ($friendRequest->sender_id == Auth::id() && $friendRequest->receiver_id == $validated['destination']) {
                $friendRequest->increment($currencyColumn1, $totalAmount);
                $friendRequest->decrement($currencyColumn2, $totalAmount);
            } elseif ($friendRequest->receiver_id == Auth::id() && $friendRequest->sender_id == $validated['destination']) {
                $friendRequest->increment($currencyColumn2, $totalAmount);
                $friendRequest->decrement($currencyColumn1, $totalAmount);
            }

            // تحديث الرصيد العام للمستخدم عبر خدمة الحساب
            BalanceService::updateBalanceInUSD(
                $friendRequest,
                $validated['sent_currency'],
                $totalAmount,
                (Auth::id() === $friendRequest->sender_id),
                $validated['destination']
            );

            // إنشاء سجل الحوالة في قاعدة البيانات
            $transfer = Transfer::create([
                'recipient_name'    => $validated['recipient_name'],
                'recipient_mobile'  => $validated['recipient_mobile'],
                'destination'       => $validated['destination'],
                'sent_currency'     => $validated['sent_currency'],
                'sent_amount'       => $validated['sent_amount'],
                'received_currency' => $validated['received_currency'],
                'received_amount'   => $validated['received_amount'],
                'fees'              => $validated['fees'] ?? 0,
                'exchange_rate'     => $validated['exchange_rate'] ?? 1,
                'note'              => $validated['note'] ?? null,
                'user_id'           => Auth::id(),
                'password'          => str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT)
                // بإمكانك إضافة حقول إضافية مثل movement_number إذا لزم الأمر
            ]);

            // توليد صورة الحوالة باستخدام خدمة توليد الصور
            $imageService = new GenerateTransferImageService();
            $imageData    = $imageService->generateTransferImage($transfer->id);

            // التأكد من نجاح عملية توليد الصورة
            if (!$imageData) {
                throw new \Exception('فشل إنشاء صورة الحوالة.');
            }

            // يتم إرجاع البيانات المهمة لمتابعة العملية لاحقاً بعد الـ commit
            return [
                'transfer'  => $transfer,
                'imageData' => $imageData
            ];
        });

        // في حال نجاح العملية يقوم الكود بإرجاع البيانات المطلوبة
        return response()->json([
            'success'         => true,
            'transfer_id'     => $result['transfer']->id,
            'movement_number' => $result['transfer']->movement_number,
            'recipient_name'  => $result['transfer']->recipient_name,
            'sent_amount'     => $result['transfer']->sent_amount,
            'sent_currency'   => ' (' . $result['transfer']->currency->name_ar . ')',
            'password'        => $result['transfer']->password,
            'destination'     => optional($result['transfer']->destinationUser)->state_user . ' - ' . optional($result['transfer']->destinationUser)->country_user,
            'Office_name'     => optional($result['transfer']->destinationUser)->Office_name,
            'user_address'    => optional($result['transfer']->destinationUser)->user_address,
            'receipt_image'   => $result['imageData'],
            'message'         => 'تم إنشاء الحوالة بنجاح'
        ]);
    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json(['error' => 'خطأ في التحقق', 'details' => $e->errors()], 422);
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        return response()->json(['error' => 'الجهة غير موجودة'], 404);
    } catch (\Exception $e) {
        // في حالة حدوث أي خطأ يتم تسجيله وإرجاع رسالة الخطأ المناسبة
        Log::error('Transfer Error: ' . $e->getMessage(), [
            'user'  => Auth::id(),
            'trace' => $e->getTraceAsString()
        ]);
        event(new UndefinedErrorOccurred($e));

        return response()->json(['error' => 'فشل في المعاملة: ' . $e->getMessage()], 500);
    }
}

}
