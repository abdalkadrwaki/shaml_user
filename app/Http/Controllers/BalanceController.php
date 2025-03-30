<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Currency;
use App\Models\FriendRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

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
            $currencyName = $currency->name_en;

            if (!Schema::hasColumn('friend_requests', "{$currencyName}_1") ||
                !Schema::hasColumn('friend_requests', "{$currencyName}_2")) {
                continue;
            }

            $senderBalance = FriendRequest::where('receiver_id', $userId)
                ->whereNotNull("{$currencyName}_1")
                ->sum("{$currencyName}_1");

            $receiverBalance = FriendRequest::where('sender_id', $userId)
                ->whereNotNull("{$currencyName}_2")
                ->sum("{$currencyName}_2");

            $totalBalance = $senderBalance + $receiverBalance;

            if ($totalBalance != 0) {
                $balances[] = [
                    'currency' => $currency,
                    'balance' => $totalBalance,
                    'key' => $currency->name_ar . '_' . $currency->name_en
                ];
            }
        }

        // حساب رصيد الدولار
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
