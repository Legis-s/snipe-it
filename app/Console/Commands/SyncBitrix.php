<?php

namespace App\Console\Commands;

use App\Models\Deal;
use App\Models\Supplier;
use App\Models\LegalPerson;
use App\Models\InvoiceType;
use DateTime;

//use False\True;
use Illuminate\Console\Command;
use App\Models\Asset;
use App\Models\Location;
use App\Models\User;

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
        $bitrix_main_url = config('services.bitrix.url');
        $bitrix_user = config('services.bitrix.user');
        $bitrix_key = config('services.bitrix.key');

        if ($bitrix_main_url !== null && $bitrix_user !== null && $bitrix_key !== null) {
            $bitrix_url = $bitrix_main_url . "rest/" . $bitrix_user . "/" . $bitrix_key . "/";

            /** @var \GuzzleHttp\Client $client */
            $client = new \GuzzleHttp\Client();
            $this->synh_users($client, $bitrix_url);
            $this->synh_objects($client, $bitrix_url);
            $this->synh_suppliers($client, $bitrix_url);
            $this->synh_legals($client, $bitrix_url);
            $this->synh_deals($client, $bitrix_url);
            $this->synh_types($client, $bitrix_url);
        } else {
            $this->error('Required Bitrix environment variables are not set. Please check BITRIX_URL, BITRIX_USER and BITRIX_KEY.');
            return 1;
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
            if ($value["ACTIVE"] == 1) {
                User::firstOrCreate(
                    ['bitrix_id' => $value["ID"]],
                    [
                        'username' => $value["EMAIL"],
                        'last_name' => $value['LAST_NAME'] ?? null,
                        'first_name' => $value['NAME'] ?? null,
                        'email' => $value['EMAIL'] ?? null,
                        'password' => bcrypt($value["EMAIL"]),
                        'activated' => $value["ACTIVE"],
                    ]
                );
            } else {
                User::where('bitrix_id', $value["ID"])->update(['activated' => false]);
            }
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
                        'ufCrm5_1721062689', // type
                        'ufCrm5_1721063355', // UF_CLOSEDATE
                        'ufCrm5_1742195522', // yandex_map
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

            $yandex_map_json = $value["ufCrm5_1742195522"];
            $yandex_map = json_decode($yandex_map_json);

            $address = '';
            $coordinates = '';
            if ($yandex_map && is_object($yandex_map)) {
                $address = property_exists($yandex_map, 'address') ? $yandex_map->address : '';
                $coordinates = property_exists($yandex_map, 'coord') ? implode(",", $yandex_map->coord) : '';
            }

            $obj_code = $value["ufCrm5_1721062689"];
            switch ($obj_code) {
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

            if ($value["id"] == 18957) {
                print_r($value);
                print("\n");
                print_r("$address");
                print_r($address);
            }

            if (!$active && $location && $location->isDeletableNoGate()) {
                if (!$location->trashed()) {
                    $location->delete();
                }
            } else {
                if ($location) {
                    $location->update([
                        'name' => $name,
                        'address' => $address,
                        'coordinates' => $coordinates,
                        'object_code' => intval($obj_code),
                        'manager_id' => $sklad_user_id,3
                    ]);
                    if ($location->trashed()) {
                        $location->restore();
                    }
                } else {
                    Location::updateOrCreate(
                        ['bitrix_id' => $value["id"]],
                        [
                            'name' => $name,
                            'address' => $address,
                            'coordinates' => $coordinates,
                            'object_code' => intval($obj_code),
                            'manager_id' => $sklad_user_id,
                        ]
                    );
                }
            }
        }


        print("Синхронизировано " . $count . " объектов Битрикс\n");

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
                    'select' => [
                        'ID',
                        'TITLE',
                        'ADDRESS',
                        'ADDRESS_2',
                        'ADDRESS_CITY',
                        'COMMENTS',
                        'COMPANY_TYPE',
                    ],
                    'filter' => [
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
        $existing_suppliers = [];
        $count = 0;
        foreach ($bitrix_suppliers as &$value) {
            $count++;
            $existing_suppliers[] = $value["ID"];
//            print_r($value);
            Supplier::updateOrCreate(
                ['bitrix_id' => $value["ID"]],
                [
                    'name' => $value["TITLE"],
                    'city' => $value["ADDRESS_CITY"],
                    'notes' => substr($value["COMMENTS"], 0, 185),
                    'address' => $value["ADDRESS"],
                    'address2' => $value["ADDRESS_2"],
                ]
            );
        }
        Supplier::whereNotIn('bitrix_id', $existing_suppliers)->delete();
        print("Синхронизировано " . $count . " поставщиков \n");
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
        $existing_legal_persons = [];
        foreach ($bitrix_legal_persons as &$value) {
            $count++;
            $existing_legal_persons[] = $value["ID"];
            $legal_person = LegalPerson::updateOrCreate(

                ['bitrix_id' => $value["ID"]],
                [
                    'name' => $value["NAME"],
                ]
            );

        }
        LegalPerson::whereNotIn('bitrix_id', $existing_legal_persons)->delete();

        print("Синхронизировано " . $count . " юр. лиц \n");
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
                        'CATEGORY_ID' => [3, 2, 13, 14, 9]
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
        print("Синхронизировано " . $count . " сделок \n");
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
        $existing_invoice_types = [];
        foreach ($bitrix_invoice_types as &$value) {
            $count++;
            $existing_invoice_types[] = $value["ID"];
            $invoice_type = InvoiceType::updateOrCreate(
                ['bitrix_id' => $value["ID"]],
                [
                    'name' => $value["NAME"],
                ]
            );
        }
        InvoiceType::whereNotIn('bitrix_id', $existing_invoice_types)->delete();

        print("Синхронизировано " . $count . " типов закупок \n");

    }
}
