<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlantUserTable extends Migration
{
    public function up()
    {
        Schema::create('plant_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('plant_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['user_id', 'plant_id']); // prevent duplicate pairs
        });
    }

    public function down()
    {
        Schema::dropIfExists('plant_user');
    }
}
