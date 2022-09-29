<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class LocationSklad extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {

            $table->integer('favorite_location_id')->unsigned()->nullable();
            $table->foreign('favorite_location_id')->references('id')->on('locations')
                ->onDelete('cascade');
        });
        Schema::table('locations', function (Blueprint $table) {
            $table->boolean('sklad')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['favorite_location_id']);
            $table->dropColumn('favorite_location_id');
        });
        Schema::table('locations', function (Blueprint $table) {
            $table->dropColumn('sklad');
        });
    }
}
