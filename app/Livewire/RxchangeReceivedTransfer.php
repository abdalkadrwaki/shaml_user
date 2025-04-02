<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Transfer;
use App\Services\BalanceServicee;

class RxchangeReceivedTransfer extends Component
{
    use WithPagination;

    public $selectedTransferId = null;
    public $selectedTransfer = null;
    public $password = '';
    public $passwordError = '';
    public $wrongAttempts = 0;
    public $maxAttempts = 5;
    public $blockUntil = null;
    public $search = ''; // متغير البحث
    protected $paginationTheme = 'bootstrap';

    protected $listeners = [
        'refreshComponent' => '$refresh',
    ];

    // لتحديث رقم الصفحة عند تغيير البحث
    protected $updatesQueryString = ['search'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function mount()
    {
       
    }

    public function render()
    {
        $receivedTransfersQuery = Transfer::with(['currency', 'sender', 'recipient'])
            ->where('destination', Auth::id())
            ->where('transaction_type', 'Exchange')
            ->whereIn('status', ['Pending', 'Frozen']);

        // تطبيق البحث في بعض الأعمدة (مثلاً: رقم الحركة، اسم المستلم والملاحظة)
        if ($this->search) {
            $receivedTransfersQuery->where(function ($query) {
                $query->where('movement_number', 'like', '%' . $this->search . '%')
                      ->orWhere('recipient_name', 'like', '%' . $this->search . '%')
                      ->orWhere('note', 'like', '%' . $this->search . '%');
            });
        }

        $receivedTransfers = $receivedTransfersQuery
            ->orderBy('created_at', 'desc')
            ->paginate(10); // 10 أسطر في كل صفحة

        // مصفوفة لتحويل الحالة الإنجليزية إلى العربية مع إعدادات اللون
        $statusMapping = [
            'Pending'   => ['text' => 'إنتظار',   'bg' => 'bg-yellow-200', 'textColor' => 'text-yellow-800'],
            'Delivered' => ['text' => 'مسلمة',    'bg' => 'bg-green-200',  'textColor' => 'text-green-800'],
            'Frozen'    => ['text' => 'مجمدة',    'bg' => 'bg-blue-200',   'textColor' => 'text-blue-800'],
            'Cancelled' => ['text' => 'ملغاة',    'bg' => 'bg-red-200',    'textColor' => 'text-red-800'],
        ];

        return view('livewire.rxchange-received-transfer', [
            'receivedTransfers' => $receivedTransfers,
            'statusMapping' => $statusMapping,
        ]);
    }

    public function selectTransfer($transferId)
    {
        if ($this->blockUntil && now()->timestamp < $this->blockUntil) {
            $this->dispatchBrowserEvent('alert', [
                'type' => 'warning',
                'message'  => 'تم حظرك من فتح هذه النافذة لمدة 5 دقائق بسبب محاولات خاطئة.'
            ]);
            return;
        }

        $this->selectedTransfer = Transfer::find($transferId);
        if (!$this->selectedTransfer) {
            $this->dispatchBrowserEvent('alert', [
                'type' => 'error',
                'message'  => 'الحوالة غير موجودة.'
            ]);
            return;
        }

        $this->password = '';
        $this->passwordError = '';
        $this->selectedTransferId = $transferId;
        $this->dispatchBrowserEvent('openDeliveryModall');
    }

    public function verifyAndDeliver()
    {
        if (!$this->password) {
            $this->passwordError = 'يجب إدخال كلمة المرور.';
            return;
        }

        if ($this->blockUntil && now()->timestamp < $this->blockUntil) {
            $this->dispatchBrowserEvent('alert', [
                'type' => 'warning',
                'message'  => 'تم حظرك من استخدام هذه الخاصية لمدة 5 دقائق.'
            ]);
            return;
        }

        if ($this->selectedTransfer->destination !== Auth::id()) {
            $this->dispatchBrowserEvent('alert', [
                'type' => 'error',
                'message'  => 'غير مصرح لك بهذه الحوالة.'
            ]);
            return;
        }

        if (in_array($this->selectedTransfer->status, ['Delivered', 'Cancelled'])) {
            $this->dispatchBrowserEvent('alert', [
                'type' => 'error',
                'message'  => 'لا يمكن التحقق من كلمة المرور، لأن الحوالة تم تسليمها أو إلغاؤها مسبقًا.'
            ]);
            return;
        }

        if ($this->selectedTransfer->password === $this->password) {
            $this->wrongAttempts = 0;
            return $this->deliverTransfer();
        }

        $friendRequest = DB::table('friend_requests')
            ->where(function ($query) {
                $query->where('sender_id', $this->selectedTransfer->user_id)
                      ->where('receiver_id', $this->selectedTransfer->destination);
            })
            ->orWhere(function ($query) {
                $query->where('receiver_id', $this->selectedTransfer->user_id)
                      ->where('sender_id', $this->selectedTransfer->destination);
            })
            ->first();

        if ($friendRequest) {
            $passwordToCheck = ($friendRequest->sender_id == $this->selectedTransfer->user_id)
                ? $friendRequest->password_usd_1
                : $friendRequest->password_usd_2;

            if (trim($passwordToCheck) === trim($this->password)) {
                $this->wrongAttempts = 0;
                return $this->deliverTransfer();
            }
        }

        $this->wrongAttempts++;
        $this->passwordError = 'كلمة المرور خاطئة.';
        $this->password = '';
        $this->dispatchBrowserEvent('alert', [
            'type' => 'error',
            'message' => 'كلمة المرور خاطئة.'
        ]);

        if ($this->wrongAttempts >= $this->maxAttempts) {
            $this->dispatchBrowserEvent('alert', [
                'type' => 'warning',
                'message' => 'لقد تجاوزت عدد المحاولات المسموح بها. سيتم حظرك لمدة 5 دقائق.'
            ]);
            $this->blockUntil = now()->addMinutes(5)->timestamp;
            $this->dispatchBrowserEvent('closeDeliveryModall');
        }
    }

    public function deliverTransfer()
    {
        DB::beginTransaction();
        try {
            $transfer = Transfer::where('id', $this->selectedTransfer->id)
                ->lockForUpdate()
                ->firstOrFail();

            $transfer->update([
                'recipient_info' => 'تم التسليم',
                'status'         => 'Delivered'
            ]);

            $friendRequest = DB::table('friend_requests')
                ->where(function ($query) use ($transfer) {
                    $query->where('sender_id', $transfer->user_id)
                          ->where('receiver_id', $transfer->destination);
                })
                ->orWhere(function ($query) use ($transfer) {
                    $query->where('receiver_id', $transfer->user_id)
                          ->where('sender_id', $transfer->destination);
                })
                ->where('status', 'accepted')
                ->lockForUpdate()
                ->first();

            if ($friendRequest) {
                $totalAmount = $transfer->sent_amount;
                $totalReceived = $transfer->received_amount;
                $isSenderInContext = ($friendRequest->sender_id == Auth::id());
                $currency = strtoupper($transfer->sent_currency);
                $received = strtoupper($transfer->received_currency);

                if ($isSenderInContext) {
                    DB::table('friend_requests')->where('id', $friendRequest->id)->decrement("{$currency}_1", $totalAmount);
                    DB::table('friend_requests')->where('id', $friendRequest->id)->increment("{$currency}_2", $totalAmount);
                    DB::table('friend_requests')->where('id', $friendRequest->id)->increment("{$received}_1", $totalReceived);
                    DB::table('friend_requests')->where('id', $friendRequest->id)->decrement("{$received}_2", $totalReceived);
                } else {
                    DB::table('friend_requests')->where('id', $friendRequest->id)->decrement("{$currency}_2", $totalAmount);
                    DB::table('friend_requests')->where('id', $friendRequest->id)->increment("{$currency}_1", $totalAmount);
                    DB::table('friend_requests')->where('id', $friendRequest->id)->increment("{$received}_2", $totalReceived);
                    DB::table('friend_requests')->where('id', $friendRequest->id)->decrement("{$received}_1", $totalReceived);
                }

                BalanceServicee::updateBalanceInUSD(
                    $friendRequest,
                    $transfer->received_currency,
                    $totalReceived,
                    $isSenderInContext,
                    $transfer->destination
                );
                BalanceServicee::updateBalanceInUSD(
                    $friendRequest,
                    $transfer->sent_currency,
                    -abs($totalAmount),
                    $isSenderInContext,
                    $transfer->destination
                );
            }

            DB::commit();
            $this->dispatchBrowserEvent('alert', [
                'type' => 'success',
                'message' => 'تم تسليم الحوالة بنجاح.'
            ]);
            $this->emit('refreshComponent');
            $this->dispatchBrowserEvent('closeDeliveryModall');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("فشل التسليم: {$e->getMessage()}", [
                'transfer_id'  => $this->selectedTransfer->id,
                'user_id'      => Auth::id(),
                'error_trace'  => $e->getTraceAsString()
            ]);
            $this->dispatchBrowserEvent('alert', [
                'type' => 'error',
                'message' => 'حدث خطأ غير متوقع.'
            ]);
        }
    }
}
