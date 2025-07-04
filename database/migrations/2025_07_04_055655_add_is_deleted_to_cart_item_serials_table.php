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
        Schema::table('cart_item_serials', function (Blueprint $table) {
            $table->boolean('is_deleted')->default(false); // Add is_deleted field
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */

    public function down()
    {
        Schema::table('cart_item_serials', function (Blueprint $table) {
            $table->dropColumn('is_deleted');
        });
    }
};
