<?php
namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

class TransferCountUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets;

    public $userId;

    public function __construct($userId)
    {
        $this->userId = $userId;
    }

    public function broadcastOn()
    {
        return ['transfer-channel'];
    }

    public function broadcastAs()
    {
        return 'transfer-count-updated';
    }
}
