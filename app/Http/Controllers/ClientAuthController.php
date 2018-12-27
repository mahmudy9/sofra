<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;
use Illuminate\Support\Facades\Hash;
use App\Client;
use OneSignal;
use App\Player;

class ClientAuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api_client')->except(['login' , 'register']);
    }


    public function register(Request $request)
    {
        $validator = Validator::make($request->all() , [
            'name' => 'required|min:3|max:190|string',
            'email' => 'required|email|unique:clients,email',
            'phone' => 'required|numeric|digits_between:10,15|unique:clients,phone',
            'password' => 'required|min:6|max:190|string|confirmed',
            'address' => 'required|string|min:6|max:190',
            'city' => 'required|integer|min:1',
            'neighborhood' => 'required|integer|min:1', 
        ]);

        if($validator->fails())
        {
            return apiRes(400 , 'Validation error' , $validator->errors());
        }
        $client = new Client;
        $client->name = $request->input('name');
        $client->email = $request->input('email');
        $client->phone = $request->input('phone');
        $client->password = Hash::make($request->input('password'));
        $client->address = $request->input('address');
        $client->city_id = $request->input('city');
        $client->neighborhood_id = $request->input('neighborhood');
        $client->save();
        return apiRes(200 , 'Account created , you may now login');
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all() , [
            'email' => 'required|email',
            'password' => 'required',
            'device_type' => 'required|integer'
        ]);
        if($validator->fails())
        {
            return apiRes(400 , 'validation error' , $validator->errors());
        }
        $credentials = request(['email', 'password']);

        if (! $token = auth('api_client')->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $client = Client::where('email' , $request->email)->first();
        $this->store_palyer($client , $request->device_type);
        return $this->respondWithToken($token);
    }

    public function sendNote()
    {
        $player = auth('api_client')->user()->player()->first();
        //OneSignal::sendNotificationToUser('This is note' , $player['player_id']);
        $content = array(
			"en" => 'this is test message'
			);
		
		$fields = array(
			'app_id' => env('ONESIGNAL_APPID'),
			'include_player_ids' => array(),
			'data' => array("foo" => "bar"),
			'contents' => $content
		);
		
		$fields = json_encode($fields);
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8' ,
         'Authorization: Basic '.env('ONESIGNAL_REST_API_KEY')));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

		$response = curl_exec($ch);
		curl_close($ch);
		
		
        
        return apiRes(200 , 'success notification sent to one signal' , $response);
    }



    function notifyByFirebase($title="hithere", $body="notification body",
     $tokens=['jgfhdggt'], $data = [], $is_notification = true)
    {
    // https://gist.github.com/rolinger/d6500d65128db95f004041c2b636753a
    // API access key from Google FCM App Console
        // env('FCM_API_ACCESS_KEY'));
    
    //    $singleID = 'eEvFbrtfRMA:APA91bFoT2XFPeM5bLQdsa8-HpVbOIllzgITD8gL9wohZBg9U.............mNYTUewd8pjBtoywd';
    //    $registrationIDs = array(
    //        'eEvFbrtfRMA:APA91bFoT2XFPeM5bLQdsa8-HpVbOIllzgITD8gL9wohZBg9U.............mNYTUewd8pjBtoywd',
    //        'eEvFbrtfRMA:APA91bFoT2XFPeM5bLQdsa8-HpVbOIllzgITD8gL9wohZBg9U.............mNYTUewd8pjBtoywd',
    //        'eEvFbrtfRMA:APA91bFoT2XFPeM5bLQdsa8-HpVbOIllzgITD8gL9wohZBg9U.............mNYTUewd8pjBtoywd'
    //    );
        $registrationIDs = $tokens;
    
    // prep the bundle
    // to see all the options for FCM to/notification payload:
    // https://firebase.google.com/docs/cloud-messaging/http-server-ref#notification-payload-support
    
    // 'vibrate' available in GCM, but not in FCM
        $fcmMsg = array(
            'body' => $body,
            'title' => $title,
            'sound' => "default",
            'color' => "#203E78"
        );
        $data = json_encode($data);
    // I haven't figured 'color' out yet.
    // On one phone 'color' was the background color behind the actual app icon.  (ie Samsung Galaxy S5)
    // On another phone, it was the color of the app icon. (ie: LG K20 Plush)
    
    // 'to' => $singleID ;      // expecting a single ID
    // 'registration_ids' => $registrationIDs ;     // expects an array of ids
    // 'priority' => 'high' ; // options are normal and high, if not set, defaults to high.
        $fcmFields = array(
            'registration_ids' => $registrationIDs,
            'priority' => 'high',
            'data' => $data
        );
        if ($is_notification)
        {
            $fcmFields['notification'] = $fcmMsg;
        }
    
        $headers = array(
            'Authorization: key=' . env('FIREBASE_API_ACCESS_KEY'),
            'Content-Type: application/json'
        );
    
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fcmFields));
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;

    }


    private function create_player( $device_type)
    {
        $params = ['device_type' => $device_type];
        return OneSignal::createPlayer($params)->getBody()->getContents();
    }

    private function store_palyer($client , $device_type)
    {
        $player_json = $this->create_player($device_type);
        $player_array = \json_decode($player_json , true);
        $player_id = $player_array['id'];
        //$client = Client::find($client_id);
        if( Player::where('player_id' , $player_id)->exists())
        {
            Player::where('player_id' , $player_id)->delete();
        }
        $player = new Player;
        $player->player_id = $player_id;
        $player->device_type = $device_type;
        $client->player()->save($player);
        return true;
    }

    private function remove_player()
    {
        return auth('api_client')->user()->player()->delete();
    }


    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json(auth('api_client')->user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        $this->remove_player();

        auth('api_client')->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth('api_client')->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api_client')->factory()->getTTL() * 60
        ]);
    }
}
