<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
/**
 * @group Authentication
 *
 */
class ConfirmablePasswordController extends Controller
{
    /**
     * Confirm the user's password
     * 
     * @authenticated
     * 
     * @response scenario=success {
     *  "message": "Password Confirmed"
     * }
     */
    public function store(Request $request)
    {
        if (! Auth::guard('api')->validate([
            'email' => $request->user()->email,
            'password' => $request->password,
        ])) {
            throw ValidationException::withMessages([
                'password' => __('auth.password'),
            ]);
        }

        $request->session()->put('auth.password_confirmed_at', time());

        //return redirect()->intended(RouteServiceProvider::HOME);
        return response()->json(['message'=>'Password Confirmed'], 200); 
    }
}
