<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use App\Models\Transfer;

class TransferSucceeded extends Notification implements ShouldBroadcast
{
    use Queueable;

    public $transfer;

    public function __construct(Transfer $transfer)
    {
        $this->transfer = $transfer;
    }

    // نرسل البيانات إلى الكلاينت
    public function toBroadcast($notifiable)
    {
        return [
            'transfer_id'    => $this->transfer->id,
            'amount'         => $this->transfer->sent_amount,
            'currency'       => $this->transfer->sent_currency,
            'recipient_name' => $this->transfer->recipient_name,
            'message'        => 'تم إنشاء الحوالة بنجاح'
        ];
    }

    // إذا أردت تخزين الإشعار في قاعدة البيانات أيضاً
    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }
}
