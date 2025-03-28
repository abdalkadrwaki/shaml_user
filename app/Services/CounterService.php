<?php

namespace App\Services;

use App\Models\FriendRequest;
use App\Models\Transfer;
use Illuminate\Support\Facades\Auth;

class CounterService
{
    public function getFriendRequestCount()
    {
        $userId = Auth::id();
        return FriendRequest::where('status', 'Pending')
            ->where(function ($query) use ($userId) {
                $query->where('sender_id', $userId)
                      ->orWhere('receiver_id', $userId);
            })
            ->count();
    }

    public function getTransfersCount()
    {
        $userId = Auth::id();
        return Transfer::whereIn('transaction_type', ['Credit', 'Transfer'])
            ->where('status', 'Pending')
            ->where('destination', $userId)
            ->count();
    }


    public function getTransfersCount1()
    {
        $userId = Auth::id();
        return Transfer::whereIn('transaction_type', ['Credit', 'Transfer'])
            ->where('status', 'Pending')
            ->where('transaction_type', 'Transfer')
            ->where('destination', $userId)
            ->count();
    }

    public function getTransfersCount2()
    {
        $userId = Auth::id();
        return Transfer::whereIn('transaction_type', ['Credit', 'Transfer'])
            ->where('status', 'Pending')
            ->where('transaction_type', 'Credit')
            ->where('destination', $userId)
            ->count();
    }

    public function getTransfersCount3()
    {
        $userId = Auth::id();
        return Transfer::whereIn('transaction_type', ['Credit', 'Transfer'])
            ->where('status', 'Pending')
            ->where('transaction_type', 'Exchange')
            ->where('destination', $userId)
            ->count();
    }

}
