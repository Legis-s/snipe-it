<?php

namespace App\Console\Commands;

use App\Models\Contract;
use App\Models\CustomField;
use App\Models\Device;
use App\Models\Supplier;
use App\Models\LegalPerson;
use App\Models\InvoiceType;
use DateTime;
use DateTimeInterface;
use Exception;

//use False\True;
use Illuminate\Console\Command;
use App\Models\Asset;
use App\Models\Location;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use stdClass;

class SyncDevices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'snipeit:sync-devices {--output= : info|warn|error|all} ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This utility will sync with mdm';

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


        /** @var \GuzzleHttp\Client $client */
        $client = new \GuzzleHttp\Client();

        $response = $client->request('POST', 'https://mdm.legis-s.ru/rest/public/jwt/login', [
            \GuzzleHttp\RequestOptions::JSON => ['login' => 'api_user', 'password' => '4C08BD7F715FE2120A80144357C409FC']
        ]);
        $response = $response->getBody()->getContents();
        $token_json = json_decode($response, true);
        $token = $token_json["id_token"];
        print($token_json["id_token"]);
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer '.$token,
        ];


        $response = $client->request('POST', 'https://mdm.legis-s.ru/rest/private/devices/search', [
            'headers' => $headers,
            \GuzzleHttp\RequestOptions::JSON => ['pageSize' => 1000000, 'pageNum' => 1]
        ]);
        $response = $response->getBody()->getContents();
        $json = json_decode($response, true);

        $data = $json["data"];
        $devices = $data["devices"];
        $items= $devices["items"];
        $count = 0;
        foreach ($items as &$phone) {
//            print (json_encode($phone));
            $count++;
            $date = new DateTime();
            $date->setTimestamp($phone["lastUpdate"]/ 1000);
            $deviceId = null;
            $info_imei = null;
            $batteryLevel = null;
            $model = null;

            if (isset($phone["info"])){
                $info = $phone["info"];
                if (isset($info["deviceId"])){
                    $deviceId = $info["deviceId"];
                }
                if (isset($info["imei"])){
                    $info_imei  = $info["imei"];
                }
                if (isset($info["batteryLevel"])){
                    $batteryLevel = $info["batteryLevel"];
                }
                if (isset($info["model"])){
                    $model = $info["model"];
                }
            }
            $imei= null;
            if (isset($phone["imei"])){
                $imei =  $phone["imei"];
            }

            $description= null;
            if (isset($phone["description"])){
                $description =  $phone["description"];
            }
            $statusCode = null;
            if (isset($phone["statusCode"])){
                $statusCode =  $phone["statusCode"];
            }
            $asset_id = null;
            $asset = Asset::where('asset_tag', "it_".$phone["number"])->first();
            if ($asset){
                print ($asset->id."\n");
                $asset_id =$asset->id;
            }
            $device = Device::updateOrCreate(
                ['mdm_id' => $phone["id"]],
                [
                    'number' => $phone["number"],
                    'statusCode' => $statusCode,
                    'description' => $description,
                    'deviceId' => $deviceId,
                    'info_imei' => $info_imei,
                    'batteryLevel' => $batteryLevel,
                    'model' => $model,
                    'imei' => $imei,
                    'lastUpdate' => $date,
                    'asset_id' => $asset_id,
                ]
            );
        }
        print("Синхрониизтрованно " . $count . " устройств \n");
    }
}
