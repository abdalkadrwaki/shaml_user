<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\User;
class Transfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'recipient_name',
        'recipient_mobile',
        'destination',
        'sent_currency',
        'sent_amount',
        'received_currency',
        'received_amount',
        'fees',
        'exchange_rate',
        'note',
        'user_id',
        'movement_number',
        'status',
        'status',
        'transaction_type',
        'password',
    ];

    // توليد رقم الحركة تلقائيًا
    protected static function booted()
    {
        static::creating(function ($transfer) {
            DB::transaction(function () use ($transfer) {
                $lastMovementNumber = Transfer::lockForUpdate()->orderBy('id', 'desc')->value('movement_number') ?? '0000000000';
                $newMovementNumber = (int) $lastMovementNumber + 1;
                $transfer->movement_number = str_pad($newMovementNumber, 10, '0', STR_PAD_LEFT);
            });
        });
    }
    public function sender()
    {
        // تأكد من أن عمود user_id في جدول transfers يشير إلى id في جدول users
        return $this->belongsTo(User::class, 'user_id');
    }
    public function recipient()
{
    // نفترض أن عمود "destination" في جدول transfers يحتوي على معرف المستخدم (User)
    return $this->belongsTo(User::class, 'destination');
}

public function currency()
{
    return $this->belongsTo(Currency::class, 'sent_currency', 'name_en')
                ->select(['id', 'name_ar', 'name_en']); // أضف name_en هنا
}

public function receivedCurrency()
{
    return $this->belongsTo(Currency::class, 'received_currency', 'name_en')
                ->select(['id', 'name_ar', 'name_en']);
}
    public function destinationUser()
{
    return $this->belongsTo(User::class, 'destination');
}

public function index()
{
    $receivedTransfers = Transfer::where('destination', auth()->id())->get();
    return view('dashboard', compact('receivedTransfers'));
}


public function user()
{
    return $this->belongsTo(User::class);
}


}
