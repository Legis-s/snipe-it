<?php

namespace App\Console\Commands;

use App\Models\Contract;
use App\Models\CustomField;
use App\Models\Inventory;
use App\Models\Supplier;
use App\Models\LegalPerson;
use App\Models\InvoiceType;
use DateTime;
use Exception;

//use False\True;
use Illuminate\Console\Command;
use App\Models\Asset;
use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use stdClass;

class CleanInventory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'snipeit:clean-inventory {--output= : info|warn|error|all} ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This utility will clean inventory';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $output['info'] = [];
        $output['warn'] = [];
        $output['error'] = [];

        $dayBefore = (new DateTime('now'))->format('Y-m-d');


        $inventories = Inventory::with('inventory_items','location')
            ->select([
                'inventories.id',
                'inventories.status',
                'inventories.name',
                'inventories.device',
                'inventories.responsible_id',
                'inventories.responsible',
                'inventories.responsible_photo',
                'inventories.coords',
                'inventories.log',
                'inventories.comment',
                'inventories.location_id',
                'inventories.created_at',
                'inventories.updated_at',
            ])
            ->withCount([
                'inventory_items as total',
                'inventory_items as checked' => function (Builder $query) {
                    $query->where('checked', true);
                },
                'inventory_items as successfully' => function (Builder $query) {
                    $query->where('successfully', true);
                },
            ])
            ->where('inventories.created_at', '<', $dayBefore)
            ->get();

        $to_delete  = 0;
        foreach ($inventories as &$inv) {
            $checked = $inv->checked;
            if ($checked == 0){
                $to_delete++;
                $inv->inventory_items()->forceDelete();
                $inv->forceDelete();
            }
        }
        print("Получено " . count($inventories) . "  инвентаризаций\n");
        print("К удалению  " . $to_delete . "  инвентаризаций\n");

    }
}
