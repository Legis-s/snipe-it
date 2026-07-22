<?php

use Carbon\CarbonImmutable;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const BACKUP_TABLE = 'assets_depreciable_cost_backup_20260722';

    private const QUALITY_MULTIPLIERS = [
        1 => 0.0,
        2 => 0.3,
        3 => 0.5,
        4 => 0.8,
        5 => 1.0,
    ];

    public function up(): void
    {
        Schema::create(self::BACKUP_TABLE, function (Blueprint $table) {
            $table->unsignedInteger('asset_id')->primary();
            $table->decimal('depreciable_cost', 8, 2)->nullable();
        });

        DB::table(self::BACKUP_TABLE)->insertUsing(
            ['asset_id', 'depreciable_cost'],
            DB::table('assets')->select(['id', 'depreciable_cost'])
        );

        $calculatedAt = CarbonImmutable::now();

        DB::table('assets')
            ->leftJoin('models', 'assets.model_id', '=', 'models.id')
            ->leftJoin('depreciations', 'models.depreciation_id', '=', 'depreciations.id')
            ->whereNotNull('assets.purchase_cost')
            ->where('assets.purchase_cost', '>', 0)
            ->whereBetween('assets.quality', [1, 5])
            ->select([
                'assets.id',
                'assets.purchase_cost',
                'assets.purchase_date',
                'assets.quality',
                'depreciations.months as depreciation_months',
            ])
            ->chunkById(500, function ($assets) use ($calculatedAt) {
                foreach ($assets as $asset) {
                    $lifetime = $asset->depreciation_months === null
                        ? 36
                        : max((int) $asset->depreciation_months, 1);
                    $monthsUsed = $asset->purchase_date
                        ? max(
                            (($calculatedAt->year - CarbonImmutable::parse($asset->purchase_date)->year) * 12)
                            + ($calculatedAt->month - CarbonImmutable::parse($asset->purchase_date)->month),
                            0
                        )
                        : 12;
                    $purchaseCost = (float) $asset->purchase_cost;
                    $depreciatedCost = $purchaseCost - (($purchaseCost / $lifetime) * $monthsUsed);
                    $newValue = round(max(
                        $depreciatedCost * self::QUALITY_MULTIPLIERS[(int) $asset->quality],
                        0
                    ), 2);

                    DB::table('assets')->where('id', $asset->id)->update([
                        'depreciable_cost' => $newValue,
                    ]);
                }
            }, 'assets.id', 'id');
    }

    public function down(): void
    {
        if (! Schema::hasTable(self::BACKUP_TABLE)) {
            return;
        }

        DB::table(self::BACKUP_TABLE)
            ->orderBy('asset_id')
            ->chunkById(500, function ($backups) {
                foreach ($backups as $backup) {
                    DB::table('assets')->where('id', $backup->asset_id)->update([
                        'depreciable_cost' => $backup->depreciable_cost,
                    ]);
                }
            }, 'asset_id');

        Schema::drop(self::BACKUP_TABLE);
    }
};
