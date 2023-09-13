<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
/**
 * @group Authentication
 *
 */
class PasswordResetLinkController extends Controller
{
    /**
     * Forget password request
     *
     * @bodyParam email string required The email of the user. Example: aya@gmail.com
     * 
     * @response scenario=success {
     *  "message": "Reset password link sent successfully"
     * }
     * @response status=404 scenario="failed" {
     *  "message": "No such user"
     * }
     * @response status=400 scenario="failed" {
     *  "message": "Invalid data"
     *  "Errors in":{"email":["The email field is required."]}
     * }
     */
    public function store(Request $request)
    {   
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid data','Errors in'=>$validator->messages()], 400);
        } 
        else {
        
            $status = Password::sendResetLink(
                $request->only('email')
            );
            if(!($status == Password::RESET_LINK_SENT)){
                return response()->json(['message'=>'No such user'], 404);                  
            }                    
            else{                    
                //$data=DB::table('password_resets')->where('email', $request->only('email'))->first(); Frontend will send the token that is in the header 
                return response()->json(['message'=>'Reset password link sent successfully'], 200);                    
            }
        }
    }
}
