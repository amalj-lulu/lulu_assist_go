<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('order_number')->nullable(); // Fill as available
            $table->foreignId('cart_id')->nullable();
            $table->foreignId('order_id')->nullable();
            $table->string('action');         // e.g., 'Creating order', 'Adding serial', etc.
            $table->text('details')->nullable(); // JSON or message
            $table->string('status');         // 'success', 'failed', 'info'
            $table->string('performed_by');
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
        Schema::dropIfExists('order_logs');
    }
};
