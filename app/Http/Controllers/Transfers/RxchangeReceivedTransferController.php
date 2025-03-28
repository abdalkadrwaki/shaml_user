<?php

namespace App\Http\Controllers\Transfers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Transfer;
use App\Models\FriendRequest;
use App\Services\BalanceServicee; // خدمة تحديث الرصيد

class RxchangeReceivedTransferController extends Controller
{
    /**
     * عرض قائمة الحوالات الواردة (Exchange) مع التصفح.
     */
    public function index(Request $request)
    {
        if (!Auth::check()) {
            abort(403, 'يجب تسجيل الدخول للوصول إلى هذه الصفحة.');
        }

        // استخدام paginate لتحميل مجموعة محدودة من السجلات في كل صفحة
        $receivedTransfers = Transfer::with(['currency', 'sender'])
            ->where('destination', Auth::id())
            ->where('transaction_type', 'Exchange')
            ->whereIn('status', ['Pending', 'Frozen'])
            ->orderBy('created_at', 'desc')
            ->paginate(500);

        return view('transfers.rxchangeReceivedTransfer', compact('receivedTransfers'));
    }

    /**
     * جلب بيانات الحوالات بصيغة JSON.
     */
    public function getTransfersData(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'غير مصرح'], 403);
        }

        // في حال كانت البيانات ضخمة قد يكون من الأفضل استخدام Pagination أو Chunking
        $receivedTransfers = Transfer::with(['currency', 'sender'])
            ->where('destination', Auth::id())
            ->where('transaction_type', 'Exchange')
            ->whereIn('status', ['Pending', 'Frozen'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($receivedTransfers);
    }

    /**
     * حذف (إلغاء) الحوالة.
     */
    public function destroy($id)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'غير مصرح'], 403);
        }

        $transfer = Transfer::findOrFail($id);

        // التأكد من أن المستخدم هو المستلم وأن حالة الحوالة "Pending"
        if ($transfer->destination !== Auth::id() || $transfer->status !== 'Pending') {
            return response()->json(['error' => 'عملية غير مصرح بها'], 403);
        }

        $transfer->delete();

        return response()->json(['success' => true, 'message' => 'تم إلغاء الحوالة بنجاح.']);
    }

    /**
     * جلب تفاصيل الحوالة بصيغة JSON.
     */
    public function getTransferDetails($id)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'غير مصرح'], 403);
        }

        $transfer = Transfer::findOrFail($id);

        if ($transfer->destination !== Auth::id()) {
            return response()->json(['error' => 'غير مصرح'], 403);
        }

        $recipientImage = $transfer->recipient_image ?? '';

        return response()->json([
            'transfer' => $transfer,
            'image'    => $recipientImage
        ]);
    }

    /**
     * التحقق من كلمة المرور للحوالة.
     */
    public function verifyPassword(Transfer $transfer, Request $request)
    {
        Log::debug("بدء التحقق من كلمة المرور للحوالة رقم {$transfer->id}.");

        $validated = $request->validate([
            'password' => 'required|string'
        ]);

        if (!Auth::check()) {
            Log::debug("المستخدم غير مسجل الدخول.");
            return response()->json(['success' => false, 'message' => 'يجب تسجيل الدخول.'], 403);
        }

        if ($transfer->destination !== Auth::id()) {
            Log::debug("المستخدم ليس هو المستلم الصحيح.");
            return response()->json(['success' => false, 'message' => 'غير مصرح لك بهذه الحوالة.'], 403);
        }

        if (in_array($transfer->status, ['Delivered', 'Cancelled'])) {
            Log::debug("الحوالة تم تسليمها أو إلغاؤها مسبقًا.");
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن التحقق من كلمة المرور، لأن الحوالة تم تسليمها أو إلغاؤها مسبقًا.'
            ], 403);
        }

        // أولاً التحقق من كلمة المرور في جدول transfers
        if ($transfer->password === $validated['password']) {
            Log::debug("تم التحقق من كلمة المرور بنجاح من جدول transfers.");
            return response()->json(['success' => true, 'message' => 'تم التحقق من كلمة المرور بنجاح من جدول transfers.']);
        }

        // البحث عن كلمة المرور في جدول friend_requests
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
            Log::debug("تم العثور على سجل في friend_requests.", (array) $friendRequest);

            // اختيار العمود الصحيح بناءً على العلاقة
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
     * تسليم الحوالة مع تحديث رصيد علاقة الصداقة.
     */
    public function deliverTransfer(Transfer $transfer, Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['success' => false, 'message' => 'يجب تسجيل الدخول.'], 403);
        }

        if ($transfer->destination !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'غير مصرح لك بتعديل هذه الحوالة.'], 403);
        }

        if (in_array($transfer->status, ['Delivered', 'Cancelled'])) {
            return response()->json(['success' => false, 'message' => 'لا يمكن تسليم الحوالة لأنها تم تسليمها أو إلغاؤها مسبقًا.'], 403);
        }

        DB::beginTransaction();
        try {
            // إعادة تحميل الحوالة مع قفل الصف لمنع التلاعب أثناء العملية
            $transfer = Transfer::where('id', $transfer->id)
                ->lockForUpdate()
                ->firstOrFail();

            // تحديث حالة الحوالة إلى "Delivered"
            $transfer->update([
                'recipient_info' => 'تم التسليم',
                'status'         => 'Delivered'
            ]);

            // تحديث رصيد علاقة الصداقة
            $friendRequest = FriendRequest::where(function ($query) use ($transfer) {
                $query->where('sender_id', $transfer->user_id)
                      ->where('receiver_id', $transfer->destination);
            })
            ->orWhere(function ($query) use ($transfer) {
                $query->where('receiver_id', $transfer->user_id)
                      ->where('sender_id', $transfer->destination);
            })
            ->where('status', 'accepted')
            ->lockForUpdate()
            ->first();

            if ($friendRequest) {
                // حساب القيم المالية
                $totalAmount   = $transfer->sent_amount;
                $totalReceived = $transfer->received_amount;
                $isSenderInContext = ($friendRequest->sender_id == Auth::id());
                $currency = strtoupper($transfer->sent_currency);
                $received = strtoupper($transfer->received_currency);

                if ($isSenderInContext) {
                    // حالة المستخدم (Auth) كمرسل في FriendRequest
                    $friendRequest->decrement("{$currency}_1", $totalAmount);
                    $friendRequest->increment("{$currency}_2", $totalAmount);
                    $friendRequest->increment("{$received}_1", $totalReceived);
                    $friendRequest->decrement("{$received}_2", $totalReceived);
                } else {
                    // حالة المستخدم (Auth) كمستقبل في FriendRequest
                    $friendRequest->decrement("{$currency}_2", $totalAmount);
                    $friendRequest->increment("{$currency}_1", $totalAmount);
                    $friendRequest->increment("{$received}_2", $totalReceived);
                    $friendRequest->decrement("{$received}_1", $totalReceived);
                }

                // تحديث الرصيد بالدولار بعد تحويل العملة
                BalanceServicee::updateBalanceInUSD(
                    $friendRequest,
                    $transfer->received_currency,
                    $totalReceived, // القيمة الموجبة للمستلمة
                    $isSenderInContext
                );
                BalanceServicee::updateBalanceInUSD(
                    $friendRequest,
                    $transfer->sent_currency,
                    -abs($totalAmount), // القيمة السالبة للعملة المرسلة
                    $isSenderInContext
                );
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'تم تسليم الحوالة بنجاح.']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("فشل التسليم: {$e->getMessage()}", [
                'transfer_id' => $transfer->id,
                'user_id'     => Auth::id(),
                'error_trace' => $e->getTraceAsString()
            ]);

            return response()->json(['success' => false, 'message' => 'حدث خطأ غير متوقع.'], 500);
        }
    }
}
