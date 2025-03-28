<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBroadcastMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
{
    Schema::create('broadcast_messages', function (Blueprint $table) {
        $table->id();
        $table->string('title'); // عنوان الرسالة
        $table->text('content'); // محتوى الرسالة
        $table->boolean('is_active')->default(true); // لتفعيل/تعطيل الرسالة
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
        Schema::dropIfExists('broadcast_messages');
    }
}
