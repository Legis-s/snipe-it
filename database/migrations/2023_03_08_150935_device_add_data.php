<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DeviceAddData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->string('androidVersion')->nullable();
            $table->string('biometrikaVersion')->nullable();
            $table->string('serial')->nullable();
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
            $table->dropColumn('androidVersion');
            $table->dropColumn('biometrikaVersion');
            $table->dropColumn('serial');
        });
    }
}
