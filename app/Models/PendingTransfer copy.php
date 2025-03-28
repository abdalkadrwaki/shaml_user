<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PendingTransfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'currency',
        'amount',
        'status',
        'friend_request_id'
    ];

    // العلاقة مع جدول المستخدمين
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // العلاقة مع جدول طلبات الصداقة (FriendRequest)
    public function friendRequest()
    {
        return $this->belongsTo(FriendRequest::class);
    }
}
