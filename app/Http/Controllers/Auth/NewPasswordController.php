<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
/**
 * @group Authentication
 *
 */
class NewPasswordController extends Controller
{
    /**
     * New password request
     *
     * @bodyParam token string required The token of the user. Example: 68d43b6b3ea9d817a1fdc0ea4fa25d2f4c42f91f47ae5525b95e41ef87ab6c30
     * @bodyParam email string required The email of the user. Example: aya@gmail.com
     * @bodyParam password string required The password of the user. Example: ayasameh123
     * @bodyParam password_confirmation string required The password confirmation of the user. Example: ayasameh123
     *
     * @response scenario=success {
     *  "message": "Your password has been reset!"
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
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid data','Errors in'=>$validator->messages()], 400);
        }
        else {
            $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) use ($request) {
                /*$user->forceFill([
                    'password' => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                ])->save();*/
                event(new PasswordReset($user));
                }
            );
            User::where('email',$request->email)->update(['password' => Hash::make($request->password)]);

            if(!($status == Password::PASSWORD_RESET)){
                return response()->json(['message'=>'No such user'], 404);
            }
            else{
                return response()->json(['message'=>'Your password has been reset!'], 200);
            }
        }
    }
    public function create(Request $request){
        return redirect('http://localhost:8080/resetpassword/'.$request->token.'/'.$request->email);
    }
    public function create(Request $request){
        return redirect('http://localhost:8080/resetpassword/'.$request->token.'/'.$request->email);
    }
}
