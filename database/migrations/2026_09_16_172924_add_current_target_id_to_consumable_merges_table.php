<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('consumable_merges', function (Blueprint $table) {
            $table->integer('current_target_id')->nullable()->index();
        });
        $targets = DB::table('consumable_merges')->pluck('target_id', 'source_id');
        foreach ($targets as $sourceId => $targetId) {
            $seen = [$sourceId => true];
            while ($targets->has($targetId)) {
                if (isset($seen[$targetId])) {
                    throw new RuntimeException('Circular consumable merge history');
                }
                $seen[$targetId] = true;
                $targetId = $targets->get($targetId);
            }
            DB::table('consumable_merges')->where('source_id', $sourceId)->update(['current_target_id' => $targetId]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('consumable_merges', function (Blueprint $table) {
            $table->dropColumn('current_target_id');
        });
    }
};
