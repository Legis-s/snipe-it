<?php

namespace App\Console\Commands;

use App\Models\Contract;
use App\Models\CustomField;
use App\Models\Deal;
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

class SyncBitrix extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'snipeit:sync-bitrix {--output= : info|warn|error|all} ';

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

        $bitrix_url = "https://bitrix.legis-s.ru/rest/1/rzrrat22t46msv7v/";


        /** @var \GuzzleHttp\Client $client */
        $client = new \GuzzleHttp\Client();
        $this->synh_users($client,$bitrix_url);
        $this->synh_objects($client,$bitrix_url);
        $this->synh_suppliers($client,$bitrix_url);
        $this->synh_legals($client,$bitrix_url);
        $this->synh_deals($client, $bitrix_url);
        $this->synh_types($client,$bitrix_url);

        if (($this->option('output') == 'all') || ($this->option('output') == 'info')) {
            foreach ($output['info'] as $key => $output_text) {
                $this->info($output_text);
            }
        }
        if (($this->option('output') == 'all') || ($this->option('output') == 'warn')) {
            foreach ($output['warn'] as $key => $output_text) {
                $this->warn($output_text);
            }
        }
        if (($this->option('output') == 'all') || ($this->option('output') == 'error')) {
            foreach ($output['error'] as $key => $output_text) {
                $this->error($output_text);
            }
        }
    }


    private function synh_users($client, $bitrix_url)
    {

        $leadID = 0;
        $finish = false;
        $bitrix_users_final = [];
        while (!$finish) {
            $params = [
                'query' => [
                    'FILTER' => [
                        'ACTIVE' => true,
                    ],
                    'start' => $leadID
                ]
            ];
            $response = $client->request('GET', $bitrix_url . 'user.get.json', $params);
            $response = $response->getBody()->getContents();
            $bitrix_users = json_decode($response, true);
            $bitrix_users = $bitrix_users["result"];
            $leadID = $leadID + count($bitrix_users);
            $bitrix_users_final = array_merge($bitrix_users_final, $bitrix_users);
            if (count($bitrix_users) > 0 && count($bitrix_users) == 50) {
            } else {
                $finish = true;
            }
        }


        foreach ($bitrix_users_final as &$value) {
            User::firstOrCreate(
                ['bitrix_id' => $value["ID"]],
                [
                    'username' => $value["EMAIL"],
                    'last_name' => $value["LAST_NAME"],
                    'first_name' => $value["NAME"],
                    'email' => $value["EMAIL"],
                    'password' => bcrypt($value["EMAIL"]),
                    'activated' => $value["ACTIVE"],
                ]
            );
        }
        print("Синхрониизтрованно " . count($bitrix_users_final) . " пользователей Битрикс\n");
    }

    private function synh_objects($client, $bitrix_url)
    {
        /**
         * Синхронизация объектов
         */
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
                        'stageId',
                        'assignedById',
                        'ufCrm5_1721066282', // old id
                        'ufCrm5_1721062689', // type
                        'ufCrm5_1721062974', // UF_MAP
                        'ufCrm5_1721065689255', // ADDRESS
                        'ufCrm5_1721063355', // UF_CLOSEDATE
                        'ufCrm5_1721066282', // id old
                    ],
                    'filter' => [
                        'ufCrm5_1721062689' => [843, 845, 847, 848]
                    ],
                    'start' => $next
                ]
            ];
            $response = $client->request('GET', $bitrix_url . 'crm.item.list/', $params);
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
            $count++;
            $location = Location::where('bitrix_id', $value["id"])->withTrashed()->first();
            $active = true;
            $bitrix_user = $value["assignedById"];
            /** @var User $sklad_user */
            $sklad_user = User::where('bitrix_id', $bitrix_user)->first();

            $sklad_user_id = null;
            if ($sklad_user) {
                $sklad_user_id = $sklad_user->id;
            }

            switch ($value["ufCrm5_1721062689"]) {
                case 845:
                    $name = "[Тех. безопасность] " . $value["title"];
                    break;
                case 847:
                    $name = "[Клининг] " . $value["title"];
                    break;
                case 848:
                    $name = "[Биометрика] " . $value["title"];
                    break;
                default:
                    $name = $value["title"];
            }

            if (strlen($value["ufCrm5_1721063355"]) > 0) {
                $dateTime = DateTime::createFromFormat('d.m.Y', $value["ufCrm5_1721063355"]);
                $now = new DateTime();
                if ($dateTime <= $now) {
                    $active = false;
                    $name = "[Закрыто]" . $value["title"];
                }
            }
            if ($value["stageId"] == "DT1032_7:FAIL") {
                $active = false;
                $name = "[Закрыто]" . $value["title"];
            }

            if (!$active && $location && $location->isDeletableNoGate()) {
                $location->delete();
            } else {
                if ($location) {
                    $location->update([
                        'name' => $name,
                        'bitrix_id_old' => $value["ufCrm5_1721066282"],
                        'address' => $value["ufCrm5_1721065689255"],
                        'address2' => "",
                        'coordinates' => $value["ufCrm5_1721062974"],
                        'object_code' => intval($value["ufCrm5_1721062689"]),
                        'manager_id' => $sklad_user_id,
                        'active' => $active
                    ]);
                    $location->save();
                } else {
                    Location::updateOrCreate(
                        ['bitrix_id' => $value["id"]],
                        [
                            'name' => $name,
                            'bitrix_id_old' => $value["ufCrm5_1721066282"],
                            'address' => $value["ufCrm5_1721065689255"],
                            'address2' => "",
                            'coordinates' => $value["ufCrm5_1721062974"],
                            'object_code' => intval($value["ufCrm5_1721062689"]),
                            'manager_id' => $sklad_user_id,
                            'active' => $active
                        ]
                    );
                }
            }
        }

        print("Синхрониизтрованно " . $count . " объектов Битрикс\n");

    }

    private function synh_suppliers($client, $bitrix_url)
    {
        /**
         * Синхронизация поставщиков
         */
        $next = 0;
        $finish = false;
        $bitrix_suppliers = [];
        while ($finish == false) {
            $params = [
                'query' => [
                    'FILTER' => [
                        'COMPANY_TYPE' => 1,
                    ],
                    'start' => $next
                ]
            ];
            $response = $client->request('GET', $bitrix_url . 'crm.company.list', $params);
            $response = $response->getBody()->getContents();
            $suppliers_response = json_decode($response, true);
            $suppliers_data = $suppliers_response["result"];
            $bitrix_suppliers = array_merge($bitrix_suppliers, $suppliers_data);
            if (array_key_exists("next", $suppliers_response)) {
                $next = $suppliers_response["next"];
            } else {
                $finish = true;
            }
        }

        $count = 0;
        foreach ($bitrix_suppliers as &$value) {
            $count++;
            $supplier = Supplier::updateOrCreate(

                ['bitrix_id' => $value["ID"]],
                [
                    'name' => $value["TITLE"],
                    'city' => $value["ADDRESS_CITY"],
                    'notes' => $value["COMMENTS"],
                    'address' => $value["ADDRESS"],
                    'address2' => $value["ADDRESS_2"],
                ]
            );

        }
        print("Синхрониизтрованно " . $count . " поставщиков \n");
    }

    private function synh_legals($client, $bitrix_url)
    {
        /**
         * Синхронизация юр. лиц
         */
        $params = [
            'query' => [
                'IBLOCK_TYPE_ID' => 'lists',
                'IBLOCK_ID' => 77,
            ]
        ];
        $response = $client->request('GET', $bitrix_url . 'lists.element.get', $params);
        $response = $response->getBody()->getContents();
        $bitrix_legal_persons = json_decode($response, true);
        $bitrix_legal_persons = $bitrix_legal_persons["result"];
        $count = 0;
        foreach ($bitrix_legal_persons as &$value) {
            $count++;
            $legal_person = LegalPerson::updateOrCreate(

                ['bitrix_id' => $value["ID"]],
                [
                    'name' => $value["NAME"],
                ]
            );

        }
        print("Синхрониизтрованно " . $count . " юр. лиц \n");
    }

    private function synh_deals($client, $bitrix_url)
    {
        /**
         * Синхронизация сделок
         */

        $next = 0;
        $finish = false;
        $deals = [];
        while ($finish == false) {
            $params = [
                'query' => [
                    'select' => [
//                        '*',
//                        'UF_*',
                        'ID',//bitrix_id
                        'TITLE',//NAME
                        'OPPORTUNITY',//summ
                        'CATEGORY_ID',//
                        'STAGE_ID',//
                        'BEGINDATE',//date_start
                        'CLOSEDATE',//date_end
                        'ASSIGNED_BY_ID',//assigned_by_id
                        'UF_CRM_1407316260',//number
                    ],
                    'filter' => [
                        'CATEGORY_ID' => [3, 2, 13]
                    ],
                    'start' => $next,
                ]
            ];

            $response = $client->request('GET', $bitrix_url . 'crm.deal.list/', $params);
            $response = $response->getBody()->getContents();
            $deals_response = json_decode($response, true);
            $deals = array_merge($deals, $deals_response["result"]);
            if (array_key_exists("next", $deals_response)) {
                $next = $deals_response["next"];
            } else {
                $finish = true;
            }
        }


        $count = 0;

        foreach ($deals as &$value) {
            $count++;
            $deal = Deal::updateOrCreate(
                ['bitrix_id' => $value["ID"]],
                [
                    'name' => $value["TITLE"],
                    'number' => $value["UF_CRM_1407316260"],
                    'status' => $value["STAGE_ID"],
                    'type' => $value["CATEGORY_ID"],
                    'date_start' => $value["BEGINDATE"],
                    'date_end' => $value["CLOSEDATE"],
                    'summ' => $value["OPPORTUNITY"],
                    'assigned_by_id' => $value["ASSIGNED_BY_ID"],
                ]
            );
        }
        print("Синхрониизтрованно " . $count . " сделок \n");


//        foreach ($bitrix_contracts as &$value) {
//            $count++;
////            if ( $value["ID"] == "5354"){
////                print_r($value);
////            }
//            if ($value["STATUS_ID"] == "") {
//                $value["STATUS_ID"] = "Пустой статус";
//            }
//            $contract = Contract::updateOrCreate(
//                ['bitrix_id' => $value["ID"]],
//                [
//                    'name' => $value["NAME"],
//                    'number' => $value["UF_NUMBER"],
//                    'status' => $value["STATUS_ID"],
//                    'type' => $value["TYPE_ID"],
//                    'date_start' => $value["DATE_START"],
//                    'date_end' => $value["DATE_END"],
//                    'summ' => $value["UF_CRM_1560273765"],
//                    'assigned_by_id' => $value["ASSIGNED_BY_ID"],
//                ]
//            );
//            if (is_array($value["UF_OBJECT"]) && count($value["UF_OBJECT"]) > 0 && strlen($value["UF_NUMBER"]) > 0) {
//                foreach ($value["UF_OBJECT"] as &$ufobj) {
//                    $location = Location::where('bitrix_id', '=', $ufobj)->first();
//                    if ($location) {
//                        $cn = $location->contract_number;
//                        $pos = strripos($cn, $value["UF_NUMBER"]);
//
//                        if ($pos === false) {
//                            $location->contract_number = $location->contract_number . " , " . $value["UF_NUMBER"];
//                        }
////                        print($location->contract_number);
//
//                        $location->save();
//
////                        if (strlen($cn)>0){
////                            try {
////                                $obj = json_decode($cn, true);
////                                $add = true;
////                                foreach ($obj as &$oneobj) {
////                                    if ($oneobj["id"] ==$value["ID"]){
////                                        $add = false;
////                                    }
////                                }
////                                if ($add == true){
////                                    $foo = new StdClass();
////                                    $foo->id = $value["ID"];
////                                    $foo->name = $value["UF_NUMBER"];
////                                    array_push($obj,$foo);
////                                }
////                                $json = json_encode($obj);
////                                $location->contract_number = $json;
////                                $location->save();
////                            }catch (Exception $e) {
////                                $foo = new StdClass();
////                                $foo->id = $value["ID"];
////                                $foo->name = $value["UF_NUMBER"];
////                                $json = json_encode([$foo]);
////                                $location->contract_number = $json;
////                                $location->save();
////                            }
////                        }else{
////                            $foo = new StdClass();
////                            $foo->id = $value["ID"];
////                            $foo->name = $value["UF_NUMBER"];
////                            $json = json_encode([$foo]);
////                            $location->contract_number = $json;
////                            $location->save();
////                        }
//                    }
//                }
////                $location = Location::where('bitrix_id', '=',  $value["UF_OBJECT"][0])->firstOrFail();
////                if ($location){
////                    $cn = $location->contract_number;
////
////                    try {
////                        $obj = json_decode($cn);
////                        if
////
////                    } catch (Exception $e) {
////                        // handle exception
////                    }
////                }
//
//
////                $location = Location::updateOrCreate(
////                    ['bitrix_id' =>  $value["UF_OBJECT"][0]] ,
////                    [
////                        'contract_number' => $value["UF_NUMBER"]
////                    ]
////
////                );
////                $location->save();
//            }
//        }
//        print("Синхрониизтрованно " . $count . " договоров \n");
//
//
    }

    private function synh_types($client, $bitrix_url)
    {
        /**
         * Синхронизация типов закупок
         */
        $params = [
            'query' => [
                'IBLOCK_TYPE_ID' => 'lists',
                'IBLOCK_ID' => 166,
            ]
        ];
        $response = $client->request('GET', $bitrix_url . 'lists.element.get', $params);
        $response = $response->getBody()->getContents();
        $bitrix_invoice_types = json_decode($response, true);
        $bitrix_invoice_types = $bitrix_invoice_types["result"];
        $count = 0;
        foreach ($bitrix_invoice_types as &$value) {
            $count++;
            $invoice_type = InvoiceType::updateOrCreate(
                ['bitrix_id' => $value["ID"]],
                [
                    'name' => $value["NAME"],
                ]
            );
        }
        print("Синхрониизтрованно " . $count . " типов закупок \n");
    }
}
