<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wages extends Model
{
    use HasFactory;
    protected $fillable = [
        'type',
        'from_amount',
        'to_amount',
        'fee',
        'currency_id',
        'user_id_1',
        'user_id_2',
    ];

    // علاقة مع جدول العملة
    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    // علاقة مع المستخدم الأول
    public function user1()
    {
        return $this->belongsTo(User::class, 'user_id_1');
    }

    // علاقة مع المستخدم الثاني
    public function user2()
    {
        return $this->belongsTo(User::class, 'user_id_2');
    }
}
