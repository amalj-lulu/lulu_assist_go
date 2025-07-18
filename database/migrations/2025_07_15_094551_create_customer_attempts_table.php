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
        Schema::create('customer_attempts', function (Blueprint $table) {
            $table->id();
            $table->uuid('customer_id')->nullable();
            $table->string('mobile');
            $table->string('action'); // 'created' or 'existing'
            $table->unsignedBigInteger('performed_by')->nullable(); // can be workstation/user id
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
        Schema::dropIfExists('customer_attempts');
    }
};
