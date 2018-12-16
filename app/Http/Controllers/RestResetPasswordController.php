<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Support\Facades\Auth;
use Password;
use Illuminate\Support\Facades\Hash;

class RestResetPasswordController extends Controller
{
    
    use ResetsPasswords;

    protected $redirectTo = "/";

    public function __construct()
    {
        $this->middleware('guest:api_rest');
    }

    public function showResetForm(Request $request , $token = null)
    {
        $email = $request->email;
        return view('restreset' , compact('token' , 'email'));
    }

    protected function guard()
    {
        return Auth::guard('api_rest');
    }

    protected function broker()
    {
        return Password::broker('restaurants');
    }

    public function resetPassword($user, $password)
    {
        $user->password = Hash::make($password);

        $user->save();
    }

    public function sendResetResponse(Request $request, $response)
    {
        $request->session()->flash('status' , trans($response));
        return redirect($this->redirectPath());
    }


}
