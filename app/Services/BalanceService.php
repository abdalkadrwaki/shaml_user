<?php

namespace App\Services;

use App\Models\FriendRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class BalanceService
{
    /**
     * التحقق من المحدودية.
     *
     * @param int $userId
     * @param string $currency
     * @param float $amount
     * @param bool $isSender
     * @return bool
     */
    public static function checkBalanceLimit($userId, $currency, $amount, $isSender)
    {
        // جلب أسعار الصرف مرة واحدة وإعادة استخدامها
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

        // جلب طلب الصداقة المقبول
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

        Log::info('تم العثور على طلب صداقة مقبول:', ['friendRequest' => $friendRequest]);

        // تحديد الأعمدة الخاصة بالرصيد والحد بناءً على دور المستخدم
        if ($userId == $friendRequest->sender_id) {
            $balanceColumn = 'balance_in_usd_1';
            $limitColumn   = 'Limited_2';
            Log::info('المستخدم هو المرسل (sender).', [
                'balanceColumn' => $balanceColumn,
                'limitColumn'   => $limitColumn,
            ]);
        } elseif ($userId == $friendRequest->receiver_id) {
            $balanceColumn = 'balance_in_usd_2';
            $limitColumn   = 'Limited_1';
        }

        // جلب الرصيد الحالي والحد الأقصى
        $currentBalance = $friendRequest->$balanceColumn;
        $limit          = $friendRequest->$limitColumn;

        Log::info('الرصيد الحالي والحد الأقصى:', [
            'currentBalance' => $currentBalance,
            'limit'          => $limit,
        ]);

        // حساب المجموع المسموح به
        $totalAllowed = $currentBalance + $limit;
        Log::info('المجموع المسموح به:', ['totalAllowed' => $totalAllowed]);

        // التحقق من أن المبلغ المرسل لا يتجاوز المجموع المسموح به
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
     * تحديث ميزانية الرصيد بالدولار.
     *
     * @param \App\Models\FriendRequest $friendRequest
     * @param string $currency
     * @param float $amount
     * @param bool $isSender
     * @param int $destination
     *   @return void
     */
    public static function updateBalanceInUSD($friendRequest, $currency, $amount, $isSender, $destination)
    {
        // جلب أسعار الصرف مرة واحدة وإعادة استخدامها
        $exchangeRates = ExchangeRateService::getExchangeRates();
        Log::info('تم جلب أسعار الصرف لتحديث الرصيد:', ['exchangeRates' => $exchangeRates]);

        try {
            // تحويل المبلغ إلى دولار
            $amountInUSD = ExchangeRateService::convertToUSD($currency, $amount, $exchangeRates);
            Log::info('تم تحويل المبلغ إلى دولار لتحديث الرصيد:', ['amountInUSD' => $amountInUSD]);
        } catch (\Exception $e) {
            Log::error('فشل تحويل المبلغ إلى دولار لتحديث الرصيد.', [
                'currency'      => $currency,
                'amount'        => $amount,
                'exchangeRates' => $exchangeRates,
                'error'         => $e->getMessage(),
            ]);
            return;
        }

        if ($amountInUSD <= 0) {
            Log::error('قيمة المبلغ المحولة غير صالحة لتحديث الرصيد.', [
                'currency'    => $currency,
                'amount'      => $amount,
                'amountInUSD' => $amountInUSD,
            ]);
            return;
        }

        // تحديث الرصيد بناءً على ما إذا كان المستخدم مرسلًا أو مستلمًا
        if ($isSender) {
            // التصحيح: خصم من المرسل (balance_in_usd_1) وإضافة للمستقبل (balance_in_usd_2)
            $friendRequest->decrement('balance_in_usd_1', $amountInUSD); // -
            $friendRequest->increment('balance_in_usd_2', $amountInUSD); // +
        } else {
            // التصحيح: خصم من المستقبل (balance_in_usd_2) وإضافة للمرسل (balance_in_usd_1)
            $friendRequest->decrement('balance_in_usd_2', $amountInUSD); // -
            $friendRequest->increment('balance_in_usd_1', $amountInUSD); // +
        }

        Log::info('تم تحديث الرصيد بنجاح:', [
            'sender_id'    => $friendRequest->sender_id,
            'receiver_id'  => $friendRequest->receiver_id,
            'amountInUSD'  => $amountInUSD,
        ]);
    }
}
