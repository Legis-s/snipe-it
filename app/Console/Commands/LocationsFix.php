<?php

namespace App\Console\Commands;

use App\Models\Contract;
use App\Models\CustomField;
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
use Illuminate\Support\Facades\Storage;
use stdClass;

class LocationsFix extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'snipeit:location-fix {--output= : info|warn|error|all} ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This utility will sync with bitrix';

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

        $bitrix_url =  env('BITRIX_URL');
        /** @var \GuzzleHttp\Client $client */
        $client = new \GuzzleHttp\Client();

        $next = 0;
        $finish = false;
        $bitrix_objects = [];
        while ($finish == false) {
            $params = [
                'query' => [
                    'entityTypeId' => 1032,
                    'select' => [
                        'id',
                        'title',
                        'ufCrm5_1721066282'
                    ],
                    'filter' =>[
                        'ufCrm5_1721062689' => [843,845,847,848]
                    ],
                    'start'=> $next
                ]
            ];
            $response = $client->request('GET', $bitrix_url.'crm.item.list/',$params);
            $response = $response->getBody()->getContents();
            $bitrix_objects_response = json_decode($response, true);
            $bitrix_objects = array_merge($bitrix_objects, $bitrix_objects_response["result"]["items"]);
            if (array_key_exists("next", $bitrix_objects_response)) {
                $next = $bitrix_objects_response["next"];
            } else {
                $finish = true;
            }
        }
        $count = 0;
        foreach ($bitrix_objects as &$value) {
            $location = Location::where('bitrix_id', $value["ufCrm5_1721066282"])->withTrashed()->first();
            if ($location){
                $location->bitrix_id_old = $location->bitrix_id;
                $location->bitrix_id = $value["id"];
                $location->save();
            }else{
                print ($value["id"] . "    " .$value["title"] . "\n");
            }
//            print ($value["id"]);

            $count++;
        }
        print($count);


        print("Синхрониизтрованно " . count($bitrix_objects) . " объектов Битрикс\n");

        if (($this->option('output') == 'all') || ($this->option('output') == 'info')) {
            foreach ($output['info'] as $key => $output_text) {
                $this->info($output_text);
            }
        }
    }
}
