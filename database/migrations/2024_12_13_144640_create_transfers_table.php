<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransfersTable extends Migration
{
    public function up()
    {
        Schema::create('transfers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('movement_number', 10)->unique()->default('0000000000');
            $table->string('password')->nullable();
            $table->string('recipient_name');
            $table->string('recipient_mobile');
            $table->unsignedBigInteger('destination');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('sent_currency');
            $table->decimal('sent_amount', 20, 2);
            $table->string('received_currency');
            $table->decimal('received_amount', 20);
            $table->decimal('fees', 20, 2)->default(0);
            $table->decimal('exchange_rate', 20, 4)->default(0);
            $table->enum('status', ['Pending', 'Delivered', 'Frozen', 'Cancelled', 'Archived'])->default('Pending');
            $table->enum('statuss', ['Pending', 'Delivered', 'Frozen', 'Cancelled'])->default('Pending');
            $table->enum('transaction_type', ['Transfer', 'Credit', 'Exchange'])->default('Transfer');
            $table->text('note')->nullable();
            $table->text('recipient_info')->nullable();
            $table->text('Office_name')->nullable();

            $table->timestamps();

            // إضافة الفهارس للأعمدة المهمة
            $table->index('user_id');
            $table->index('destination');
            $table->index('parent_id');
            $table->index('recipient_mobile');
            $table->index('sent_currency');
            $table->index('received_currency');

            // تعريف المفاتيح الخارجية
            $table->foreign('parent_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('destination')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('sent_currency')->references('name_en')->on('currencies')->onDelete('cascade');
            $table->foreign('received_currency')->references('name_en')->on('currencies')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('transfers');
    }
}
