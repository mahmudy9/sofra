<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Password;

class RestForgotPasswordController extends Controller
{
    use SendsPasswordResetEmails;

    public function __construct()
    {
        $this->middleware('guest:api_rest');
    }


    // public function showLinkRequestForm()
    // {
    //     return view('restforgot');
    // }

    protected function broker()
    {
        return Password::broker('restaurants');
    }


    public function sendResetLinkResponse(Request $request, $response)
    {
        return response()->json(['status' => trans($response)]);
    }

    public function sendResetLinkFailedResponse(Request $request, $response)
    {
        return response()->json(['email' => trans($response)]);
    }


}
