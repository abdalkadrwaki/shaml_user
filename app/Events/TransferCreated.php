<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TransferCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets;

    public $transfer;

    public function __construct($transfer)
    {
        $this->transfer = $transfer;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('App.Models.User.' . $this->transfer->destination);

    }

    public function broadcastWith()
    {
        return [
            'message' => 'تم استلام حوالة جديدة من ' . $this->transfer->user->name,
            'amount' => $this->transfer->sent_amount . ' ' . $this->transfer->sent_currency
        ];
    }
}
