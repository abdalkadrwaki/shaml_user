<?php

namespace App\Services;

use App\Models\FriendRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class BalanceServicee
{
    /**
     * التحقق من المحدودية (قيد التحقق من الرصيد).
     */
    public static function checkBalanceLimit($userId, $currency, $amount, $isSender)
    {
        // جلب أسعار الصرف مرة واحدة
        $exchangeRates = ExchangeRateService::getExchangeRates();
        Log::info('تم جلب أسعار الصرف:', ['exchangeRates' => $exchangeRates]);

        try {
            // تحويل المبلغ إلى دولار
            $amountInUSD = ExchangeRateService::convertToUSD($currency, $amount, $exchangeRates);
            Log::info('تم تحويل المبلغ إلى دولار:', ['amountInUSD' => $amountInUSD]);
        } catch (\Exception $e) {
            Log::error('فشل تحويل المبلغ إلى دولار.', [
                'currency'      => $currency,
                'amount'        => $amount,
                'exchangeRates' => $exchangeRates,
                'error'         => $e->getMessage()
            ]);
            return false;
        }

        if ($amountInUSD <= 0) {
            Log::error('قيمة المبلغ المحولة غير صالحة.', [
                'currency'    => $currency,
                'amount'      => $amount,
                'amountInUSD' => $amountInUSD,
            ]);
            return false;
        }

        $friendRequest = FriendRequest::where(function ($query) use ($userId) {
            $query->where('sender_id', $userId)
                  ->orWhere('receiver_id', $userId);
        })
        ->where('status', 'accepted')
        ->first();

        if (!$friendRequest) {
            Log::error('لا توجد علاقة صداقة مقبولة.', [
                'user_id'  => $userId,
                'isSender' => $isSender,
            ]);
            return false;
        }

        if ($userId == $friendRequest->sender_id) {
            $balanceColumn = 'balance_in_usd_2';
            $limitColumn   = 'Limited_2';
            Log::info('المستخدم هو المرسل (sender).', [
                'balanceColumn' => $balanceColumn,
                'limitColumn'   => $limitColumn,
            ]);
        } else {
            $balanceColumn = 'balance_in_usd_1';
            $limitColumn   = 'Limited_1';
        }

        $currentBalance = $friendRequest->$balanceColumn;
        $limit          = $friendRequest->$limitColumn;
        Log::info('الرصيد الحالي والحد الأقصى:', [
            'currentBalance' => $currentBalance,
            'limit'          => $limit,
        ]);

        $totalAllowed = $currentBalance + $limit;
        Log::info('المجموع المسموح به:', ['totalAllowed' => $totalAllowed]);

        if ($amountInUSD <= $totalAllowed) {
            Log::info('المبلغ المرسل ضمن الحد المسموح به.');
            return true;
        } else {
            Log::warning('المبلغ المرسل يتجاوز الحد المسموح به.', [
                'amountInUSD'  => $amountInUSD,
                'totalAllowed' => $totalAllowed,
            ]);
            return false;
        }
    }

    /**
     * تحديث الرصيد بالدولار بعد تحويل قيمة العملة.
     *
     * @param \App\Models\FriendRequest $friendRequest
     * @param string $currency العملة المعنية (مثلاً: USD, EUR, SAR)
     * @param float $amount المبلغ بالعملة الأصلية (يمكن أن يكون سالباً أو موجباً)
     * @param bool $isSender يحدد ما إذا كان المستخدم هو المرسل في علاقة الصداقة
     */
    public static function updateBalanceInUSD($friendRequest, $currency, $amount, $isSender)
    {
        // جلب أسعار الصرف
        $exchangeRates = ExchangeRateService::getExchangeRates();
        try {
            // تحويل المبلغ من العملة المعنية إلى دولار
            $amountInUSD = ExchangeRateService::convertToUSD($currency, $amount, $exchangeRates);

            if ($isSender) {
                // إذا كان المستخدم هو المرسل:
                // - إذا كانت القيمة إيجابية: خصم من balance_in_usd_2 وإضافة إلى balance_in_usd_1
                // - إذا كانت القيمة سالبة: ستنعكس العملية (الخصم يتحول لإضافة والعكس)
                DB::table('friend_requests')
                    ->where('id', $friendRequest->id)
                    ->decrement('balance_in_usd_2', $amountInUSD);
                DB::table('friend_requests')
                    ->where('id', $friendRequest->id)
                    ->increment('balance_in_usd_1', $amountInUSD);
            } else {
                // إذا كان المستخدم هو المستقبل:
                DB::table('friend_requests')
                    ->where('id', $friendRequest->id)
                    ->decrement('balance_in_usd_1', $amountInUSD);
                DB::table('friend_requests')
                    ->where('id', $friendRequest->id)
                    ->increment('balance_in_usd_2', $amountInUSD);
            }

            Log::info('تم تحديث الرصيد بنجاح:', [
                'sender_id'   => $friendRequest->sender_id,
                'receiver_id' => $friendRequest->receiver_id,
                'currency'    => $currency,
                'amountInUSD' => $amountInUSD,
            ]);
        } catch (\Exception $e) {
            Log::error('فشل تحويل المبلغ إلى دولار.', [
                'currency' => $currency,
                'amount'   => $amount,
                'error'    => $e->getMessage(),
            ]);
        }
    }
}
