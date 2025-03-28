<?php

namespace App\Models;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExchangeRate extends Model
{
    use HasFactory;


    protected $fillable = [
        'currency_pair',
        'name_ar',
        'buy_rate',
        'sell_rate'
    ];

    protected static function booted()
{
    static::saved(function () {
        Cache::forget('exchange_rates');
    });

    static::deleted(function () {
        Cache::forget('exchange_rates');
    });
}

}
