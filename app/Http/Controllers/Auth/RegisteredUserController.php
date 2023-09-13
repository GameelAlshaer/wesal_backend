<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\UserController;
use App\Models\Mac_address;
use App\Models\User;
use App\Models\UserCertification;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
/**
 * @group Authentication
 *
 */
class RegisteredUserController extends Controller
{
    /**
     * User register request
     * @bodyParam name string required The name of the user (max:255). Example: aya
     * @bodyParam email string required The email of the user(max:255, must be unique). Example: aya@gmail.com
     * @bodyParam password string required The password of the user(atleast 8 characters). Example: ayasameh123
     * @bodyParam password_confirmation string required The password confirmation of the user. Example: ayasameh123
     * @bodyParam phone string required The phone of the user(start with:01, max:11). Example: 01234567899
     * @bodyParam gender string required The gender of the user(must be female or male). Example: female
     * @bodyParam birth_day date required The birthday of the user(must bebefore:17 years ago). Example: 1999-11-09
     *
     * @response status=201 scenario=success {
     *  "message":"Successfully created your account, just verify it at your email !",
     *  "user":{"name":"ayadA1","email":"ayadA@gmail.com","phone":"01234567899",
     *  "gender":"female","birth_day":"1999-11-09","age":21,"reports":0,"ban":0,
     *  "ban_count":0,"VIP":0,"certified":0,"mac_address":"9E-4E-36-D3-D0-20 \\Device\\Tcpip_{4DBEE7F7-37E0-401A-B6C5-3EEFD760542C}",
     *  "updated_at":"2021-08-22T09:27:57.000000Z","created_at":"2021-08-22T09:27:57.000000Z","id":21},
     *  "AccessToken:":"eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC8xMjcuMC4wLjE6ODAwMFwvYXBpXC9yZWdpc3RlciIsImlhdCI6MTYyOTYyNDQ5MSwiZXhwIjoxNjI5NjI4MDkxLCJuYmYiOjE2Mjk2MjQ0OTEsImp0aSI6InVHTFY3ZEwzUVRSNGJSRUoiLCJzdWIiOjIxLCJwcnYiOiIyM2JkNWM4OTQ5ZjYwMGFkYjM5ZTcwMWM0MDA4NzJkYjdhNTk3NmY3In0.wkJgQT7RwycOA_3asmK8kdMRvV4UMpGXpeB2GBu6t0s"
     * }
     * @response status=403 scenario="failed" {
     *  "message": "Invalid, you used this device before !!"
     * }
     * @response status=400 scenario="failed" {
     *  "message": "Invalid data"
     *  "Errors in":{"email":["The email field is required."]}
     * }
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'phone' => ['required','regex:/(01)[0-9]{9}/','max:11'],
            'gender' => ['required','in:female,male'],
            'birth_day'=>['required','date','before:17 years ago'],
            'image'=>'image|max:2048'

        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid data','Errorsin'=>$validator->messages()], 400);
        }
        else {
            // and everything(except that he/she registered before(by saving his/her mac address))
            // **** notice*** this part will be used when we deploy this project

//             foreach (Mac_address::all() as $client){
//                 if($client->mac_address == exec('getmac')){
//                     return response()->json(['message' => 'Invalid, you used this device before !!'], 403);
//                 }
//             }
                $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone' => $request->phone,
                'gender' => $request->gender,
                'birth_day' => $request->birth_day,

                //System is responsible for incrementing user age.(users can’t edit his/her age )
                'age'=>Carbon::parse($request->birth_day)->age, //get user's age auto
                ///////////////////////////
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
            if($request->has('image')){
                $Image=$request->file('image');
                $ImageName='user_'.$user->id.'.'.$Image->getClientOriginalExtension();
                $path=$request->file('image')->move(public_path('\imgs\users_avatars\\'),$ImageName);
                $PhotoUrl='/imgs/users_avatars/'.$ImageName;
                $user->image=$PhotoUrl;
                $user->save();
            }
            event(new Registered($user));

            //Auth::login($user);
            $credentials = $request->only('email', 'password');
            $token = auth()->attempt($credentials);
            return response()->json(['message' => 'Successfully created your account, just verify it at your email !','user'=>$user,'mac_address'=>$mac,'AccessToken'=>$token], 201);
        }
    }
}
