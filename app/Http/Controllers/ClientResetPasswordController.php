<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Support\Facades\Auth;
use Password;
use Illuminate\Support\Facades\Hash;

class ClientResetPasswordController extends Controller
{
    
    use ResetsPasswords;

    protected $redirectTo = "/";

    public function __construct()
    {
        $this->middleware('guest:api_client');
    }


    public function showResetForm(Request $request , $token=null)
    {
        $email = $request->email;
        return view('clientreset' , compact('token' , 'email'));
    }


    protected function guard()
    {
        return Auth::guard('api_client');
    }

    protected function broker()
    {
        return Password::broker('clients');
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
