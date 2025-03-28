<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('syp_exchange_rates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');

            $table->string('currency_name_ar')->default('الليرة السورية'); // بدون change()
            $table->string('currency_name_en'); // اسم العملة بالإنجليزية
            $table->decimal('exchange_rate_1', 15, 2);
            $table->decimal('exchange_rate_2', 15, 2);
            $table->time('exchange_rate_start_time')->nullable();
            $table->time('exchange_rate_end_time')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // الفهارس
            $table->index('user_id');
            $table->index('currency_name_en');
            $table->index('currency_name_ar');
            $table->index('is_active');

            // المفتاح الخارجي
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('syp_exchange_rates');
    }
};
