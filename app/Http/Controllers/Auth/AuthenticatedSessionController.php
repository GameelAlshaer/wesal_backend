<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use JWTAuth;

/**
 * @group Authentication
 *
 */
class AuthenticatedSessionController extends Controller
{
    /**
	 * User Login
     * @bodyParam email string required The email of the user. Example: aya@gmail.com
     * @bodyParam password string required The password of the user. Example: ayasameh123
     * @bodyParam remember boolean required The remember me of the user. Example: true
     *
     * @response scenario=success {
     *  "message": "logged in successfully"
     *  "AccessToken": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC8xMjcuMC4wLjE6ODAwMFwvYXBpXC9sb2dpbiIsImlhdCI6MTYyOTczOTEwNiwiZXhwIjoxNjI5NzQyNzA2LCJuYmYiOjE2Mjk3MzkxMDYsImp0aSI6IjZtQWFzSDhkVFFiOTNZeFUiLCJzdWIiOjIxLCJwcnYiOiIyM2JkNWM4OTQ5ZjYwMGFkYjM5ZTcwMWM0MDA4NzJkYjdhNTk3NmY3In0.97UdgcALxqA5EMcRRZp5q0zDx-fKDiNwc-DwUMaUlHc"
     * }
     * @response status=404 scenario="failed" {
     *  "message": "No such user, invalid email or password"
     * }
     * @response status=400 scenario="failed" {
     *  "message": "Invalid data"
     *  "Errors in":{"email":["The email field is required."]}
     * }
	 */
    public function store(Request $request)
    {
        //$request->authenticate();
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid data','Errors in'=>$validator->messages()], 400);
        } else {
            //$request->session()->regenerate();
            //$request->password=Hash::make($request->password);
            $credentials = $request->only('email', 'password');
            $token = Auth::attempt($credentials, true);///remember me not working...
            if ($token){
                return response()->json(['message' => 'logged in successfully','AccessToken'=>$token], 200);
            }
            else{
                return response()->json(['message' => 'No such user, invalid email or password'], 404);
            }
        }
    }


    /**
     * User Logout
     *
     * @authenticated
     * @response scenario=success {
     *  "message": "logged out successfully"
     * }
     */
    public function destroy(Request $request)
    {
        //Auth::guard('api')->logout();
        auth()->logout();

        //$request->session()->invalidate();
        //$request->session()->regenerateToken();

        return response()->json(['message' => 'logged out successfully'], 200);
    }
}
