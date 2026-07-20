<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('consumables_locations', function (Blueprint $table) {
            $table->index(['assigned_type', 'assigned_to']);
        });
    }

    public function down(): void
    {
        Schema::table('consumables_locations', function (Blueprint $table) {
            $table->dropIndex(['assigned_type', 'assigned_to']);
        });
    }
};
