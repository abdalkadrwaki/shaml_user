<?php

namespace App\Http\Controllers\Transfers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Transfer;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class ReceivedTransferController extends Controller
{


    /**
     * دالة لاسترجاع نسخة الكاش الخاصة بالمستخدم
     */
    protected function getUserTransfersVersion()
    {
        $versionKey = "user:" . Auth::id() . ":transfers_version";
        return Cache::get($versionKey, 1);
    }

    /**
     * دالة لزيادة نسخة الكاش الخاصة بالمستخدم
     */
    protected function incrementUserTransfersVersion()
    {
        $versionKey = "user:" . Auth::id() . ":transfers_version";
        // زيادة النسخة أو تعيينها إلى 1 إذا لم تكن موجودة
        $version = Cache::increment($versionKey);
        if (!$version) {
            Cache::forever($versionKey, 1);
        }
    }

    public function index(Request $request)
    {



        $page = $request->get('page', 1);
        $version = $this->getUserTransfersVersion();
        $cacheKey = "transfers_user_" . Auth::id() . "_v" . $version . "_page_" . $page;

        // استخدام simplePaginate لتفادي استعلام العد الكامل للبيانات
        // بعد جلب الحوالات
        $receivedTransfers = Cache::remember($cacheKey, now()->addMinutes(10), function () {
            return Transfer::with(['currency', 'recipient', 'receivedCurrency'])
                ->where('destination', Auth::id())
                ->where('transaction_type', 'Transfer')
                ->whereIn('status', ['Pending', 'Frozen'])
                ->orderBy('created_at', 'desc')
                ->simplePaginate(100);
        });

        // تجميع الحوالات حسب العملة
        $groupedTransfers = $receivedTransfers->groupBy(function ($transfer) {
            return $transfer->currency ? $transfer->currency->name_ar : $transfer->sent_currency;
        });

        return view('transfers.received', compact('receivedTransfers', 'groupedTransfers'));
    }

    public function toggleFreeze(Transfer $transfer)
    {
        if (!Auth::check()) {
            return response()->json(['success' => false, 'message' => 'يجب تسجيل الدخول.'], 403);
        }

        if ($transfer->destination !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'غير مصرح لك بتعديل هذه الحوالة.'], 403);
        }

        if (in_array($transfer->status, ['Delivered', 'Cancelled'])) {
            return response()->json(['success' => false, 'message' => 'لا يمكن تعديل حالة هذه الحوالة.'], 403);
        }

        // تبديل الحالة بين "Frozen" و"Pending"
        $transfer->status = ($transfer->status === 'Frozen') ? 'Pending' : 'Frozen';
        $transfer->statuss = $transfer->status;
        $transfer->save();

        // زيادة نسخة الكاش لتحديث بيانات الحوالات في الطلبات القادمة
        $this->incrementUserTransfersVersion();

        return response()->json([
            'success' => true,
            'newStatus' => $transfer->status,
        ]);
    }

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

        // يُنصح بتشفير كلمات المرور وعدم تخزينها كنص عادي
        if ($transfer->password === $validated['password']) {
            if (in_array($transfer->status, ['Pending', 'Frozen'])) {
                Log::debug("تم التحقق من كلمة المرور بنجاح.");
                return response()->json(['success' => true, 'message' => 'تم التحقق من كلمة المرور بنجاح.']);
            } else {
                Log::debug("الحوالة تم تسليمها أو إلغاؤها مسبقًا.");
                return response()->json([
                    'success' => false,
                    'message' => 'لا يمكن التحقق من كلمة المرور، لأن الحوالة تم تسليمها أو إلغاؤها مسبقًا.'
                ], 403);
            }
        } else {
            Log::warning("فشل التحقق من كلمة المرور للحوالة رقم {$transfer->id} من قبل المستخدم " . Auth::id());
            return response()->json(['success' => false, 'message' => 'كلمة المرور غير صحيحة.']);
        }
    }

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
            // إعادة تحميل الحوالة مع قفل الصف لتجنب التلاعب أثناء المعاملة
            $transfer = Transfer::where('id', $transfer->id)
                ->lockForUpdate()
                ->first();

            if (in_array($transfer->status, ['Delivered', 'Cancelled'])) {
                Log::debug("الحوالة تم تسليمها أو إلغاؤها مسبقًا.");
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'لا يمكن تسليم الحوالة لأنها تم تسليمها أو إلغاؤها مسبقًا.'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'recipientInfo' => 'required|string|max:255',
                'imageData' => [
                    'required',
                    'string',
                    function ($attribute, $value, $fail) {
                        if (!preg_match('/^data:image\/(\w+);base64,/', $value, $matches)) {
                            $fail('تنسيق الصورة غير صالح.');
                        }
                    }
                ],
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
            }

            $validated = $validator->validated();

            // معالجة الصورة (يمكن نقل العملية إلى Queue إذا كانت ثقيلة)
            if (preg_match('/^data:image\/(\w+);base64,/', $validated['imageData'], $type)) {
                $imageData = substr($validated['imageData'], strpos($validated['imageData'], ',') + 1);
                $extension = strtolower($type[1]); // jpeg, png, gif, ...
                $imageDecoded = base64_decode($imageData);
                if ($imageDecoded === false) {
                    return response()->json(['success' => false, 'message' => 'فشل في فك تشفير الصورة.']);
                }
                if (strlen($imageDecoded) > (2 * 1024 * 1024)) {
                    return response()->json(['success' => false, 'message' => 'حجم الصورة أكبر من الحجم المسموح به.']);
                }
            } else {
                return response()->json(['success' => false, 'message' => 'تنسيق الصورة غير صالح.']);
            }

            // حفظ الصورة في المسار المطلوب
            $fileName = $transfer->movement_number . '.' . $extension;
            $filePath = 'recipient_image/' . $fileName;
            Storage::disk('public')->put($filePath, $imageDecoded);

            // تحديث بيانات الحوالة وتغيير الحالة إلى "Delivered"
            $transfer->recipient_info = strip_tags($validated['recipientInfo']);
            $transfer->status = 'Delivered';
            $transfer->statuss = 'Delivered';
            $transfer->save();

            DB::commit();

            // زيادة نسخة الكاش لتحديث بيانات الحوالات في الطلبات القادمة
            $this->incrementUserTransfersVersion();

            return response()->json(['success' => true, 'message' => 'تم تسليم الحوالة بنجاح.']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("فشل تسليم الحوالة: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'حدث خطأ أثناء تسليم الحوالة.'], 500);
        }
    }
}
