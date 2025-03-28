<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Currency;
use App\Models\FriendRequest;
use App\Models\User;
use App\Models\Transfer;
use Illuminate\Support\Facades\Auth;

class TransferForm extends Component
{
    public $recipient_name;
    public $recipient_mobile;
    public $destination;
    public $user_address; // حقل جديد لعرض العنوان
    public $sent_currency;
    public $sent_amount;
    public $received_currency;
    public $received_amount;
    public $fees = 0;
    public $exchange_rate = 0;
    public $note;

    public $currencies = [];
    public $destinations = [];

    // القواعد للتحقق من المدخلات
    protected $rules = [
        'recipient_name' => 'required|string|max:255',
        'recipient_mobile' => 'required|numeric',
        'destination' => 'required|exists:users,id',
        'sent_currency' => 'required|string',
        'sent_amount' => 'required|numeric|min:0',
        'received_currency' => 'required|string',
        'received_amount' => 'required|numeric|min:0',
        'fees' => 'nullable|numeric|min:0',
        'exchange_rate' => 'nullable|numeric|min:0',
        'note' => 'nullable|string|max:500',
    ];

    // دالة mount لتحميل العملات والجهات
    public function mount()
    {
        // تحميل العملات النشطة
        $this->currencies = Currency::activeCurrencies();

        // جلب الجهات
        $this->loadDestinations();
    }

    // دالة لتحميل الجهات بناءً على علاقات الأصدقاء
    public function loadDestinations()
    {
        $currentUserId = Auth::id();

        $friendRequests = FriendRequest::where(function ($query) use ($currentUserId) {
            $query->where('receiver_id', $currentUserId)
                  ->orWhere('sender_id', $currentUserId);
        })
        ->where('status', 'accepted')
        ->get();

        $userIds = $friendRequests->map(function ($request) use ($currentUserId) {
            return $request->receiver_id === $currentUserId ? $request->sender_id : $request->receiver_id;
        });

        $this->destinations = User::whereIn('id', $userIds)
            ->get(['id', 'name', 'state_user', 'country_user'])
            ->toArray();
    }

    // دالة لتحديث العنوان عند تغيير الجهة المختارة
    public function updatedDestination($value)
    {
        // جلب العنوان بناءً على قيمة destination
        $user = User::find($value);
        $this->user_address = $user ? $user->user_address : '';  // التأكد من وجود العنوان
    }

    // دالة تقديم النموذج
    public function submit()
    {
        // التحقق من المدخلات وإنشاء الحوالة
        $this->validate();

        Transfer::create([
            'recipient_name' => $this->recipient_name,
            'recipient_mobile' => $this->recipient_mobile,
            'destination' => $this->destination,
            'sent_currency' => $this->sent_currency,
            'sent_amount' => $this->sent_amount,
            'received_currency' => $this->received_currency,
            'received_amount' => $this->received_amount,
            'fees' => $this->fees,
            'exchange_rate' => $this->exchange_rate,
            'note' => $this->note,
        ]);

        session()->flash('message', 'تم إرسال الحوالة بنجاح.');

        // إعادة تعيين الحقول بعد الإرسال
        $this->reset([
            'recipient_name',
            'recipient_mobile',
            'sent_amount',
            'received_amount',
            'fees',
            'exchange_rate',
            'note',
            'user_address',  // إعادة تعيين العنوان أيضًا
        ]);
    }

    public function render()
    {
        return view('livewire.transfer-form');
    }
}
