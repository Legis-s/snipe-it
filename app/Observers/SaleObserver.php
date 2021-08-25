<?php

namespace App\Observers;

use App\Models\Asset;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Setting;
use App\Models\Actionlog;
use App\Models\Statuslabel;
use Auth;
use Log;
use Crypt;
class SaleObserver
{
    /**
     * Listen to the User created event.
     *
     * @param  Asset  $asset
     * @return void
     */
    public function updating(Sale $asset)
    {

        $purchase =$asset->purchase;
        if ($purchase && $purchase->status != Purchase::FINISHED){

            \Log::error("sale observer");
            $purchase->checkStatus($asset);
//            $purchase->checkStatus();
            $purchase->save();
        }
    }


    /**
     * Listen to the Asset created event, and increment 
     * the next_auto_tag_base value in the settings table when i
     * a new asset is created.
     *
     * @param  Asset  $asset
     * @return void
     */
    public function created(Sale $asset)
    {
        if ($settings = Setting::first()) {
            $settings->increment('next_auto_tag_base');
        }

        $logAction = new Actionlog();
        $logAction->item_type = Sale::class;
        $logAction->item_id = $asset->id;
        $logAction->created_at =  date("Y-m-d H:i:s");
        $logAction->user_id = Auth::id();
        $logAction->logaction('create');

    }

    /**
     * Listen to the Asset deleting event.
     *
     * @param  Asset  $asset
     * @return void
     */
    public function deleting(Sale $asset)
    {
        $logAction = new Actionlog();
        $logAction->item_type = Sale::class;
        $logAction->item_id = $asset->id;
        $logAction->created_at =  date("Y-m-d H:i:s");
        $logAction->user_id = Auth::id();
        $logAction->logaction('delete');
    }
}
