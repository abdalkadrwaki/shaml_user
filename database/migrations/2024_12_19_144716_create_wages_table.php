<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wages', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->enum('type', ['1', '2'])->comment('نوع التسعير'); // Pricing type
            $table->decimal('from_amount', 15, 2); // Amount from
            $table->decimal('to_amount', 15, 2); // Amount to
            $table->decimal('fee', 15, 2); // Fee
            $table->unsignedBigInteger('currency_id'); // Currency type
            $table->unsignedBigInteger('user_id_1'); // User 1
            $table->unsignedBigInteger('user_id_2'); // User 2
            $table->unsignedBigInteger('parent_id')->nullable(); // Removed after('id')
            $table->string('segment_name'); // عمود "اسم شريحة"
            $table->decimal('wage_amount', 10, 2); // عمود "مبلغ الاجور"
            $table->decimal('ratio', 5, 2); // عمود "نسبة"
            // Foreign key relationships
            $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('cascade');
            $table->foreign('user_id_1')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('user_id_2')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('parent_id')->references('id')->on('users')->onDelete('cascade');

            $table->timestamps(); // Created_at and updated_at columns
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('wages');
    }
}
