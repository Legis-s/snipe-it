<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMassOperationAssetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('asset_mass_operation', function (Blueprint $table) {
            $table->integer('mass_operation_id')->unsigned();
            $table->integer('asset_id')->unsigned();
            $table->foreign('mass_operation_id')->references('id')->on('mass_operations');
            $table->foreign('asset_id')->references('id')->on('assets');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('asset_mass_operation');
    }
}
