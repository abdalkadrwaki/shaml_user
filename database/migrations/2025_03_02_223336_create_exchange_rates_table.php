<?php
// database/migrations/2024_05_21_000000_create_exchange_rates_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExchangeRatesTable extends Migration
{
    public function up()
    {
        Schema::create('exchange_rates', function (Blueprint $table) {
            $table->id();
            $table->string('currency_pair')->unique(); // مثال: USD/TRY
            $table->string('name_ar'); // الاسم بالعربية مثل "الدولار الأمريكي/الليرة التركية"
            $table->decimal('buy_rate', 10, 4); // سعر الشراء
            $table->decimal('sell_rate', 10, 4); // سعر البيع
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('exchange_rates');
    }
}
