<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class BulkConsumablesAssigmentsAdd extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cons_assignment_mass_operation', function (Blueprint $table) {
            $table->integer('mass_operation_id')->unsigned();
            $table->integer('consumable_assignment_id')->unsigned();
            $table->foreign('mass_operation_id')->references('id')->on('mass_operations');
            $table->foreign('consumable_assignment_id')->references('id')->on('consumables_locations');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cons_assignment_mass_operation');
    }
}
