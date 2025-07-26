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
        foreach ($this->existing_table_list() as $table) {
            if (Schema::hasColumn($table, 'user_id')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->renameColumn('user_id', 'created_by');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

        foreach ($this->existing_table_list() as $table) {
            if (Schema::hasColumn($table, 'created_by')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->renameColumn('created_by', 'user_id');
                });
            }
        }
    }

    public function existing_table_list() {
        return [
            'consumables_locations',
            'mass_operations',
            'inventory_status_labels',
        ];
    }
};
