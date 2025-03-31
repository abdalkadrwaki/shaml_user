<?php

namespace App\Http\Controllers\Transformation;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Currency;
use App\Models\Transfer;
use App\Services\BalanceService;
use App\Services\GenerateTransferImageService;
use App\Services\FriendService;
use App\Services\FriendRequestValidator;
use App\Events\UndefinedErrorOccurred;
use Carbon\Carbon;
use App\Models\SypExchangeRate;

class TransfersypController extends Controller
{
    /**
     * عرض صفحة إنشاء الحوالة.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $currencies   = Currency::activeCurrencies();
        $destinations = FriendService::loadDestinations();
        $exchangeRate = null; // أو قيمة افتراضية مناسبة

        return view('dashboard', compact('currencies', 'destinations', 'exchangeRate'));
    }

    /**
     * جلب سعر الصرف بناءً على الجهة المحددة.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getExchangeRate(Request $request)
    {
        $destinationId = $request->input('destination_id');
        $userId        = Auth::id();

        Log::debug("User ID: {$userId} - Destination ID: {$destinationId}");

        // التحقق من علاقة الصداقة وحالة الإيقاف باستخدام الخدمة المخصصة
        try {
            $friendRequest = FriendRequestValidator::validate($destinationId, $userId);
        } catch (\Exception $e) {
            Log::debug($e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }

        // جلب سجل سعر الصرف المُفعل للجهة
        $exchangeRate = SypExchangeRate::where('user_id', $destinationId)
            ->where('is_active', 1)
            ->first();

        if (!$exchangeRate) {
            Log::debug("No active exchange rate found for destination ID: {$destinationId}");
            return response()->json(['success' => false, 'message' => 'تم إيقاف سعر الصرف']);
        }

        // التحقق من وقت توفر الخدمة لسعر الصرف
        $startTime   = Carbon::parse($exchangeRate->exchange_rate_start_time)->format('H:i');
        $endTime     = Carbon::parse($exchangeRate->exchange_rate_end_time)->format('H:i');
        $currentTime = now()->timezone('Asia/Riyadh')->format('H:i');

        if ($currentTime < $startTime || $currentTime > $endTime) {
            Log::debug("Service closed. Current: {$currentTime}, Start: {$startTime}, End: {$endTime}");
            return response()->json(['success' => false, 'message' => 'الخدمة غير متاحة الآن']);
        }

        // تحديد سعر الصرف بناءً على شريحة العلاقة (Slice_type)
        $rate = null;
        if ($friendRequest->Slice_type == 0) {
            $rate = $exchangeRate->exchange_rate_1;
        } elseif ($friendRequest->Slice_type == 1) {
            $rate = $exchangeRate->exchange_rate_2;
        }

        if ($rate !== null) {
            return response()->json([
                'success'         => true,
                'exchange_rate'   => $rate,
                'exchange_rate_1' => $exchangeRate->exchange_rate_1,
                'exchange_rate_2' => $exchangeRate->exchange_rate_2
            ]);
        }

        Log::debug("No matching exchange rate found for user ID: {$userId}");
        return response()->json(['success' => false, 'message' => 'سعر الصرف غير متاح']);
    }

    /**
     * تنفيذ عملية الحوالة مع حساب سعر الصرف من الخادم.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sypstore(Request $request)
    {

        try {
            $validated = $request->validate([
                'recipient_name'     => 'required|string|max:255',
                'recipient_mobile'   => 'required|numeric|regex:/^05\d{8}$/',
                'destination'        => 'required|exists:users,id',
                'sent_currency'      => 'required|in:USD',
                'sent_amount'        => 'required|numeric|min:0.01|max:100000000',
                'received_currency'  => 'required|in:SYP',
                'received_amount'    => 'required|numeric|min:0.01|max:100000000',
                'fees'               => 'nullable|numeric|min:0|max:1000',
                'note'               => 'nullable|string|max:500'
            ]);

            $totalAmount = $validated['sent_amount'] + ($validated['fees'] ?? 0);
            $validated['user_id'] = Auth::id();

            // التحقق من رصيد المستخدم
            if (!BalanceService::checkBalanceLimit(Auth::id(), $validated['sent_currency'], $totalAmount, true)) {
                return response()->json(['error' => 'تجاوز الحد المسموح به للرصيد'], 422);
            }

            DB::beginTransaction();

            // التحقق من علاقة الصداقة وحالة الإيقاف باستخدام الخدمة المخصصة
            try {
                $friendRequest = FriendRequestValidator::validate($validated['destination']);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['error' => $e->getMessage()], 422);
            }

            // جلب سجل سعر الصرف للجهة المُرسلة
            $exchangeRateRecord = SypExchangeRate::where('user_id', $validated['destination'])
                ->where('is_active', 1)
                ->first();

            if (!$exchangeRateRecord) {
                DB::rollBack();
                return response()->json(['error' => 'تم إيقاف سعر الصرف'], 422);
            }

            // التحقق من وقت توفر الخدمة لسعر الصرف
            $startTime   = Carbon::parse($exchangeRateRecord->exchange_rate_start_time)->format('H:i');
            $endTime     = Carbon::parse($exchangeRateRecord->exchange_rate_end_time)->format('H:i');
            $currentTime = now()->timezone('Asia/Riyadh')->format('H:i');

            if ($currentTime < $startTime || $currentTime > $endTime) {
                DB::rollBack();
                return response()->json(['error' => 'الخدمة غير متاحة الآن'], 422);
            }

            // تحديث رصيد العلاقة بين المستخدمين بناءً على العملة
            $currencyColumn1 = strtoupper($validated['sent_currency']) . '_1';
            $currencyColumn2 = strtoupper($validated['sent_currency']) . '_2';

            if ($friendRequest->sender_id == Auth::id() && $friendRequest->receiver_id == $validated['destination']) {
                $friendRequest->increment($currencyColumn1, $totalAmount);
                $friendRequest->decrement($currencyColumn2, $totalAmount);
            } elseif ($friendRequest->receiver_id == Auth::id() && $friendRequest->sender_id == $validated['destination']) {
                $friendRequest->increment($currencyColumn2, $totalAmount);
                $friendRequest->decrement($currencyColumn1, $totalAmount);
            }

            BalanceService::updateBalanceInUSD(
                $friendRequest,
                $validated['sent_currency'],
                $totalAmount,
                (Auth::id() === $friendRequest->sender_id),
                $validated['destination']
            );

            // حساب سعر الصرف بناءً على شريحة العلاقة (Slice_type)
            $computedRate = ($friendRequest->Slice_type == 0)
                ? $exchangeRateRecord->exchange_rate_1
                : $exchangeRateRecord->exchange_rate_2;

            $mslm = $validated['sent_amount'] * $computedRate;

            // إنشاء الحوالة مع القيمة المحسوبة لسعر الصرف
            $transfer = Transfer::create([
                'recipient_name'    => $validated['recipient_name'],
                'recipient_mobile'  => $validated['recipient_mobile'],
                'destination'       => $validated['destination'],
                'sent_currency'     => $validated['sent_currency'],
                'sent_amount'       => $validated['sent_amount'],
                'received_currency' => $validated['received_currency'],
                'received_amount'   => $mslm,
                'fees'              => $validated['fees'] ?? 0,
                'exchange_rate'     => $computedRate,
                'note'              => $validated['note'] ?? null,
                'user_id'           => Auth::id(),
                'password'          => str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT)

            ]);

            // إنشاء صورة إيصال الحوالة
            $imageService = new GenerateTransferImageService();
            $imageData    = $imageService->generateTransferImage($transfer->id);

            DB::commit();

            return response()->json([
                'success'         => true,
                'transfer_id'     => $transfer->id,
                'movement_number' => $transfer->movement_number,
                'recipient_name'  => $transfer->recipient_name,
                $mslm = $transfer->sent_amount,
                'received_currency'   => ' (' . $transfer->currency->name_ar . ')',
                'password'        => $transfer->password,
                'destination'     => optional($transfer->destinationUser)->state_user . ' - ' . optional($transfer->destinationUser)->country_user,
                'Office_name'     => optional($transfer->destinationUser)->Office_name,
                'user_address'    => optional($transfer->destinationUser)->user_address,
                'image_data'      => $imageData,
                'message'         => 'تم إنشاء الحوالة بنجاح'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['error' => 'خطأ في التحقق', 'details' => $e->errors()], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'الجهة غير موجودة'], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Transfer Error: ' . $e->getMessage(), [
                'user'  => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
            event(new UndefinedErrorOccurred($e));
            return response()->json(['error' => 'فشل في المعاملة'], 500);
        }
    }
}
