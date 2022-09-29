<?php

use App\Models\ConsumableAssignment;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ConsumaablesLocationsLog extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('consumables_locations', function ($table) {
            $table->string('type')->nullable();
            $table->text('comment')->nullable();
            $table->string('assigned_type')->nullable();

        });

        // Prior to this migration, asset's could only be assigned to users.
        ConsumableAssignment::whereNotNull('assigned_to')->orWhere('assigned_to', '!=', null)->update(['assigned_type' => \App\Models\Location::class]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('consumables_locations', function ($table) {
            $table->dropColumn('type');
            $table->dropColumn('comment');
            $table->dropColumn('assigned_type');
        });
    }
}
