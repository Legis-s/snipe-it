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


        /** @var \GuzzleHttp\Client $client */
        $client = new \GuzzleHttp\Client();


        /**
         * Синхронизация польззователей
         */
        $leadID = 0;
        $finish = false;
        $bitrix_users_final = [];
        while (!$finish) {
            $response = $client->request('GET', 'https://bitrix.legis-s.ru/rest/1/rzrrat22t46msv7v/user.get.json?ACTIVE=True&start=' . $leadID);
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

            $user = User::firstOrCreate(
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

        $response = $client->request('GET', 'https://bitrix.legis-s.ru/rest/1/rzrrat22t46msv7v/legis_crm.object.list/?select%5B0%5D=UF_*&select%5B1%5D=*');
        $response = $response->getBody()->getContents();
        $bitrix_objects = json_decode($response, true);
        $bitrix_objects = $bitrix_objects["result"];
        $count = 0;
        foreach ($bitrix_objects as &$value) {
            $count++;
            $location = Location::where('bitrix_id', $value["ID"])->withTrashed()->first();
            $active = true;
            $bitrix_user = $value["ASSIGNED_BY_ID"];
            /** @var User $sklad_user */
            $sklad_user = User::where('bitrix_id', $bitrix_user)->first();
            switch ($value["UF_TYPE"]) {
                case 455:
                    $name = $value["NAME"];
                    break;
//                case 456:
//                    $name = "[Пульт] " . $value["NAME"];
//                    break;
                case 457:
                    $name = "[Тех. безопасность] " . $value["NAME"];
                    break;
                case 739:
                    $name = "[Клининг] " . $value["NAME"];
                    break;
                case 741:
                    $name = "[Биометрика] " . $value["NAME"];
                    break;
                default:
                    $name = $value["NAME"];

            }

            if (strlen($value["UF_CLOSEDATE"]) > 0) {
                $dateTime = DateTime::createFromFormat('d.m.Y', $value["UF_CLOSEDATE"]);
                $now = new DateTime();
                if ($dateTime <= $now) {
                    $active = false;
                    $name = "[Закрыто]" . $value["NAME"];
                }
            }

            if ($value["DELETED"] == 1) {
                $active = false;
                $name = "[Удалено]" . $value["NAME"];
            }


            $obj_types_to_sync = [455, 457, 739, 741];


            if ($active == false && $location && $location->isDeletableNoGate()){
                $location->delete();
            }else{
                if ($location){
                    $location->update([
                        'name' => $name,
                        'city' => $value["ADDRESS_CITY"],
                        'address' => $value["ADDRESS"],
                        'address2' => $value["ADDRESS_2"],
                        'coordinates' => $value["UF_MAP"],
                        'object_code' => intval($value["UF_TYPE"]),
                        'pult_id' => $value["UF_PULT_ID"],
                        'manager_id' =>  $sklad_user->id,
                        'active' => $active
                    ]);
                    $location->save();
                }
                if (in_array($value["UF_TYPE"], $obj_types_to_sync)) {
                    if (!$location) {
                        $location = Location::updateOrCreate(
                            ['bitrix_id' => $value["ID"]],
                            [
                                'name' => $name,
                                'city' => $value["ADDRESS_CITY"],
                                'address' => $value["ADDRESS"],
                                'address2' => $value["ADDRESS_2"],
                                'coordinates' => $value["UF_MAP"],
                                'object_code' => intval($value["UF_TYPE"]),
                                'pult_id' => $value["UF_PULT_ID"],
                                'manager_id' => $sklad_user->id,
                                'active' => $active
                            ]
                        );
                    }
                }
            }
        }


//            if (($value["TABEL_ID"] && $value["UF_TYPE"] == 455) || $value["UF_TYPE"] == 739 || $value["UF_TYPE"] == 457 || $value["UF_TYPE"] == 456 || $value["UF_TYPE"] == 741 || $value["ID"] == 2956 || $value["UF_TYPE"] == 742) {
//
//
//                if ($value["DELETED"] == 1 or strlen($value["UF_CLOSEDATE"]) > 0) {
//
//                    $active = true;
//                    if ($value["DELETED"] == 1) {
//                        $active = false;
//                    }
//                    if (strlen($value["UF_CLOSEDATE"]) > 0) {
//                        $dateTime = DateTime::createFromFormat('d.m.Y', $value["UF_CLOSEDATE"]);
//                        $now = new DateTime();
//                        if ($dateTime <= $now) {
//                            $active = false;
//                        }
//                    }
//
//                    if ($location) {
//                        $location->update([
//                            'name' => "[Удалено]" . $value["NAME"],
//                            'city' => $value["ADDRESS_CITY"],
//                            'address' => $value["ADDRESS"],
//                            'address2' => $value["ADDRESS_2"],
//                            'coordinates' => $value["UF_MAP"],
//                            'object_code' => intval($value["UF_TYPE"]),
//                            'active' => $active
//                        ]);
//                        $location->save();
//                    }
//                }
//
////                if (($value["TABEL_ID"] && $value["UF_TYPE"] == 455) || $value["UF_TYPE"] == 739 || $value["UF_TYPE"] == 457 || $value["UF_TYPE"] == 456 || $value["UF_TYPE"] == 741 || $value["ID"] == 2956 || $value["UF_TYPE"] == 742) {
//                    $count++;
//                    if ($value["UF_TYPE"] == 456) {
////                    print_r($value["UF_TYPE"]);
//                        $location = Location::updateOrCreate(
//                            ['bitrix_id' => $value["ID"]],
//                            [
//                                'name' => "[Пульт] " . $value["NAME"],
//                                'city' => $value["ADDRESS_CITY"],
//                                'address' => $value["ADDRESS"],
//                                'address2' => $value["ADDRESS_2"],
//                                'coordinates' => $value["UF_MAP"],
//                                'object_code' => intval($value["UF_TYPE"]),
//                                'active' => false,
//                                'pult_id' => $value["UF_PULT_ID"],
//
//                            ]
//                        );
//                    } else if ($value["UF_TYPE"] == 457) {
//                        $location = Location::updateOrCreate(
//                            ['bitrix_id' => $value["ID"]],
//                            [
//                                'name' => "[Тех. безопасность] " . $value["NAME"],
//                                'city' => $value["ADDRESS_CITY"],
//                                'address' => $value["ADDRESS"],
//                                'address2' => $value["ADDRESS_2"],
//                                'coordinates' => $value["UF_MAP"],
//                                'object_code' => intval($value["UF_TYPE"]),
//                                'active' => true,
//                                'pult_id' => $value["UF_PULT_ID"],
//                            ]
//                        );
//                    } else {
//                        $location = Location::updateOrCreate(
//                            ['bitrix_id' => $value["ID"]],
//                            [
//                                'name' => $value["NAME"],
//                                'city' => $value["ADDRESS_CITY"],
//                                'address' => $value["ADDRESS"],
//                                'address2' => $value["ADDRESS_2"],
//                                'coordinates' => $value["UF_MAP"],
//                                'object_code' => intval($value["UF_TYPE"]),
//                                'active' => true
//                            ]
//                        );
//                    }
//
//
//                    if (!$sklad_user) {
//                        print("Responsible at object '" . $value["NAME"] . "' [" . $value["ID"] . "] not found (Bitrix user id " . $bitrix_user . ")\n");
//                    } else {
//                        $location->manager_id = $sklad_user->id;
//                        $location->save();
//                    }
//                }
//            }
        print("Синхрониизтрованно " . $count . " объектов Битрикс\n");

        $next = 0;
        $finish = false;
        $bitrix_suppliers = [];
        while ($finish == false) {
            $response = $client->request('GET', 'https://bitrix.legis-s.ru/rest/1/rzrrat22t46msv7v/crm.company.list?FILTER[COMPANY_TYPE]=1&start=' . "$next");
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


        $response = $client->request('GET', 'https://bitrix.legis-s.ru/rest/1/rzrrat22t46msv7v/lists.element.get?IBLOCK_TYPE_ID=lists&IBLOCK_ID=77');
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


        $response = $client->request('GET', 'https://bitrix.legis-s.ru/rest/1/rzrrat22t46msv7v/legis_crm.contracts.list?select[0]=UF_*&select[1]=*');
        $response = $response->getBody()->getContents();
        $bitrix_contracts = json_decode($response, true);
        $bitrix_contracts = $bitrix_contracts["result"];
        $count = 0;
        foreach ($bitrix_contracts as &$value) {
            $count++;
//            if ( $value["ID"] == "5354"){
//                print_r($value);
//            }
            if ($value["STATUS_ID"] == "") {
                $value["STATUS_ID"] = "Пустой статус";
            }
            $contract = Contract::updateOrCreate(
                ['bitrix_id' => $value["ID"]],
                [
                    'name' => $value["NAME"],
                    'number' => $value["UF_NUMBER"],
                    'status' => $value["STATUS_ID"],
                    'type' => $value["TYPE_ID"],
                    'date_start' => $value["DATE_START"],
                    'date_end' => $value["DATE_END"],
                    'summ' => $value["UF_CRM_1560273765"],
                    'assigned_by_id' => $value["ASSIGNED_BY_ID"],
                ]
            );
            if (is_array($value["UF_OBJECT"]) && count($value["UF_OBJECT"]) > 0 && strlen($value["UF_NUMBER"]) > 0) {
                foreach ($value["UF_OBJECT"] as &$ufobj) {
                    $location = Location::where('bitrix_id', '=', $ufobj)->first();
                    if ($location) {
                        $cn = $location->contract_number;
                        $pos = strripos($cn, $value["UF_NUMBER"]);

                        if ($pos === false) {
                            $location->contract_number = $location->contract_number . " , " . $value["UF_NUMBER"];
                        }
//                        print($location->contract_number);

                        $location->save();

//                        if (strlen($cn)>0){
//                            try {
//                                $obj = json_decode($cn, true);
//                                $add = true;
//                                foreach ($obj as &$oneobj) {
//                                    if ($oneobj["id"] ==$value["ID"]){
//                                        $add = false;
//                                    }
//                                }
//                                if ($add == true){
//                                    $foo = new StdClass();
//                                    $foo->id = $value["ID"];
//                                    $foo->name = $value["UF_NUMBER"];
//                                    array_push($obj,$foo);
//                                }
//                                $json = json_encode($obj);
//                                $location->contract_number = $json;
//                                $location->save();
//                            }catch (Exception $e) {
//                                $foo = new StdClass();
//                                $foo->id = $value["ID"];
//                                $foo->name = $value["UF_NUMBER"];
//                                $json = json_encode([$foo]);
//                                $location->contract_number = $json;
//                                $location->save();
//                            }
//                        }else{
//                            $foo = new StdClass();
//                            $foo->id = $value["ID"];
//                            $foo->name = $value["UF_NUMBER"];
//                            $json = json_encode([$foo]);
//                            $location->contract_number = $json;
//                            $location->save();
//                        }
                    }
                }
//                $location = Location::where('bitrix_id', '=',  $value["UF_OBJECT"][0])->firstOrFail();
//                if ($location){
//                    $cn = $location->contract_number;
//
//                    try {
//                        $obj = json_decode($cn);
//                        if
//
//                    } catch (Exception $e) {
//                        // handle exception
//                    }
//                }


//                $location = Location::updateOrCreate(
//                    ['bitrix_id' =>  $value["UF_OBJECT"][0]] ,
//                    [
//                        'contract_number' => $value["UF_NUMBER"]
//                    ]
//
//                );
//                $location->save();
            }
        }
        print("Синхрониизтрованно " . $count . " договоров \n");


        $response = $client->request('GET', 'https://bitrix.legis-s.ru/rest/1/rzrrat22t46msv7v/lists.element.get?IBLOCK_TYPE_ID=lists&IBLOCK_ID=166');
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
}
