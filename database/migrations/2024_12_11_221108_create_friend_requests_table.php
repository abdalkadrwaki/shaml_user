<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFriendRequestsTable extends Migration
{
    public function up()
    {
        Schema::create('friend_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('receiver_id')->constrained('users')->onDelete('cascade');
            $table->enum('status', ['pending', 'accepted', 'rejected'])->default('pending');

            // قيود الفريدية (Unique constraints)
            $table->unique(['sender_id', 'receiver_id', 'status']);
            $table->unique(['sender_id', 'receiver_id']);

            // الأعمدة الإضافية
            $table->decimal('balance_in_usd_1', 20, 4)->default(0);
            $table->decimal('balance_in_usd_2', 20, 4)->default(0);
            $table->string('Limited_1', 255)->nullable();
            $table->string('Limited_2', 255)->nullable();
            $table->boolean('Stop_movements_1')->default(true);
            $table->boolean('Stop_movements_2')->default(true);
            $table->boolean('account_1')->default(true);
            $table->boolean('account_2')->default(true);
            $table->boolean('stop_approval_1')->default(false);
            $table->boolean('stop_approval_2')->default(false);
            $table->boolean('stop_exchange_1')->default(false);
            $table->boolean('stop_exchange_2')->default(false);
            $table->boolean('hide_account_1')->default(false);
            $table->boolean('hide_account_2')->default(false);
            $table->boolean('stop_link_1')->default(false);
            $table->boolean('stop_link_2')->default(false);
            $table->string('password_usd_1')->nullable();
            $table->string('password_usd_2')->nullable();
            $table->boolean('stop_syp_1')->default(false);
            $table->boolean('stop_syp_2')->default(false);
            $table->tinyInteger('Slice_type_1')->default(1);
            $table->tinyInteger('Slice_type_2')->default(1);
            $table->decimal('syp_price_1', 10, 2)->default(0);
            $table->decimal('syp_price_2', 10, 2)->default(0);
            $table->timestamps();

            // إضافة الفهارس لتحسين أداء الاستعلامات
            $table->index('parent_id');
            $table->index('status');
        });

        // تعريف المفتاح الخارجي للعمود parent_id
        Schema::table('friend_requests', function (Blueprint $table) {
            $table->foreign('parent_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('friend_requests');
    }
}
