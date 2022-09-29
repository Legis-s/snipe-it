<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddConsumablesMassOperations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('consumable_mass_operation', function (Blueprint $table) {
            $table->integer('mass_operation_id')->unsigned();
            $table->integer('consumable_id')->unsigned();
            $table->foreign('mass_operation_id')->references('id')->on('mass_operations');
            $table->foreign('consumable_id')->references('id')->on('consumables');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('consumable_mass_operation');
    }
}
