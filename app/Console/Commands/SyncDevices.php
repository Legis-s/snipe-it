<?php

namespace App\Console\Commands;

use App\Models\Asset;
use App\Models\Category;
use App\Models\Device;
use DateInterval;
use DateTime;
use DateTimeZone;
use Exception;
use Illuminate\Console\Command;


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

        $category_sim = Category::where('name', 'Сим-Карты')->first();

        /** @var \GuzzleHttp\Client $client */
        $client = new \GuzzleHttp\Client();
        $mdm_url = env('MDM_URL');
        $mdm_user = env('MDM_USER');
        $mdm_password = env('MDM_PASSWORD');

        $response = $client->request('POST', $mdm_url . 'public/jwt/login', [
            \GuzzleHttp\RequestOptions::JSON => ['login' => $mdm_user, 'password' => $mdm_password]
        ]);
        $response = $response->getBody()->getContents();
        $token_json = json_decode($response, true);
        $token = $token_json["id_token"];
//        print($token_json["id_token"]);
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $token,
        ];


        $response = $client->request('POST', $mdm_url . 'private/devices/search', [
            'headers' => $headers,
            \GuzzleHttp\RequestOptions::JSON => ['pageSize' => 1000000, 'pageNum' => 1]
        ]);
        $response = $response->getBody()->getContents();
        $json = json_decode($response, true);

        $data = $json["data"];
        $devices = $data["devices"];
        $items = $devices["items"];
        $count = 0;

        $number_array = [];
        foreach ($items as &$phone) {
//            if ( $phone["number"] == "863258030469639"){
//                print (json_encode($phone)."\n");
//            }
            $count++;
            $date = new DateTime();
            $date->setTimestamp($phone["lastUpdate"] / 1000);
            $date->setTimezone(new DateTimeZone('Europe/Moscow'));
            $date->sub(new DateInterval('PT3H'));
            $deviceId = null;
            $info_imei = null;
            $batteryLevel = null;
            $model = null;
            $launcherVersion = null;
            $biometrikaVersion = null;

            if (isset($phone["info"])) {
                $info = $phone["info"];
                if (isset($info["deviceId"])) {
                    $deviceId = $info["deviceId"];
                }
                if (isset($info["imei"])) {
                    $info_imei = $info["imei"];
                    $number_array[] = $info_imei;
                }
                if (isset($info["batteryLevel"])) {
                    $batteryLevel = $info["batteryLevel"];
                }
                if (isset($info["model"])) {
                    $model = $info["model"];
                }
                if (isset($info["applications"])) {
                    $applications = $info["applications"];
                    foreach ($applications as &$app) {
                        if ($app["pkg"] == "ru.legis_s.biometrika") {
                            $biometrikaVersion = $app["version"];
                        }
                        if ($app["pkg"] == "com.hmdm.launcher") {
                            $launcherVersion = $app["version"];
                        }
                    }
                }
            }
            $imei = null;
            if (isset($phone["imei"])) {
                $imei = $phone["imei"];
            }

            $description = null;
            if (isset($phone["description"])) {
                $description = $phone["description"];
            }
            $statusCode = null;
            if (isset($phone["statusCode"])) {
                $statusCode = $phone["statusCode"];
            }

            $androidVersion = null;
            if (isset($phone["androidVersion"])) {
                $androidVersion = $phone["androidVersion"];
            }

            $serial = null;
            if (isset($phone["serial"])) {
                $serial = $phone["serial"];
            }

            $enrollTime = null;
            if (isset($phone["enrollTime"])) {
                $dateEnrol = new DateTime();
                $dateEnrol->setTimestamp($phone["enrollTime"] / 1000);
                $dateEnrol->setTimezone(new DateTimeZone('Europe/Moscow'));
                $dateEnrol->sub(new DateInterval('PT3H'));
                $enrollTime = $dateEnrol;
            }
            $publicIp = null;
            if (isset($phone["publicIp"])) {
                $publicIp = $phone["publicIp"];
            }

            $publicIp = null;
            $anyDesk = null;
            if (isset($phone["custom1"])) {
                $anyDesk = $phone["custom1"];
            }


            $asset_id = null;
            $sim_id = null;
            $asset = Asset::where('asset_tag', "it_" . $phone["number"])->first();
            if ($asset) {
                $asset_id = $asset->id;
                $assignedAssets = $asset->assignedAssets;
                foreach ($assignedAssets as &$aa) {
                    if ($aa->model->category->id == $category_sim->id) {
                        $sim_id = $aa->id;
                    }
                }
                if ($asset->location) {
                    $locationAs = $asset->location->name;
                    $responseupd = $client->request('POST', $mdm_url . 'private/devices/' . $phone["id"] . '/description', [
                        'headers' => $headers,
                        'body' => $locationAs
                    ]);
//                    $responseupd = $responseupd->getBody()->getContents();
//                    print($responseupd);
                }
            }
            $coordinates = null;
            $locationUpdate = null;

            try {
                $response = $client->request('GET', $mdm_url . 'plugins/devicelocations/devicelocations/private/device/' . $phone["id"] . '/location', [
                    'headers' => $headers,
                ]);
                $response = $response->getBody()->getContents();
                $json = json_decode($response, true);
                if (isset($json["data"])) {
                    $data = $json["data"];
                    if (isset($data["lat"]) && isset($data["lon"])) {
                        $lat = $data["lat"];
                        $lon = $data["lon"];
                        $coordinates = $lat . "," . $lon;
                    }
                    if (isset($data["ts"])) {
                        $dateLoc = new DateTime();
                        $dateLoc->setTimestamp($data["ts"] / 1000);
                        $dateLoc->setTimezone(new DateTimeZone('Europe/Moscow'));
                        $dateLoc->sub(new DateInterval('PT3H'));
                        $locationUpdate = $dateLoc;
                    }
                }

            } catch (Exception $e) {
                print 'Caught exception: ' . json_encode($e->getMessage()) . "\n";
            }
            $distance = null;
            if ($asset && $coordinates) {
                if ($asset->location) {
                    if ($asset->location->coordinates) {
                        $obj_location = $asset->location->coordinates;
                        $obj_location = explode(",", $obj_location);
                        $dev_coordinates = explode(",", $coordinates);
                        $distance = $this->getDistanceBetweenPointsNew($obj_location[0], $obj_location[1], $dev_coordinates[0], $dev_coordinates[1]);
                    }
                }
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
                    'androidVersion' => $androidVersion,
                    'biometrikaVersion' => $biometrikaVersion,
                    'launcherVersion' => $launcherVersion,
                    'serial' => $serial,
                    'lastUpdate' => $date,
                    'coordinates' => $coordinates,
                    'locationUpdate' => $locationUpdate,
                    'asset_id' => $asset_id,
                    'asset_sim_id' => $sim_id,
                    'distance' => $distance,
                    'publicIp' => $publicIp,
                    'enrollTime' => $enrollTime,
                    'anyDesk' => $anyDesk,
                ]
            );
        }
        print("Синхронизировано " . $count . " устройств\n");

        $devices_from_db = Device::all();
        foreach($devices_from_db as $dev) {
//            print($dev->number."\n");
            if (!in_array($dev->number, $number_array)) {
//                echo "нет в списке!\n";
                $dev->delete();
            }
        }
    }

    public function getDistanceBetweenPointsNew($latitude1, $longitude1, $latitude2, $longitude2): int
    {
        $theta = $longitude1 - $longitude2;
        $distance = (sin(deg2rad($latitude1)) * sin(deg2rad($latitude2))) + (cos(deg2rad($latitude1)) * cos(deg2rad($latitude2)) * cos(deg2rad($theta)));
        $distance = acos($distance);
        $distance = rad2deg($distance);
        $distance = $distance * 60 * 1.1515 * 1.609344 * 1000;
        return intval($distance);
    }
}
