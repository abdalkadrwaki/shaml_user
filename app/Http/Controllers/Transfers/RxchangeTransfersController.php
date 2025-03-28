<?php

namespace App\Http\Controllers\Transfers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Transfer;


class RxchangeTransfersController extends Controller
{
    /**
     * عرض قائمة الحوالات الواردة (Exchange) مع التصفح.
     */
    public function index(Request $request)
    {
        $this->authorizeUser();

        $receivedTransfers = Transfer::with(['currency', 'recipient'])
            ->where('user_id', Auth::id())
            ->where('transaction_type', 'Exchange')
            ->whereIn('status', ['Pending', 'Cancelled', 'Delivered'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('transfers.RxchangeTransfers', compact('receivedTransfers'));
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

        $this->authorizeUser();

        if ($transfer->destination !== Auth::id()) {
            Log::debug("المستخدم ليس هو المستلم الصحيح.");
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بهذه الحوالة.'
            ], 403);
        }

        if (in_array($transfer->status, ['Delivered', 'Cancelled'])) {
            Log::debug("الحوالة تم تسليمها أو إلغاؤها مسبقًا.");
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن التحقق من كلمة المرور، لأن الحوالة تم تسليمها أو إلغاؤها مسبقًا.'
            ], 403);
        }

        // التحقق من كلمة المرور في جدول transfers
        if ($transfer->password === $validated['password']) {
            Log::debug("تم التحقق من كلمة المرور بنجاح من جدول transfers.");
            return response()->json([
                'success' => true,
                'message' => 'تم التحقق من كلمة المرور بنجاح من جدول transfers.'
            ]);
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
            Log::debug("تم العثور على سجل في friend_requests.", (array)$friendRequest);

            $passwordToCheck = ($friendRequest->sender_id == $transfer->user_id)
                ? $friendRequest->password_usd_1
                : $friendRequest->password_usd_2;

            Log::debug("كلمة المرور المسترجعة من friend_requests هي: {$passwordToCheck}");

            if (trim($passwordToCheck) === trim($validated['password'])) {
                Log::debug("تم التحقق من كلمة المرور بنجاح من جدول friend_requests.");
                return response()->json([
                    'success' => true,
                    'message' => 'تم التحقق من كلمة المرور بنجاح من جدول friend_requests.'
                ]);
            } else {
                Log::debug("كلمة المرور غير متطابقة مع friend_requests.");
            }
        } else {
            Log::debug("لم يتم العثور على سجل مطابق في friend_requests.");
        }

        Log::warning("فشل التحقق من كلمة المرور للحوالة رقم {$transfer->id} من قبل المستخدم " . Auth::id());
        return response()->json([
            'success' => false,
            'message' => 'كلمة المرور غير صحيحة.'
        ]);
    }

    /**
     * تسليم الحوالة بأمان مع منع التسليم المكرر.
     */
    public function deliverTransfer(Transfer $transfer, Request $request)
    {
        $this->authorizeUser();

        if ($transfer->destination !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بتعديل هذه الحوالة.'
            ], 403);
        }

        if (in_array($transfer->status, ['Delivered', 'Cancelled'])) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن تسليم الحوالة لأنها تم تسليمها أو إلغاؤها مسبقًا.'
            ], 403);
        }

        DB::beginTransaction();
        try {
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

            $transfer->update([
                'recipient_info' => 'تم التسليم',
                'status'         => 'Delivered',
                'statuss'        => 'Delivered'
            ]);

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

    /**
     * استرجاع بيانات الحوالات بصيغة JSON.
     */
    public function getTransfersData()
    {
        $this->authorizeUser();

        $transfers = Transfer::with(['currency', 'recipient'])
            ->where('destination', Auth::id())
            ->where('transaction_type', 'Exchange')
            ->whereIn('status', ['Pending', 'Frozen'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['transfers' => $transfers]);
    }

    /**
     * إلغاء الحوالة (في حالة الانتظار).
     */
    public function destroy($id)
    {
        $this->authorizeUser();

        $transfer = Transfer::findOrFail($id);

        if ($transfer->status !== 'Pending') {
            return redirect()->back()->withErrors('لا يمكن إلغاء هذه الحوالة.');
        }

        if ($transfer->user_id !== Auth::id()) {
            return redirect()->back()->withErrors('غير مصرح لك بتعديل هذه الحوالة.');
        }

        $transfer->update([
            'status'  => 'Cancelled',
            'statuss' => 'Cancelled'
        ]);

        return redirect()->back()->with('success', 'تم إلغاء الحوالة بنجاح.');
    }

    /**
     * عرض صورة الحوالة بصيغة base64.
     */
    public function printImage($id)
    {
        $this->authorizeUser();

        $transfer = Transfer::findOrFail($id);

        if (Auth::id() !== $transfer->destination) {
            return response()->json(['error' => 'غير مصرح لك.'], 403);
        }

        if (!$transfer->image) {
            return response()->json(['error' => 'لا توجد صورة متاحة.'], 404);
        }

        if (!Storage::disk('public')->exists($transfer->image)) {
            return response()->json(['error' => 'ملف الصورة غير موجود.'], 404);
        }

        $imageData   = Storage::disk('public')->get($transfer->image);
        $base64Image = base64_encode($imageData);

        return response()->json(['base64Image' => $base64Image]);
    }

    /**
     * جلب تفاصيل الحوالة لعرضها في النافذة.
     */
    public function getTransferDetails($id)
    {
        $this->authorizeUser();

        $transfer = Transfer::with('recipient')->findOrFail($id);

        if (Auth::id() !== $transfer->destination) {
            return response()->json(['error' => 'غير مصرح لك.'], 403);
        }

        return response()->json([
            'transfer' => $transfer,
            'image'    => ($transfer->recipient && $transfer->recipient->image)
                            ? asset('storage/' . $transfer->recipient->image)
                            : null
        ]);
    }

    /**
     * التحقق من أن المستخدم مسجل الدخول، وإلا يتم رفض الطلب.
     */
    private function authorizeUser()
    {
        if (!Auth::check()) {
            abort(403, 'يجب تسجيل الدخول للوصول إلى هذه الصفحة.');
        }
    }
}
