<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('consumables_locations', function ($table) {
            $table->integer('deal_id')->unsigned()->nullable();
            $table->foreign('deal_id')->references('id')->on('deals');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('consumables_locations', function ($table) {
            $table->dropColumn('deal_id');
            $table->dropForeign(['deal_id']);
        });
    }
};
