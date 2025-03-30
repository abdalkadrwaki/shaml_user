<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Currency;
use App\Models\FriendRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class BalanceController extends Controller
{
    public function index()
    {
        $userId = Auth::id();
        abort_unless($userId, 403, 'غير مصرح بالدخول');

        $balances = [];
        $balanceInUsd = 0;

        $currencies = Currency::all();

        foreach ($currencies as $currency) {
            // تحويل اسم العملة إلى صيغة snake_case
            $currencyKey = Str::snake(strtolower($currency->name_en));

            $column1 = "{$currencyKey}_1";
            $column2 = "{$currencyKey}_2";

            // التحقق من وجود الأعمدة في الجدول
            if (!Schema::hasColumn('friend_requests', $column1) ||
                !Schema::hasColumn('friend_requests', $column2)) {
                continue;
            }

            // حساب الرصيد كمرسل
            $senderBalance = FriendRequest::where('receiver_id', $userId)
                ->whereNotNull($column1)
                ->sum($column1);

            // حساب الرصيد كمستقبل
            $receiverBalance = FriendRequest::where('sender_id', $userId)
                ->whereNotNull($column2)
                ->sum($column2);

            $totalBalance = $senderBalance + $receiverBalance;

            if ($totalBalance != 0) {
                $balances[] = [
                    'currency' => $currency,
                    'balance' => $totalBalance,
                    'key' => $currency->name_ar . '_' . $currency->name_en
                ];
            }
        }

        // معالجة رصيد الدولار بشكل منفصل
        $usdSenderBalance = FriendRequest::where('receiver_id', $userId)
            ->whereNotNull('balance_in_usd_2')
            ->sum('balance_in_usd_2');

        $usdReceiverBalance = FriendRequest::where('sender_id', $userId)
            ->whereNotNull('balance_in_usd_1')
            ->sum('balance_in_usd_1');

        $balanceInUsd = $usdSenderBalance + $usdReceiverBalance;

        return view('balances.index', compact('balances', 'balanceInUsd'));
    }
}
