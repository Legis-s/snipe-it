<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Transformers\SalesTransformer;
use App\Models\Sale;
use App\Models\Statuslabel;

class BiometricController extends Controller
{

    /**
     * Returns JSON with information about an asset for detail view.
     *
     * @param int $assetId
     * @return JsonResponse
     * @since [v4.0]
     * @author [A. Gianotto] [<snipe@snipe.net>]
     */
    public function check($id)
    {
        /** @var \GuzzleHttp\Client $client */
        $client = new \GuzzleHttp\Client();

        $response = $client->request('GET', 'https://tabelapi.legis-s.ru:45443/tabel_local_server/response/'.$id, [
            'headers' => [
                'Authorization' => 'TabelLocalServerToken gAAAAABgkwAkYnPxsSkFvUGu51MshvJ1dzBlxhdzcpxm34n7Q_zalcEazo7dPWfWhO9ouhDqrdLH3O-cQFKwoD6QpGNwkv_V-M4Eit1880xt0R83RPGIeyl3MrBWcQhl-sYvR1b6uBo3',
            ]
            ]);
        $responseJson = $response->getBody()->getContents();

        return $responseJson;
    }

}