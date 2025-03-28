<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Currency;
use App\Models\FriendRequest;
use Illuminate\Support\Facades\Auth;

class UserBalances extends Component
{
    public $balances = [];
    public $balance_in_usd_; // تعريف المتغير الخاص برصيد الدولار

    public function mount()
    {
        // جلب جميع العملات من جدول currencies
        $currencies = Currency::all();

        // جلب الـ ID الخاص بالمستخدم الحالي
        $userId = Auth::id();

        // استعلام للحصول على الأرصدة الخاصة بكل عملة
        foreach ($currencies as $currency) {
            $currencyName = $currency->name_en;

            // جلب الرصيد من عمود sender_id
            $senderBalance = FriendRequest::where('receiver_id', $userId)
                ->whereNotNull("{$currencyName}_1")
                ->sum("{$currencyName}_1");

            // جلب الرصيد من عمود receiver_id
            $receiverBalance = FriendRequest::where('sender_id', $userId)
                ->whereNotNull("{$currencyName}_2")
                ->sum("{$currencyName}_2");

            // جمع الأرصدة المرسلة والمستقبلة
            $totalBalance = $senderBalance + $receiverBalance;

            // تخزين الرصيد مع كائن العملة بدون حساب الرصيد بالدولار
            $this->balances[$currency->name_ar . '_' . $currency->name_en] = [
                'currency' => $currency,
                'balance' => $totalBalance,
            ];
        }

        // حساب رصيد الدولار باستخدام نفس الشروط
        $usdSenderBalance = FriendRequest::where('receiver_id', $userId)
            ->whereNotNull('balance_in_usd_2')
            ->sum('balance_in_usd_2');

        $usdReceiverBalance = FriendRequest::where('sender_id', $userId)
            ->whereNotNull('balance_in_usd_1')
            ->sum('balance_in_usd_1');

        $this->balance_in_usd_ = $usdSenderBalance + $usdReceiverBalance;
    }

    public function render()
    {
        return view('livewire.user-balances');
    }
}
