<?php

namespace App\Http\Controllers\Transfers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Transfer;
use App\Services\BalanceService; // خدمة تحديث الرصيد
use App\Services\FriendService;

class ReceivedTransferApprovalController extends Controller
{
    /**
     * عرض قائمة الحوالات الواردة مع استخدام التصفح لتقليل استهلاك الذاكرة.
     */
    public function index(Request $request)
    {
       

        // يُمكنك إضافة صلاحيات إضافية هنا إذا لزم الأمر

        // استخدام simplePaginate بدلاً من get() لتحميل مجموعة محدودة من البيانات في كل صفحة
        $receivedTransfers = Transfer::with(['currency', 'sender'])
            ->where('destination', Auth::id())
            ->where('transaction_type', 'Credit')
            ->whereIn('status', ['Pending', 'Frozen'])
            ->orderBy('created_at', 'desc')
            ->simplePaginate(1000);

        return view('transfers.receivedapproval', compact('receivedTransfers'));
    }

    /**
     * التحقق من كلمة المرور للحوالة.
     */
    public function verifyPassword(Transfer $transfer, Request $request)
    {
        Log::debug("بدء التحقق من كلمة المرور للحوالة رقم {$transfer->id}.");

        if (!Auth::check()) {
            Log::debug("المستخدم غير مسجل الدخول.");
            return response()->json(['success' => false, 'message' => 'يجب تسجيل الدخول.'], 403);
        }

        if ($transfer->destination !== Auth::id()) {
            Log::debug("المستخدم ليس هو المستلم الصحيح.");
            return response()->json(['success' => false, 'message' => 'غير مصرح لك بهذه الحوالة.'], 403);
        }

        // منع التحقق إذا كانت الحوالة مسلمة أو ملغاة
        if (in_array($transfer->status, ['Delivered', 'Cancelled'])) {
            Log::debug("الحوالة تم تسليمها أو إلغاؤها مسبقًا.");
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن التحقق من كلمة المرور، لأن الحوالة تم تسليمها أو إلغاؤها مسبقًا.'
            ], 403);
        }

        // التحقق من المدخلات
        $validated = $request->validate([
            'password' => 'required|string'
        ]);

        // 1- التحقق من كلمة المرور في جدول transfers
        if ($transfer->password === $validated['password']) {
            Log::debug("تم التحقق من كلمة المرور بنجاح من جدول transfers.");
            return response()->json(['success' => true, 'message' => 'تم التحقق من كلمة المرور بنجاح من جدول transfers.']);
        }

        // 2- البحث عن كلمة المرور في جدول friend_requests
        $friendRequest = DB::table('friend_requests')
            ->where(function ($query) use ($transfer) {
                $query->where('sender_id', $transfer->user_id)
                      ->where('receiver_id', $transfer->destination);
            })
            ->orWhere(function ($query) use ($transfer) {
                $query->where('receiver_id', $transfer->user_id)
                      ->where('sender_id', $transfer->destination);
            })
            ->first();

        if ($friendRequest) {
            Log::debug("تم العثور على سجل في friend_requests.", (array)$friendRequest);

            // تحديد العمود الصحيح لكلمة المرور بناءً على العلاقة بين المستخدمين
            $passwordToCheck = ($friendRequest->sender_id == $transfer->user_id)
                ? $friendRequest->password_usd_1
                : $friendRequest->password_usd_2;

            Log::debug("كلمة المرور المسترجعة من friend_requests هي: {$passwordToCheck}");

            if (trim($passwordToCheck) === trim($validated['password'])) {
                Log::debug("تم التحقق من كلمة المرور بنجاح من جدول friend_requests.");
                return response()->json(['success' => true, 'message' => 'تم التحقق من كلمة المرور بنجاح من جدول friend_requests.']);
            } else {
                Log::debug("كلمة المرور غير متطابقة مع friend_requests.");
            }
        } else {
            Log::debug("لم يتم العثور على سجل مطابق في friend_requests.");
        }

        Log::warning("فشل التحقق من كلمة المرور للحوالة رقم {$transfer->id} من قبل المستخدم " . Auth::id());
        return response()->json(['success' => false, 'message' => 'كلمة المرور غير صحيحة.']);
    }

    /**
     * تسليم الحوالة بأمان مع منع التسليم المكرر وتحديث رصيد علاقة الصداقة.
     */
    public function deliverTransfer(Transfer $transfer, Request $request)
    {
        if (in_array($transfer->status, ['Delivered', 'Cancelled'])) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن تسليم الحوالة لأنها تم تسليمها أو إلغاؤها مسبقًا.'
            ], 403);
        }

        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'يجب تسجيل الدخول.'
            ], 403);
        }

        if ($transfer->destination !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بتعديل هذه الحوالة.'
            ], 403);
        }

        DB::beginTransaction();

        try {
            // إعادة تحميل الحوالة مع قفل الصف لمنع التلاعب أثناء المعاملة
            $transfer = Transfer::where('id', $transfer->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (in_array($transfer->status, ['Delivered', 'Cancelled'])) {
                Log::warning("الحوالة تم تسليمها أو إلغاؤها مسبقًا.");
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'لا يمكن تسليم الحوالة لأنها تم تسليمها أو إلغاؤها مسبقًا.'
                ], 403);
            }

            // تحديث حالة الحوالة إلى "Delivered"
            $transfer->update([
                'recipient_info' => 'تم التسليم',
                'status'         => 'Delivered',
                'statuss'        => 'Delivered'
            ]);

            // التحقق من وجود علاقة صداقة مقبولة بين المستخدمين
            $friendRequest = FriendService::checkAcceptedFriendship(
                $transfer->user_id,      // مرسل الحوالة
                $transfer->destination   // مستقبل الحوالة
            );

            // حساب المبلغ الكلي مع الرسوم
            $totalAmount = $transfer->sent_amount + ($transfer->fees ?? 0);

            // تحديد اتجاه التحديث بناءً على كون المستخدم الحالي هو المرسل أم المستقبل
            $isSenderInContext = ($friendRequest->sender_id == Auth::id());

            // تحديث الرصيد بالعملة الأصلية
            $currency = strtoupper($transfer->sent_currency);
            $currencyColumn1 = $currency . '_1';
            $currencyColumn2 = $currency . '_2';

            if ($isSenderInContext) {
                $friendRequest->increment($currencyColumn1, $totalAmount);
                $friendRequest->decrement($currencyColumn2, $totalAmount);
            } else {
                $friendRequest->increment($currencyColumn2, $totalAmount);
                $friendRequest->decrement($currencyColumn1, $totalAmount);
            }

            // تحديث الرصيد بالدولار مع التصحيح
            BalanceService::updateBalanceInUSD(
                $friendRequest,
                $transfer->sent_currency,
                $totalAmount,
                $isSenderInContext,
                $transfer->destination
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'تم تسليم الحوالة بنجاح.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("فشل التسليم: {$e->getMessage()}", [
                'transfer_id' => $transfer->id,
                'user_id'     => Auth::id(),
                'error_trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => config('app.debug') ? $e->getMessage() : 'حدث خطأ غير متوقع'
            ], 500);
        }
    }
}
