<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWageTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wage_types', function (Blueprint $table) {
            $table->id();
            $table->string('segment_name'); // عمود "اسم الشريحة"
            $table->decimal('wage_amount', 10, 2); // عمود "مبلغ الأجر"
            $table->decimal('ratio', 8, 2); // عمود "النسبة"
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('wage_types');
    }
}
