<?php

use App\Models\ConsumableAssignment;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ConsumaablesLocationsLog3 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Prior to this migration, asset's could only be assigned to users.
        ConsumableAssignment::whereNull('type')->update(['type' => "issued"]);

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
