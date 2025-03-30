<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use App\Models\FriendRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;

class UserBalanceController extends Controller
{
    public function dashboard(Request $request)
{
    // تهيئة المتغيرات
    $balances = [];
    $balance_in_usd_ = 0;

    // جلب جميع العملات من جدول currencies
    $currencies = Currency::all();

    // جلب الـ ID الخاص بالمستخدم الحالي
    $userId = Auth::id();

    // استعلام للحصول على الأرصدة الخاصة بكل عملة
    foreach ($currencies as $currency) {
        $currencyName = $currency->name_en;

        // تأكد من أن الأعمدة موجودة قبل تنفيذ الاستعلام
        if (!Schema::hasColumn('friend_requests', "{$currencyName}_1") || !Schema::hasColumn('friend_requests', "{$currencyName}_2")) {
            continue; // تخطي العملة إذا لم تكن الأعمدة موجودة
        }

        $senderBalance = FriendRequest::where('receiver_id', $userId)
            ->whereNotNull("{$currencyName}_1")
            ->sum("{$currencyName}_1");

        $receiverBalance = FriendRequest::where('sender_id', $userId)
            ->whereNotNull("{$currencyName}_2")
            ->sum("{$currencyName}_2");

        $totalBalance = $senderBalance + $receiverBalance;

        $balances[$currency->name_ar . '_' . $currency->name_en] = [
            'currency' => $currency,
            'balance' => $totalBalance,
        ];
    }

    // حساب رصيد الدولار
    $usdSenderBalance = FriendRequest::where('receiver_id', $userId)
        ->whereNotNull('balance_in_usd_2')
        ->sum('balance_in_usd_2');

    $usdReceiverBalance = FriendRequest::where('sender_id', $userId)
        ->whereNotNull('balance_in_usd_1')
        ->sum('balance_in_usd_1');

    $balance_in_usd_ = $usdSenderBalance + $usdReceiverBalance;

    // إرسال المتغيرات إلى الـ View
    return view('dashboard', compact('balances', 'balance_in_usd_'));
}


}
