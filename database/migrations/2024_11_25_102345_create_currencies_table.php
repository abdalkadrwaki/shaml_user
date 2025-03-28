<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCurrenciesTable extends Migration
{
    public function up()
    {
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->string('name_ar'); // اسم العملة باللغة العربية
            $table->string('name_en')->unique(); // اسم العملة باللغة الإنجليزية مع فهرس فريد
            $table->boolean('is_active')->default(true); // حالة التفعيل
            $table->timestamps();
        });
    }


    public function down()
    {
        Schema::dropIfExists('currencies');
    }
}
