<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;

class UserAddress extends Component
{
    public $selectedUserId; // القيمة المختارة من العنصر select
    public $userAddress;    // العنوان الخاص بالمستخدم المحدد

    // تحديث العنوان بناءً على قيمة المستخدم المختارة
    public function updatedSelectedUserId()
    {
        // جلب عنوان المستخدم المحدد إذا كانت القيمة صحيحة
        $this->userAddress = User::where('id', $this->selectedUserId)
                                 ->value('user_address');
    }

    public function render()
    {
        // جلب المستخدمين كبيانات للعرض في القائمة
        $users = User::all(['id', 'name']);

        return view('livewitransfer.create', compact('users'));
    }
}
