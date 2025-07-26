<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use http\Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Passport\TokenRepository;


class AuthController extends Controller
{


    /**
     * Create a new authentication controller instance.
     *
     * @return void
     */
    public function __construct(TokenRepository $tokenRepository)
    {
        $this->tokenRepository = $tokenRepository;
    }


    public function getToken(Request $request)
    {
        if ($request->filled('username') && $request->filled('password')) {
            $login = $request->get('username');
            $password= $request->get('password');
            if (auth()->validate(['username' => $login, 'password' => $password, 'activated' => 1])){

                try {
                    $user = User::where('username', '=', $login)->whereNull('deleted_at')->where('activated', '=', '1')->first();
                    if(!is_null($user)) {
                        $token =$user->createToken(
                            $request->name, $request->scopes ?: []
                        );
                        $arr = [
                            "name" => $user->getFullNameAttribute(),
                            "username"=>$login,
                            "token"=>$token->accessToken
                        ];
                        return json_encode($arr);
                    }
                } catch(Exception $e) {
                    Log::debug("There was an error authenticating the Remote user: " . $e->getMessage());
                }

                return $login;
            }else{
                return "Bad credentals";
            }
        }else{
            return "No credentals";
        }
    }

    public function bitrixAuth(Request $request)
    {
        if ($request->filled('code')) {
            $code = $request->get('code');
            $client = new \GuzzleHttp\Client();
            $params = [
                'query' => [
                    'grant_type' => 'authorization_code',
                    'client_id' => 'local.6384cb37df2c27.72657963',
                    'client_secret' => 'uLx3Ht6F07S0gwJanKry7jV1oMAeMW40s9ARDfv1PzBMvH6nrP',
                    'code' => $code,
                ]
            ];
            $response = $client->request('GET','https://oauth.bitrix.info/oauth/token/',$params);
            if ($response->getStatusCode() == 200){
                $data = json_decode((string) $response->getBody(), true);
                $bitrixId = $data["user_id"];
                $user = User::where('bitrix_id', '=', $bitrixId)->whereNull('deleted_at')->where('activated', '=', '1')->first();
                if(!is_null($user)) {
                    auth()->login($user, 1);
                    if ($user = auth()->user()) {
                        $user->last_login = \Carbon::now();
                        $user->activated = 1;
                        $user->save();
                    }
                    // Redirect to the users page
                    return redirect()->intended()->with('success', trans('auth/message.signin.success'));
                }
            }else{
                return "Bitrix Error";
            }
        }else{
            return "No credentals";
        }
    }
}

