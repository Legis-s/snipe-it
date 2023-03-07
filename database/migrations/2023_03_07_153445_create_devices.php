<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDevices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('devices', function (Blueprint $table) {
            $table->increments('id');
            $table->string('number')->nullable();
            $table->integer('mdm_id')->nullable();
            $table->string('statusCode')->nullable();
            $table->text('description')->nullable();
            $table->string('deviceId')->nullable();
            $table->string('info_imei')->nullable();
            $table->integer('batteryLevel')->nullable();
            $table->string('model')->nullable();
            $table->string('imei')->nullable();
            $table->string('launcherVersion')->nullable();
            $table->dateTime('lastUpdate')->nullable();
            $table->integer('asset_id')->unsigned()->nullable();
            $table->foreign('asset_id')->references('id')->on('assets')
                ->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('devices');
    }
}
