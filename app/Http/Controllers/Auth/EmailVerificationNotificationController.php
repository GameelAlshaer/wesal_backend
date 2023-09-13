<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
/**
 * @group Authentication
 *
 */
class EmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification
     * @response scenario=success {
     *  "message": "Successfully Verified"
     * }
     * 
     */
    public function store(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json(['message' => 'Successfully Verified'], 200);
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    }
}
