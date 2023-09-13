<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
/**
 * @group Authentication
 *
 */
class VerifyEmailController extends Controller
{
    /**
     * Verify user's email address
     *
     * @param  \Illuminate\Foundation\Auth\EmailVerificationRequest  $request
     * @authenticated
     * @urlParam id integer required The id of the user.
     * @urlParam hash string required the hashed token. Example: 72aa8d4285d697a6f82edd86fe9e29e039dea408
     * @response status=200 scenario="success" {
     *  "message": "Email Verified"
     * }
     */
    public function __invoke(EmailVerificationRequest $request)
    {
       // return redirect('http://localhost:8080/verifyemail/'.$request->id.'/'.$request->hash.'/'.$request->expires.'/'.$request->hash);
        if ($request->user()->hasVerifiedEmail()) {
            //return redirect()->intended(RouteServiceProvider::HOME.'?verified=1');
            return response()->json(['message'=>'Email Verified'], 200);
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        //return redirect()->intended(RouteServiceProvider::HOME.'?verified=1');
        return response()->json(['message'=>'Email Verified'], 200);
    }


}
