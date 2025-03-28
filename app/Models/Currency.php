<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    use HasFactory;

    protected $table = 'currencies';  // اسم الجدول في قاعدة البيانات

    // الأعمدة القابلة للتعبئة
    protected $fillable = ['name_en', 'name_ar', 'is_active'];

    // استرجاع العملات النشطة
   // في موديل Currency (app/Models/Currency.php)
public static function activeCurrencies()
{
    return self::where('is_active', 1)->get(['name_en', 'name_ar']);
}


}
