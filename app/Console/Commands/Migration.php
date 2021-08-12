<?php

namespace App\Console\Commands;

use App\Models\ConsumableAssignment;
use App\Models\Purchase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;

class Migration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'snipeit:migration';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create an admin user via command line.';

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

        $purchases = Purchase::where('status', "paid")->whereRaw('LENGTH(consumables_json) > 3')->get();;
        $assigned_type = "App\Models\Purchase";
        foreach ($purchases as $purchase) {
//            $this->info($purchase->consumables_json);
            $consumables =$purchase->consumables;
            if (count($consumables)>0){
                foreach ($consumables as $consumable) {
                    $consumable->locations()->attach($consumable->id, [
                        'consumable_id' => $consumable->id,
                        'user_id' =>$purchase->user_id,
                        'quantity' => $consumable->qty,
                        'cost' => $consumable->purchase_cost,
                        'type' => ConsumableAssignment::PURCHASE,
                        'assigned_to' => $purchase->id,
                        'assigned_type' => $assigned_type,
                        'created_at' => $purchase->created_at,
                        'updated_at' => $purchase->created_at,
                    ]);

                    $this->info($consumable);
                }
            }

        }
    }
}
