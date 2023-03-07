<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DeviceAddSim extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->integer('asset_sim_id')->unsigned()->nullable();
            $table->foreign('asset_sim_id')->references('id')->on('assets')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('devices', function ($table) {
            $table->dropColumn('asset_sim_id');
            $table->dropForeign(['asset_sim_id']);
        });
    }
}
