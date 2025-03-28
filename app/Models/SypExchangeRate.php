<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SypExchangeRate extends Model
{
    use HasFactory;

    // تحديد الأعمدة المسموح تعبئتها (fillable)
    protected $fillable = [
        'currency_name_ar',
        'currency_name_en',
        'exchange_rate_1',
        'exchange_rate_2',
        'is_active',
        'user_id',
        'exchange_rate_start_time' => 'datetime',
    'exchange_rate_end_time' => 'datetime',
    ];

    public function exchangeRate()
    {
        return $this->hasOne(SypExchangeRate::class, 'user_id');
    }
}
