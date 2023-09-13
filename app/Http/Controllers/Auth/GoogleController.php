<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Models\Mac_address;

/**
 * @group Socialization
 *
 */
class GoogleController extends Controller
{
    /**
	 * User Login/Sign up with google redirect page.
     * @bodyParam phone string required The phone of the user(start with:01, max:11). Example: 01234567899
     * @bodyParam gender string required The gender of the user(must be female or male). Example: female
     * @bodyParam birth_day date required The birthday of the user(must bebefore:17 years ago). Example: 1999-11-09
     * @return \Illuminate\Http\RedirectResponse
	 */
    public function redirectToGoogle(Request $request)
    {
        return Socialite::driver('google')->with(['state' =>'phone='.$request->phone.'&gender='.$request->gender.'&birth_day='.$request->birth_day])->redirect();
    }
    /**
	 * User Login/Sign up with google handling.
     *
     * @response scenario=success {
     *  "message": "logged in with google account successfully"
     *  "AccessToken": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC8xMjcuMC4wLjE6ODAwMFwvYXBpXC9sb2dpbiIsImlhdCI6MTYyOTczOTEwNiwiZXhwIjoxNjI5NzQyNzA2LCJuYmYiOjE2Mjk3MzkxMDYsImp0aSI6IjZtQWFzSDhkVFFiOTNZeFUiLCJzdWIiOjIxLCJwcnYiOiIyM2JkNWM4OTQ5ZjYwMGFkYjM5ZTcwMWM0MDA4NzJkYjdhNTk3NmY3In0.97UdgcALxqA5EMcRRZp5q0zDx-fKDiNwc-DwUMaUlHc"
     * }
     * @response status=400 scenario="failed" {
     *  "message": "Invalid data"
     *  "Errors in":{"phone":["The phone field is required."]}
     * }
     * @response status=201 scenario="created"{
     *  "message": "Signed up with google account successfully"
     *  "AccessToken": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC8xMjcuMC4wLjE6ODAwMFwvYXBpXC9sb2dpbiIsImlhdCI6MTYyOTczOTEwNiwiZXhwIjoxNjI5NzQyNzA2LCJuYmYiOjE2Mjk3MzkxMDYsImp0aSI6IjZtQWFzSDhkVFFiOTNZeFUiLCJzdWIiOjIxLCJwcnYiOiIyM2JkNWM4OTQ5ZjYwMGFkYjM5ZTcwMWM0MDA4NzJkYjdhNTk3NmY3In0.97UdgcALxqA5EMcRRZp5q0zDx-fKDiNwc-DwUMaUlHc"
     * }
	 */
    public function handleGoogleCallback(Request $request)
    {
        $user = Socialite::driver('google')->stateless()->user();
        $oldUser = User::where('email', $user->email)->first();
        if($oldUser){
            $token = auth()->login($oldUser);
            return response()->json(['message' => 'logged in with google account successfully ','AccessToken'=>$token], 200);
        }
        else{
            foreach (Mac_address::all() as $client){
                if($client->mac_address == exec('getmac')){
                    return response()->json(['message' => 'Invalid, you used this device before !!'], 403);
                }
            }
            $state = $request->input('state');
            parse_str($state, $result);
            $validator = Validator::make($result, [
                'phone' => ['required','regex:/(01)[0-9]{9}/','max:11'],
                'birth_day' =>['required','date','before:17 years ago'],
                'gender' => ['required','in:female,male'],
            ]);
            if ($validator->fails()) {
                return response()->json(['message' => 'Invalid data','Errorsin'=>$validator->messages()], 400);
            }
            else {
                date_default_timezone_set('africa/cairo');
                $date = date('Y/m/d H:i:s');
                
                $newUser = User::create([
                    'name' => $user->name,
                    'email' => $user->email,
                    'email_verified_at'=> $date,
                    'password' => Hash::make('123456dummy'),
                    'phone' => $result['phone'],
                    'birth_day'=>$result['birth_day'],
                    'gender' =>$result['gender'],
                    'age'=>Carbon::parse($result['birth_day'])->age, //get user's age auto
                    'image' => $user->avatar,
                    'reports' => 0,
                    'ban' => 0,
                    'ban_count' => 0,
                    'VIP' => 0,
                    'certified' => 0,
                    'answered'=>false,
                ]);
               
                $mac=Mac_address::create([
                    'mac_address'=>exec('getmac'), //get mac address
                ]);
                //event(new Registered($newUser));//already verified
                $token = auth()->login($newUser);
                return response()->json(['message' => 'Signed up with google account successfully ','AccessToken'=>$token], 201);
            }
        }
    }
}
