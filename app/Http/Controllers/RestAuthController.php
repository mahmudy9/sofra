<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Restaurant;
use Illuminate\Support\Facades\Hash;
use Validator;

class RestAuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api_rest')->except(['login' , 'register']);
    }


    public function register(Request $request)
    {
        $validator = Validator::make($request->all() , [
            'name' => 'required|min:3|max:190|string',
            'email' => 'required|email|unique:clients,email',
            'password' => 'required|min:6|max:190|string|confirmed',
            'city' => 'required|integer|min:1',
            'neighborhood' => 'required|integer|min:1', 
            'category' => 'required|integer|min:1',
            'min_order' => 'required|numeric|between:0,99999999.99',
            'delivery_fee' => 'required|numeric|between:0,99999999.99',
            'deliverytime_from' => 'required|date_format:H:i',
            'deliverytime_to' => 'required|date_format:H:i',
            'order_days' => 'required|in:all days,all days except friday,all days except saturday,all days except sunday,all days except sunday and saturday',
            'phone' => 'nullable|numeric|digits_between:10,15',
            'whatsapp' => 'nullable|numeric|digits_between:10,15',
            'picture' => 'required|image|max:1900'
        ]);

        if($validator->fails())
        {
            return apiRes(400 , 'Validation error' , $validator->errors());
        }

        if($request->hasFile('picture'))
        {
            $path = $request->file('picture')->store('public');
            $filename = pathinfo($path , PATHINFO_BASENAME);
        }

        $rest = new Restaurant;
        $rest->name = $request->input('name');
        $rest->email = $request->input('email');
        $rest->password = Hash::make($request->input('password'));
        $rest->city_id = $request->input('city');
        $rest->neighborhood_id = $request->input('neighborhood');
        $rest->category_id = $request->input('category');
        $rest->min_order = $request->input('min_order');
        $rest->delivery_fee = $request->input('delivery_fee');
        $rest->deliverytime_from = $request->input('deliverytime_from');
        $rest->deliverytime_to = $request->input('deliverytime_to');
        $rest->order_days = $request->input('order_days');
        if($request->has('phone'))
        {
            $rest->phone = $request->input('phone');
        }
        if($request->has('whatsapp'))
        {
            $rest->whatsapp = $request->input('whatsapp');
        }
        $rest->pic = $filename;
        $rest->save();
        return apiRes(200 , 'Account created you may now login' );        
    }


    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login()
    {
        $credentials = request(['email', 'password']);

        if (! $token = auth('api_rest')->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json(auth('api_rest')->user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth('api_rest')->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth('api_rest')->refresh());
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
            'expires_in' => auth('api_rest')->factory()->getTTL() * 60
        ]);
    }
}
