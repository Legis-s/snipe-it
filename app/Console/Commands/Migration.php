<?php

namespace App\Console\Commands;

use App\Helpers\Helper;
use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Company;
use App\Models\ConsumableAssignment;
use App\Models\Purchase;
use App\Models\Sale;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
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

        $sales = Sale::where('deleted_at', NULL)->get();
        $this->info(count($sales));
        foreach ($sales as $sale) {
//            $this->info($sale);
            $asset = new Asset();
            $asset->name = $sale->name;
            $asset->serial =  $sale->serial;;
            $asset->model_id =  $sale->model_id;
            $asset->order_number = $sale->order_number;
            $asset->notes = $sale->notes;
            $asset->asset_tag = $sale->asset_tag;
            $asset->user_id =  $sale->user_id;
            $asset->status_id = $sale->status_id;
            $asset->purchase_cost = $sale->purchase_cost;
            $asset->purchase_date = $sale->purchase_date;
            $asset->supplier_id = $sale->supplier_id;
            $asset->physical = $sale->physical;
            $asset->location_id = $sale->location_id;
            $asset->purchase_id = $sale->purchase_id;
            $asset->purchase_cost = $sale->purchase_cost;
            $asset->nds = $sale->nds;
            $asset->user_verified_id = $sale->user_verified_id;
            $asset->contract_id = $sale->contract_id;
            $asset->deleted_at = $sale->deleted_at;
            $asset->quality = 5;
            if($sale->closing_documents>0 && $sale->contract_id>0){
                $asset->assigned_type = "App\Models\Contract";
                $asset->assigned_to = $sale->contract_id;
            }else if ($sale->user_responsible_id>0){
                $asset->assigned_type = "App\Models\User";
                $asset->assigned_to = $sale->user_responsible_id;
            }
            $asset->save();
            if ($asset->id>0){
                if($sale->closing_documents>0 && $sale->contract_id>0){
                    $log = new Actionlog();
                    $log->user_id = $sale->user_id;
                    $log->action_type = 'sell';
                    $log->target_type = "App\Models\Contract";
                    $log->target_id = $sale->contract_id;
                    $log->item_id = $asset->id;
                    $log->item_type = Asset::class;
                    $log->save();
                }else if ($sale->user_responsible_id>0){
                    $log = new Actionlog();
                    $log->user_id = $sale->user_id;
                    $log->action_type = 'issued_for_sale';
                    $log->target_type = "App\Models\User";
                    $log->target_id = $sale->user_responsible_id;
                    $log->item_id = $asset->id;
                    $log->item_type = Asset::class;
                    $log->save();
                }
            }
            $sale->delete();
        }
    }
}
