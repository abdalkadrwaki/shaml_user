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
use Illuminate\Support\Facades\Hash;

class ReceivedTransferController extends Controller
{
    /**
     * استرجاع نسخة الكاش الخاصة بالمستخدم
     */
    protected function getUserTransfersVersion()
    {
        $versionKey = "user:" . Auth::id() . ":transfers_version";
        return Cache::get($versionKey, 1);
    }

    /**
     * زيادة نسخة الكاش الخاصة بالمستخدم
     */
    protected function incrementUserTransfersVersion()
    {
        $versionKey = "user:" . Auth::id() . ":transfers_version";
        $version = Cache::increment($versionKey);
        if (!$version) {
            Cache::forever($versionKey, 1);
        }
    }

    /**
     * عرض الحوالات المستلمة
     */
    public function index(Request $request)
    {
        $groupedTransfers = []; // تعريف المصفوفة

        Transfer::with(['currency', 'recipient', 'receivedCurrency'])
            ->where('destination', Auth::id())
            ->where('transaction_type', 'Transfer')
            ->whereIn('status', ['Pending', 'Frozen'])
            ->orderBy('created_at', 'desc')
            ->chunk(100, function ($transfers) use (&$groupedTransfers) {
                foreach ($transfers as $transfer) {
                    $key = $transfer->currency ? $transfer->currency->name_ar : $transfer->sent_currency;
                    $groupedTransfers[$key][] = $transfer;
                }
            });

        // تحويل المصفوفة إلى مجموعة
        $groupedTransfers = collect($groupedTransfers);

        return view('transfers.received', compact('groupedTransfers'));
    }

    /**
     * تبديل حالة الحوالة بين "Frozen" و"Pending"
     */
    public function toggleFreeze(Transfer $transfer)
    {
        if (!Auth::check() || $transfer->destination !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'غير مصرح لك.'], 403);
        }

        if (in_array($transfer->status, ['Delivered', 'Cancelled'])) {
            return response()->json(['success' => false, 'message' => 'لا يمكن تعديل حالة هذه الحوالة.'], 403);
        }

        $transfer->status = ($transfer->status === 'Frozen') ? 'Pending' : 'Frozen';
        $transfer->save();

        $this->incrementUserTransfersVersion();

        return response()->json(['success' => true, 'newStatus' => $transfer->status]);
    }

    /**
     * التحقق من كلمة المرور للحوالة
     */
    public function verifyPassword(Transfer $transfer, Request $request)
    {
        $validated = $request->validate(['password' => 'required|string']);

        if (!Auth::check() || $transfer->destination !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'غير مصرح لك.'], 403);
        }

        if (in_array($transfer->status, ['Delivered', 'Cancelled'])) {
            return response()->json(['success' => false, 'message' => 'لا يمكن التحقق من كلمة المرور.'], 403);
        }

        if (Hash::check($validated['password'], $transfer->password)) {
            return response()->json(['success' => true, 'message' => 'تم التحقق من كلمة المرور بنجاح.']);
        }

        return response()->json(['success' => false, 'message' => 'كلمة المرور غير صحيحة.']);
    }

    /**
     * تسليم الحوالة
     */
    public function deliverTransfer(Transfer $transfer, Request $request)
    {
        if (!Auth::check() || $transfer->destination !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'غير مصرح لك.'], 403);
        }

        if (in_array($transfer->status, ['Delivered', 'Cancelled'])) {
            return response()->json(['success' => false, 'message' => 'لا يمكن تسليم الحوالة.'], 403);
        }

        DB::transaction(function () use ($transfer, $request) {
            $transfer->lockForUpdate();

            $validator = Validator::make($request->all(), [
                'recipientInfo' => 'required|string|max:255',
                'imageData' => [
                    'required',
                    'string',
                    function ($attribute, $value, $fail) {
                        if (!preg_match('/^data:image\/(\w+);base64,/', $value)) {
                            $fail('تنسيق الصورة غير صالح.');
                        }
                    }
                ],
            ]);

            if ($validator->fails()) {
                throw new \Exception($validator->errors()->first());
            }

            $validated = $validator->validated();

            // معالجة الصورة
            if (preg_match('/^data:image\/(\w+);base64,/', $validated['imageData'], $type)) {
                $imageData = substr($validated['imageData'], strpos($validated['imageData'], ',') + 1);
                $extension = strtolower($type[1]);
                $imageDecoded = base64_decode($imageData);

                if ($imageDecoded === false || strlen($imageDecoded) > (2 * 1024 * 1024)) {
                    throw new \Exception('فشل في فك تشفير الصورة أو حجمها كبير.');
                }

                $fileName = $transfer->movement_number . '.' . $extension;
                $filePath = 'recipient_image/' . $fileName;
                Storage::disk('public')->put($filePath, $imageDecoded);
            }

            $transfer->recipient_info = strip_tags($validated['recipientInfo']);
            $transfer->status = 'Delivered';
            $transfer->save();

            $this->incrementUserTransfersVersion();
        });

        return response()->json(['success' => true, 'message' => 'تم تسليم الحوالة بنجاح.']);
    }
}
