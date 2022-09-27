<?php

namespace App\Console\Commands;

use App\Models\Contract;
use App\Models\CustomField;
use App\Models\Supplier;
use App\Models\LegalPerson;
use App\Models\InvoiceType;
use Exception;
use Illuminate\Console\Command;
use App\Models\Asset;
use App\Models\Location;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

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


//        /**
//         * Начинаем с нуля или с какого то предыдущего шага
//         */
//        $leadID = 0;
//        $finish = false;
//        $bitrix_users_final = [];
//        while (!$finish){
//            $response = $client->request('GET', 'https://bitrix.legis-s.ru/rest/1/rzrrat22t46msv7v/user.get.json?ACTIVE=True&start='.$leadID);
//            $response = $response->getBody()->getContents();
//            $bitrix_users = json_decode($response, true);
//            $bitrix_users = $bitrix_users["result"];
//            $leadID = $leadID + count($bitrix_users);
//            $bitrix_users_final = array_merge($bitrix_users_final, $bitrix_users);
//            if (count($bitrix_users) > 0 && count($bitrix_users) ==50) {
//            }else{
//                $finish = true;
//            }
//        }
//
//
//        foreach ($bitrix_users_final as &$value) {
//            $user = User::firstOrCreate(
//                ['bitrix_id' =>  $value["ID"]],
//                [
//                    'username' => $value["EMAIL"],
//                    'last_name' => $value["LAST_NAME"],
//                    'first_name' => $value["NAME"],
//                    'email' => $value["EMAIL"],
//                    'password'=> bcrypt($value["EMAIL"]),
//                    'activated'=> true,
//                ]
//            );
//        }
//        print("Синхрониизтрованно ".count($bitrix_users_final)." пользователей Битрикс\n");
//
//        $response = $client->request('GET', 'https://bitrix.legis-s.ru/rest/1/rzrrat22t46msv7v/legis_crm.object.list/?select%5B0%5D=UF_*&select%5B1%5D=*');
//        $response = $response->getBody()->getContents();
//        $bitrix_objects = json_decode($response, true);
//        $bitrix_objects = $bitrix_objects["result"];
//        $count = 0 ;
//        foreach ($bitrix_objects as &$value) {
//            if ($value["DELETED"] == 1) {
//                $location = Location::where('bitrix_id',$value["ID"])->first();
//                if ($location){
//                    $location->update(  [
//                        'name' => "[Закрыто]".$value["NAME"],
//                        'city' => $value["ADDRESS_CITY"],
//                        'address' => $value["ADDRESS"],
//                        'address2' => $value["ADDRESS_2"],
//                        'coordinates' => $value["UF_MAP"],
//                        'active' => false
//                    ]);
//                    $location->save();
//                }
//                continue;
//            }
//            if(($value["TABEL_ID"] && $value["UF_TYPE"] == 455) || $value["UF_TYPE"] == 739 || $value["UF_TYPE"] == 457 || $value["UF_TYPE"] == 456 || $value["UF_TYPE"] == 741 || $value["ID"] == 2956 || $value["UF_TYPE"] == 742){
//                $count++;
//                $bitrix_user =  $value["ASSIGNED_BY_ID"];
//                /** @var User $sklad_user */
//                $sklad_user = User::where('bitrix_id', $bitrix_user)->first();
//                if  ($value["UF_TYPE"] == 456){
////                    print_r($value);
//                    $location = Location::updateOrCreate(
//                        ['bitrix_id' =>  $value["ID"]],
//                        [
//                            'name' => "[Пульт] ".$value["NAME"],
//                            'city' => $value["ADDRESS_CITY"],
//                            'address' => $value["ADDRESS"],
//                            'address2' => $value["ADDRESS_2"],
//                            'coordinates' => $value["UF_MAP"],
//                            'active' => true,
//                            'pult_id' => $value["UF_PULT_ID"],
//                        ]
//                    );
//                }else if ($value["UF_TYPE"] == 457) {
//                    $location = Location::updateOrCreate(
//                        ['bitrix_id' =>  $value["ID"]],
//                        [
//                            'name' => "[Тех. безопасность] ".$value["NAME"],
//                            'city' => $value["ADDRESS_CITY"],
//                            'address' => $value["ADDRESS"],
//                            'address2' => $value["ADDRESS_2"],
//                            'coordinates' => $value["UF_MAP"],
//                            'active' => true,
//                            'pult_id' => $value["UF_PULT_ID"],
//                        ]
//                    );
//                }else{
//                    $location = Location::updateOrCreate(
//                        ['bitrix_id' =>  $value["ID"]],
//                        [
//                            'name' => $value["NAME"],
//                            'city' => $value["ADDRESS_CITY"],
//                            'address' => $value["ADDRESS"],
//                            'address2' => $value["ADDRESS_2"],
//                            'coordinates' => $value["UF_MAP"],
//                            'active' => true
//                        ]
//                    );
//                }
//                if (!$sklad_user) {
//                    print("Responsible at object '".$value["NAME"]."' [".$value["ID"]."] not found (Bitrix user id ".$bitrix_user.")\n");
//                }else{
//                    $location->manager_id = $sklad_user->id;
//                }
//
//                $location->save();
//            }
//        }
//        print("Синхрониизтрованно ".$count." объектов Битрикс\n");
//
//        $next = 0;
//        $finish = false;
//        $bitrix_suppliers = [];
//        while ($finish == false){
//            $response = $client->request('GET', 'https://bitrix.legis-s.ru/rest/1/rzrrat22t46msv7v/crm.company.list?FILTER[COMPANY_TYPE]=1&start='."$next");
//            $response = $response->getBody()->getContents();
//            $suppliers_response = json_decode($response, true);
//            $suppliers_data = $suppliers_response["result"];
//            $bitrix_suppliers = array_merge($bitrix_suppliers, $suppliers_data);
//            if (array_key_exists("next", $suppliers_response)) {
//                $next =  $suppliers_response["next"];
//            }else{
//                $finish = true;
//            }
//        }
//
//        $count = 0 ;
//        foreach ($bitrix_suppliers as &$value) {
//            $count++;
//            $supplier = Supplier::updateOrCreate(
//
//                ['bitrix_id' =>  $value["ID"]],
//                [
//                    'name' => $value["TITLE"],
//                    'city' => $value["ADDRESS_CITY"],
//                    'notes'=> $value["COMMENTS"],
//                    'address' => $value["ADDRESS"],
//                    'address2' => $value["ADDRESS_2"],
//                ]
//            );
//
//        }
//        print("Синхрониизтрованно ".$count." поставщиков \n");
//
//
//
//        $response = $client->request('GET', 'https://bitrix.legis-s.ru/rest/1/rzrrat22t46msv7v/lists.element.get?IBLOCK_TYPE_ID=lists&IBLOCK_ID=77');
//        $response = $response->getBody()->getContents();
//        $bitrix_legal_persons = json_decode($response, true);
//        $bitrix_legal_persons = $bitrix_legal_persons["result"];
//        $count = 0 ;
//        foreach ($bitrix_legal_persons as &$value) {
//            $count++;
//            $legal_person = LegalPerson::updateOrCreate(
//
//                ['bitrix_id' =>  $value["ID"]],
//                [
//                    'name' => $value["NAME"],
//                ]
//            );
//
//        }
//        print("Синхрониизтрованно ".$count." юр. лиц \n");


        $response = $client->request('GET', 'https://bitrix.legis-s.ru/rest/1/rzrrat22t46msv7v/legis_crm.contracts.list?select[0]=UF_*&select[1]=*');
        $response = $response->getBody()->getContents();
        $bitrix_contracts = json_decode($response, true);
        $bitrix_contracts = $bitrix_contracts["result"];
        $count = 0 ;
        foreach ($bitrix_contracts as &$value) {
            $count++;
            if ( $value["ID"] == "5354"){
                print_r($value);
            }
            if ($value["STATUS_ID"]  == ""){
                $value["STATUS_ID"] = "Пустой статус";
            }
            $contract = Contract::updateOrCreate(
                ['bitrix_id' =>  $value["ID"]],
                [
                    'name' => $value["NAME"],
                    'number' => $value["UF_NUMBER"],
                    'status' => $value["STATUS_ID"],
                    'type' => $value["TYPE_ID"],
                    'date_start' => $value["DATE_START"],
                    'date_end' => $value["DATE_END"],
                    'assigned_by_id' => $value["ASSIGNED_BY_ID"],
                ]
            );
            if ( is_array($value["UF_OBJECT"]) && count($value["UF_OBJECT"]) >0 && strlen($value["UF_NUMBER"])>0 ){
                print("UF_NUMBER");
                print($value["UF_NUMBER"]);
                print("UF_OBJECT");
                print(count($value["UF_OBJECT"]));
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
        print("Синхрониизтрованно ".$count." договоров \n");



//        $response = $client->request('GET', 'https://bitrix.legis-s.ru/rest/1/rzrrat22t46msv7v/lists.element.get?IBLOCK_TYPE_ID=lists&IBLOCK_ID=166');
//        $response = $response->getBody()->getContents();
//        $bitrix_invoice_types = json_decode($response, true);
//        $bitrix_invoice_types = $bitrix_invoice_types["result"];
//        $count = 0 ;
//        foreach ($bitrix_invoice_types as &$value) {
//            $count++;
//            $invoice_type = InvoiceType::updateOrCreate(
//
//                ['bitrix_id' =>  $value["ID"]],
//                [
//                    'name' => $value["NAME"],
//                ]
//            );
//
//
//        }
//        print("Синхрониизтрованно ".$count." типов закупок \n");
//
//
//        if (($this->option('output')=='all') || ($this->option('output')=='info')) {
//            foreach ($output['info'] as $key => $output_text) {
//                $this->info($output_text);
//            }
//        }
//        if (($this->option('output')=='all') || ($this->option('output')=='warn')) {
//            foreach ($output['warn'] as $key => $output_text) {
//                $this->warn($output_text);
//            }
//        }
//        if (($this->option('output')=='all') || ($this->option('output')=='error')) {
//            foreach ($output['error'] as $key => $output_text) {
//                $this->error($output_text);
//            }
//        }
    }
}
