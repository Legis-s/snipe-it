<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('purchases')
            ->where(function ($query) {
                $query->whereNull('bitrix_id')
                    ->orWhere('bitrix_id', 0);
            })
            ->update(['status' => 'error']);
    }

    public function down(): void
    {
        // Previous statuses cannot be restored reliably.
    }
};
