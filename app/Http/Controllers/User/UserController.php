<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\InfoUser;
use App\Models\Message;
use App\Models\MessageImage;
use App\Models\SuggestedAnswers;
use App\Models\User;
use App\Models\UserCertification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

/**
 * @group User controller
 * this controller control in user's functions
 */
class UserController extends Controller
{
    /*
      //Restricting Female users to see only male users and vice versa.

      /**
       * this function show verse gender
       * @return \Illuminate\Http\JsonResponse|void
       * @response
      [
      {
      "id": 2,
      "name": "monica47",
      "email": "beatty.dorothea@example.com",
      "phone": "(682) 402-5102",
      "birth_day": "1984-06-01 00:00:00",
      "gender": "Ms.",
      "image": "https://via.placeholder.com/640x480.png/0077ee?text=earum",
      "reports": 8501891,
      "ban": 1,
      "ban_count": 877,
      "certified": 1,
      "VIP": 0,
      "created_at": "2021-08-17T16:23:33.000000Z",
      "updated_at": "2009-02-13T00:00:00.000000Z"
      }
      ]
       */
    /*
    public function searchGender()
    {
        $gender = Auth::user()->gender;

        $query = User::where('gender','!=', $gender)->get();
        return response()->json($query);
    }
*/

    //Anyone can delete his/her account with all the chat history

    //### at RegisteredUserController ###//
    // notice** i have commented it to complete my work because mac address doesnt allow me to create a new account
    // and everything(except that he/she registered before(by saving his/her mac address))

    /**
     * this function delete user and his chat history
     * @return JsonResponse
     * @response ['message':'Your account has been deleted!']
     */
    public function deleteAccount()
    {
        $user = Auth::user();
        $id = $user->id;

        if (Chat::find($id)) {
            $chat = Chat::where('user_1', '=', $id)->first();
            $message = Message::where('chat_id', '=', $chat->id)->first();
            $Messageimage = MessageImage::where('message_id', '=', $message->id)->first();

            $Messageimage->delete();
            $message->delete();
            $chat->delete();
        }


        $new = str_replace('/', '\\', $user->image);

        $imagepath = public_path() . $new;
        $imagename = '\user_' . $id . '.' . pathinfo($imagepath, PATHINFO_EXTENSION);

        File::delete($imagepath);
        $user->save();

        auth()->logout();

        InfoUser::where('user_id', $id)->delete();
        User::where('id', $id)->delete();

        return response()->json(['message' => 'Your account has been deleted']);
    }

    //filtration for vip and normal user

    /**
     * Filter by name,age,vip,banCount or gender as a default for Vip user and by only name for normal user.
     * @bodyParam name.
     * @bodyParam age.
     * @bodyParam VIP.
     * @bodyParam ban_count.
     * @bodyParam user_id and question_id and answer_id.
     * @return JsonResponse|void
     *
     */
    public function filter(Request $request)
    {
        $user = Auth::user();

        if ($user->VIP == 0) {

            if ($user->gender == 'male') {
                $query = User::select("*")->where('gender', '!=', $user->gender)->where('id', '!=', $user->id)->where(function ($query) use ($user, $request) {
                    $query->orWhere('name', 'LIKE', '%' . $request->name . '%');
                })->get();
                return response()->json($query);

            } else {
                $query = User::select("*")->where('gender', '!=', $user->gender)->where('id', '!=', $user->id)->where(function ($query) use ($user, $request) {
                    $query->orWhere('name', 'LIKE', '%' . $request->name . '%');
                })->get();

                return response()->json($query);
            }

        } else if ($user->VIP == 1) {
            if ($request->vip) {

                if ($request->vip) {
                    $query1 = DB::table('users')->
                    where('gender', '!=', $user->gender)->where('id', '!=', $user->id)->where('VIP', '=', $request->vip)->get();
                    return response()->json($query1);
                }
                else if($request->name&&$request->age&&$request->certified&&$request->ban_count){
                    $query1 = DB::table('users')->
                    where('gender', '!=', $user->gender)->where('id', '!=', $user->id)->where('VIP', '=', $request->vip)
                        ->where(function ($query) use ($user, $request) {
                            $query->where('name', 'LIKE', '%' . $request->name . '%')
                                ->where('age', 'LIKE', $request->age .'%')
                                ->where('certified', '=', $request->certified)
                                ->where('ban_count', 'LIKE', $request->ban_count .'%');
                        })->get();

                    return response()->json($query1);

                }else if($request->name&&$request->age&&$request->certified){
                    $query1 = DB::table('users')->
                    where('gender', '!=', $user->gender)->where('id', '!=', $user->id)->where('VIP', '=', $request->vip)
                        ->where(function ($query) use ($user, $request) {
                            $query->where('name', 'LIKE', '%' . $request->name . '%')
                                ->where('age', 'LIKE', $request->age .'%')
                                ->where('certified', '=', $request->certified);
                        })->get();

                    return response()->json($query1);

                }else if($request->name&&$request->age&&$request->ban_count){
                    $query1 = DB::table('users')->
                    where('gender', '!=', $user->gender)->where('id', '!=', $user->id)->where('VIP', '=', $request->vip)
                        ->where(function ($query) use ($user, $request) {
                            $query->where('name', 'LIKE', '%' . $request->name . '%')
                                ->where('age', 'LIKE', $request->age .'%')
                                ->where('ban_count', 'LIKE', $request->ban_count .'%');
                        })->get();

                    return response()->json($query1);

                }else if($request->name&&$request->certified&&$request->ban_count){
                    $query1 = DB::table('users')->
                    where('gender', '!=', $user->gender)->where('id', '!=', $user->id)->where('VIP', '=', $request->vip)
                        ->where(function ($query) use ($user, $request) {
                            $query->where('name', 'LIKE', '%' . $request->name . '%')
                                ->where('ban_count', 'LIKE', $request->ban_count .'%')
                                ->where('certified', '=', $request->certified);
                        })->get();

                    return response()->json($query1);

                }else if($request->name&&$request->age){
                    $query1 = DB::table('users')->
                    where('gender', '!=', $user->gender)->where('id', '!=', $user->id)->where('VIP', '=', $request->vip)
                        ->where(function ($query) use ($user, $request) {
                            $query->where('name', 'LIKE', '%' . $request->name . '%')
                                ->where('age', 'LIKE', $request->age .'%');
                        })->get();

                    return response()->json($query1);

                }else if($request->name&&$request->ban_count){
                    $query1 = DB::table('users')->
                    where('gender', '!=', $user->gender)->where('id', '!=', $user->id)->where('VIP', '=', $request->vip)
                        ->where(function ($query) use ($user, $request) {
                            $query->where('name', 'LIKE', '%' . $request->name . '%')
                                ->where('ban_count', 'LIKE', $request->ban_count .'%');
                        })->get();

                    return response()->json($query1);

                }else if($request->name&&$request->certified){
                    $query1 = DB::table('users')->
                    where('gender', '!=', $user->gender)->where('id', '!=', $user->id)->where('VIP', '=', $request->vip)
                        ->where(function ($query) use ($user, $request) {
                            $query->where('name', 'LIKE', '%' . $request->name . '%')
                                ->where('certified', '=', $request->certified);
                        })->get();

                    return response()->json($query1);

                }else if($request->name){
                    $query1 = DB::table('users')->
                    where('gender', '!=', $user->gender)->where('id', '!=', $user->id)->where('VIP', '=', $request->vip)
                        ->where(function ($query) use ($user, $request) {
                            $query->where('name', 'LIKE', '%' . $request->name . '%');
                        })->get();

                    return response()->json($query1);

                }else if($request->age){
                    $query1 = DB::table('users')->
                    where('gender', '!=', $user->gender)->where('id', '!=', $user->id)->where('VIP', '=', $request->vip)
                        ->where(function ($query) use ($user, $request) {
                            $query->where('age', 'LIKE', $request->age .'%');
                        })->get();

                    return response()->json($query1);

                }else if($request->certified){
                    $query1 = DB::table('users')->
                    where('gender', '!=', $user->gender)->where('id', '!=', $user->id)->where('VIP', '=', $request->vip)
                        ->where(function ($query) use ($user, $request) {
                            $query->where('certified', '=', $request->certified);

                        })->get();

                    return response()->json($query1);

                }else if($request->ban_count){
                    $query1 = DB::table('users')->
                    where('gender', '!=', $user->gender)->where('id', '!=', $user->id)->where('VIP', '=', $request->vip)
                        ->where(function ($query) use ($user, $request) {
                            $query->where('ban_count', 'LIKE', $request->ban_count .'%');
                        })->get();

                    return response()->json($query1);
                }

                else{
                    $query1 = DB::table('users')->
                    where('gender', '!=', $user->gender)->where('id', '!=', $user->id)->where('VIP', '=', $request->vip)
                        ->where(function ($query) use ($user, $request) {
                            $query->orWhere('name', 'LIKE', '%' . $request->name . '%')
                                ->orWhere('age', '=', $request->age )
                                ->orWhere('certified', '=', $request->certified)
                                ->orWhere('ban_count', '=', $request->ban_count);
                        })->get();

                    return response()->json($query1);

                }


///////////////////////////////////////////////////////////////////////

            } else if (!($request->vip)) {

                if($request->name&&$request->age&&$request->certified&&$request->ban_count){
                    $query1 = DB::table('users')->
                    where('gender', '!=', $user->gender)->where('id', '!=', $user->id)
                        ->where(function ($query) use ($user, $request) {
                            $query->where('name', 'LIKE', '%' . $request->name . '%')
                                ->where('age', 'LIKE', $request->age .'%')
                                ->where('certified', '=', $request->certified)
                                ->where('ban_count', 'LIKE', $request->ban_count .'%');
                        })->get();

                    return response()->json($query1);

                }else if($request->name&&$request->age&&$request->certified){
                    $query1 = DB::table('users')->
                    where('gender', '!=', $user->gender)->where('id', '!=', $user->id)
                        ->where(function ($query) use ($user, $request) {
                            $query->where('name', 'LIKE', '%' . $request->name . '%')
                                ->where('age', 'LIKE', $request->age .'%')
                                ->where('certified', '=', $request->certified);
                        })->get();

                    return response()->json($query1);

                }else if($request->name&&$request->age&&$request->ban_count){
                    $query1 = DB::table('users')->
                    where('gender', '!=', $user->gender)->where('id', '!=', $user->id)
                        ->where(function ($query) use ($user, $request) {
                            $query->where('name', 'LIKE', '%' . $request->name . '%')
                                ->where('age', 'LIKE', $request->age .'%')
                                ->where('ban_count', 'LIKE', $request->ban_count .'%');
                        })->get();

                    return response()->json($query1);

                }else if($request->name&&$request->certified&&$request->ban_count){
                    $query1 = DB::table('users')->
                    where('gender', '!=', $user->gender)->where('id', '!=', $user->id)
                        ->where(function ($query) use ($user, $request) {
                            $query->where('name', 'LIKE', '%' . $request->name . '%')
                                ->where('ban_count', 'LIKE', $request->ban_count .'%')
                                ->where('certified', '=', $request->certified);
                        })->get();

                    return response()->json($query1);

                }else if($request->name&&$request->age){
                    $query1 = DB::table('users')->
                    where('gender', '!=', $user->gender)->where('id', '!=', $user->id)
                        ->where(function ($query) use ($user, $request) {
                            $query->where('name', 'LIKE', '%' . $request->name . '%')
                                ->where('age', 'LIKE', $request->age .'%');
                        })->get();

                    return response()->json($query1);

                }else if($request->name&&$request->ban_count){
                    $query1 = DB::table('users')->
                    where('gender', '!=', $user->gender)->where('id', '!=', $user->id)
                        ->where(function ($query) use ($user, $request) {
                            $query->where('name', 'LIKE', '%' . $request->name . '%')
                                ->where('ban_count', 'LIKE', $request->ban_count .'%');
                        })->get();

                    return response()->json($query1);

                }else if($request->name&&$request->certified){
                    $query1 = DB::table('users')->
                    where('gender', '!=', $user->gender)->where('id', '!=', $user->id)
                        ->where(function ($query) use ($user, $request) {
                            $query->where('name', 'LIKE', '%' . $request->name . '%')
                                ->where('certified', '=', $request->certified);
                        })->get();

                    return response()->json($query1);

                }else if($request->name){
                    $query1 = DB::table('users')->
                    where('gender', '!=', $user->gender)->where('id', '!=', $user->id)
                        ->where(function ($query) use ($user, $request) {
                            $query->where('name', 'LIKE', '%' . $request->name . '%');
                        })->get();

                    return response()->json($query1);

                }else if($request->age){
                    $query1 = DB::table('users')->
                    where('gender', '!=', $user->gender)->where('id', '!=', $user->id)
                        ->where(function ($query) use ($user, $request) {
                            $query->where('age', 'LIKE', $request->age .'%');
                        })->get();

                    return response()->json($query1);

                }else if($request->certified){
                    $query1 = DB::table('users')->
                    where('gender', '!=', $user->gender)->where('id', '!=', $user->id)
                        ->where(function ($query) use ($user, $request) {
                            $query->where('certified', '=', $request->certified);

                        })->get();

                    return response()->json($query1);

                }else if($request->ban_count){
                    $query1 = DB::table('users')->
                    where('gender', '!=', $user->gender)->where('id', '!=', $user->id)
                        ->where(function ($query) use ($user, $request) {
                            $query->where('ban_count', 'LIKE', $request->ban_count .'%');
                        })->get();

                    return response()->json($query1);
                }

                else{
                    $query1 = DB::table('users')->
                    where('gender', '!=', $user->gender)->where('id', '!=', $user->id)
                        ->where(function ($query) use ($user, $request) {
                            $query->orWhere('name', 'LIKE', '%' . $request->name . '%')
                                ->orWhere('age', '=', $request->age )
                                ->orWhere('certified', '=', $request->certified)
                                ->orWhere('ban_count', '=', $request->ban_count);
                        })->get();

                    return response()->json($query1);

                }


///////////////////////////////////////////////////////////////////////
            } else if ($request->vip && $request->answer_id) {


                $answer = InfoUser::where('user_id', '=', $request->id)->where('question_id', '=', $request->question_id)->first();

                if($request->name&&$request->age&&$request->certified&&$request->ban_count){
                    $query1 = DB::table('users')->
                    where('gender', '!=', $user->gender)->where('id', '!=', $user->id)->where('VIP', '=', $request->vip)
                        ->where(function ($query) use ($user, $request) {
                            $query->where('name', 'LIKE', '%' . $request->name . '%')
                                ->where('age', 'LIKE', $request->age .'%')
                                ->where('certified', '=', $request->certified)
                                ->where('ban_count', 'LIKE', $request->ban_count .'%');
                        })->get();

                    $query2 = InfoUser::select("*")->where('answer_id', '=', $answer->answer_id);

                    $merge = $query1->merge($query2);
                    $query3 = $merge->all();

                    return response()->json($query3);

                }else if($request->name&&$request->age&&$request->certified){
                    $query1 = DB::table('users')->
                    where('gender', '!=', $user->gender)->where('id', '!=', $user->id)->where('VIP', '=', $request->vip)
                        ->where(function ($query) use ($user, $request) {
                            $query->where('name', 'LIKE', '%' . $request->name . '%')
                                ->where('age', 'LIKE', $request->age .'%')
                                ->where('certified', '=', $request->certified);
                        })->get();

                    $query2 = InfoUser::select("*")->where('answer_id', '=', $answer->answer_id);

                    $merge = $query1->merge($query2);
                    $query3 = $merge->all();

                    return response()->json($query3);

                }else if($request->name&&$request->age&&$request->ban_count){
                    $query1 = DB::table('users')->
                    where('gender', '!=', $user->gender)->where('id', '!=', $user->id)->where('VIP', '=', $request->vip)
                        ->where(function ($query) use ($user, $request) {
                            $query->where('name', 'LIKE', '%' . $request->name . '%')
                                ->where('age', 'LIKE', $request->age .'%')
                                ->where('ban_count', 'LIKE', $request->ban_count .'%');
                        })->get();

                    $query2 = InfoUser::select("*")->where('answer_id', '=', $answer->answer_id);

                    $merge = $query1->merge($query2);
                    $query3 = $merge->all();

                    return response()->json($query3);

                }else if($request->name&&$request->certified&&$request->ban_count){
                    $query1 = DB::table('users')->
                    where('gender', '!=', $user->gender)->where('id', '!=', $user->id)->where('VIP', '=', $request->vip)
                        ->where(function ($query) use ($user, $request) {
                            $query->where('name', 'LIKE', '%' . $request->name . '%')
                                ->where('ban_count', 'LIKE', $request->ban_count .'%')
                                ->where('certified', '=', $request->certified);
                        })->get();

                    $query2 = InfoUser::select("*")->where('answer_id', '=', $answer->answer_id);

                    $merge = $query1->merge($query2);
                    $query3 = $merge->all();

                    return response()->json($query3);

                }else if($request->name&&$request->age){
                    $query1 = DB::table('users')->
                    where('gender', '!=', $user->gender)->where('id', '!=', $user->id)->where('VIP', '=', $request->vip)
                        ->where(function ($query) use ($user, $request) {
                            $query->where('name', 'LIKE', '%' . $request->name . '%')
                                ->where('age', 'LIKE', $request->age .'%');
                        })->get();

                    $query2 = InfoUser::select("*")->where('answer_id', '=', $answer->answer_id);

                    $merge = $query1->merge($query2);
                    $query3 = $merge->all();

                    return response()->json($query3);

                }else if($request->name&&$request->ban_count){
                    $query1 = DB::table('users')->
                    where('gender', '!=', $user->gender)->where('id', '!=', $user->id)->where('VIP', '=', $request->vip)
                        ->where(function ($query) use ($user, $request) {
                            $query->where('name', 'LIKE', '%' . $request->name . '%')
                                ->where('ban_count', 'LIKE', $request->ban_count .'%');
                        })->get();

                    $query2 = InfoUser::select("*")->where('answer_id', '=', $answer->answer_id);

                    $merge = $query1->merge($query2);
                    $query3 = $merge->all();

                    return response()->json($query3);

                }else if($request->name&&$request->certified){
                    $query1 = DB::table('users')->
                    where('gender', '!=', $user->gender)->where('id', '!=', $user->id)->where('VIP', '=', $request->vip)
                        ->where(function ($query) use ($user, $request) {
                            $query->where('name', 'LIKE', '%' . $request->name . '%')
                                ->where('certified', '=', $request->certified);
                        })->get();

                    $query2 = InfoUser::select("*")->where('answer_id', '=', $answer->answer_id);

                    $merge = $query1->merge($query2);
                    $query3 = $merge->all();

                    return response()->json($query3);

                }else if($request->name){
                    $query1 = DB::table('users')->
                    where('gender', '!=', $user->gender)->where('id', '!=', $user->id)->where('VIP', '=', $request->vip)
                        ->where(function ($query) use ($user, $request) {
                            $query->where('name', 'LIKE', '%' . $request->name . '%');
                        })->get();

                    $query2 = InfoUser::select("*")->where('answer_id', '=', $answer->answer_id);

                    $merge = $query1->merge($query2);
                    $query3 = $merge->all();

                    return response()->json($query3);

                }else if($request->age){
                    $query1 = DB::table('users')->
                    where('gender', '!=', $user->gender)->where('id', '!=', $user->id)->where('VIP', '=', $request->vip)
                        ->where(function ($query) use ($user, $request) {
                            $query->where('age', 'LIKE', $request->age .'%');
                        })->get();

                    $query2 = InfoUser::select("*")->where('answer_id', '=', $answer->answer_id);

                    $merge = $query1->merge($query2);
                    $query3 = $merge->all();

                    return response()->json($query3);

                }else if($request->certified){
                    $query1 = DB::table('users')->
                    where('gender', '!=', $user->gender)->where('id', '!=', $user->id)->where('VIP', '=', $request->vip)
                        ->where(function ($query) use ($user, $request) {
                            $query->where('certified', '=', $request->certified);

                        })->get();

                    $query2 = InfoUser::select("*")->where('answer_id', '=', $answer->answer_id);

                    $merge = $query1->merge($query2);
                    $query3 = $merge->all();

                    return response()->json($query3);

                }else if($request->ban_count){
                    $query1 = DB::table('users')->
                    where('gender', '!=', $user->gender)->where('id', '!=', $user->id)->where('VIP', '=', $request->vip)
                        ->where(function ($query) use ($user, $request) {
                            $query->where('ban_count', 'LIKE', $request->ban_count .'%');
                        })->get();

                    $query2 = InfoUser::select("*")->where('answer_id', '=', $answer->answer_id);

                    $merge = $query1->merge($query2);
                    $query3 = $merge->all();

                    return response()->json($query3);                }

                else{
                    $query1 = DB::table('users')->
                    where('gender', '!=', $user->gender)->where('id', '!=', $user->id)->where('VIP', '=', $request->vip)
                        ->where(function ($query) use ($user, $request) {
                            $query->orWhere('name', 'LIKE', '%' . $request->name . '%')
                                ->orWhere('age', '=', $request->age )
                                ->orWhere('certified', '=', $request->certified)
                                ->orWhere('ban_count', '=', $request->ban_count);
                        })->get();

                    $query2 = InfoUser::select("*")->where('answer_id', '=', $answer->answer_id);

                    $merge = $query1->merge($query2);
                    $query3 = $merge->all();

                    return response()->json($query3);
                }

//////////////////////////////////////////////////////////////////

            } else {
                $answer = InfoUser::where('user_id', '=', $request->id)->where('question_id', '=', $request->question_id)->first();

                if($request->name&&$request->age&&$request->certified&&$request->ban_count){
                    $query1 = DB::table('users')->
                    where('gender', '!=', $user->gender)->where('id', '!=', $user->id)
                        ->where(function ($query) use ($user, $request) {
                            $query->where('name', 'LIKE', '%' . $request->name . '%')
                                ->where('age', 'LIKE', $request->age .'%')
                                ->where('certified', '=', $request->certified)
                                ->where('ban_count', 'LIKE', $request->ban_count .'%');
                        })->get();

                    $query2 = InfoUser::select("*")->where('answer_id', '=', $answer->answer_id);

                    $merge = $query1->merge($query2);
                    $query3 = $merge->all();

                    return response()->json($query3);

                }else if($request->name&&$request->age&&$request->certified){
                    $query1 = DB::table('users')->
                    where('gender', '!=', $user->gender)->where('id', '!=', $user->id)
                        ->where(function ($query) use ($user, $request) {
                            $query->where('name', 'LIKE', '%' . $request->name . '%')
                                ->where('age', 'LIKE', $request->age .'%')
                                ->where('certified', '=', $request->certified);
                        })->get();

                    $query2 = InfoUser::select("*")->where('answer_id', '=', $answer->answer_id);

                    $merge = $query1->merge($query2);
                    $query3 = $merge->all();

                    return response()->json($query3);

                }else if($request->name&&$request->age&&$request->ban_count){
                    $query1 = DB::table('users')->
                    where('gender', '!=', $user->gender)->where('id', '!=', $user->id)
                        ->where(function ($query) use ($user, $request) {
                            $query->where('name', 'LIKE', '%' . $request->name . '%')
                                ->where('age', 'LIKE', $request->age .'%')
                                ->where('ban_count', 'LIKE', $request->ban_count .'%');
                        })->get();

                    $query2 = InfoUser::select("*")->where('answer_id', '=', $answer->answer_id);

                    $merge = $query1->merge($query2);
                    $query3 = $merge->all();

                    return response()->json($query3);

                }else if($request->name&&$request->certified&&$request->ban_count){
                    $query1 = DB::table('users')->
                    where('gender', '!=', $user->gender)->where('id', '!=', $user->id)
                        ->where(function ($query) use ($user, $request) {
                            $query->where('name', 'LIKE', '%' . $request->name . '%')
                                ->where('ban_count', 'LIKE', $request->ban_count .'%')
                                ->where('certified', '=', $request->certified);
                        })->get();

                    $query2 = InfoUser::select("*")->where('answer_id', '=', $answer->answer_id);

                    $merge = $query1->merge($query2);
                    $query3 = $merge->all();

                    return response()->json($query3);

                }else if($request->name&&$request->age){
                    $query1 = DB::table('users')->
                    where('gender', '!=', $user->gender)->where('id', '!=', $user->id)
                        ->where(function ($query) use ($user, $request) {
                            $query->where('name', 'LIKE', '%' . $request->name . '%')
                                ->where('age', 'LIKE', $request->age .'%');
                        })->get();

                    $query2 = InfoUser::select("*")->where('answer_id', '=', $answer->answer_id);

                    $merge = $query1->merge($query2);
                    $query3 = $merge->all();

                    return response()->json($query3);

                }else if($request->name&&$request->ban_count){
                    $query1 = DB::table('users')->
                    where('gender', '!=', $user->gender)->where('id', '!=', $user->id)
                        ->where(function ($query) use ($user, $request) {
                            $query->where('name', 'LIKE', '%' . $request->name . '%')
                                ->where('ban_count', 'LIKE', $request->ban_count .'%');
                        })->get();

                    $query2 = InfoUser::select("*")->where('answer_id', '=', $answer->answer_id);

                    $merge = $query1->merge($query2);
                    $query3 = $merge->all();

                    return response()->json($query3);

                }else if($request->name&&$request->certified){
                    $query1 = DB::table('users')->
                    where('gender', '!=', $user->gender)->where('id', '!=', $user->id)
                        ->where(function ($query) use ($user, $request) {
                            $query->where('name', 'LIKE', '%' . $request->name . '%')
                                ->where('certified', '=', $request->certified);
                        })->get();

                    $query2 = InfoUser::select("*")->where('answer_id', '=', $answer->answer_id);

                    $merge = $query1->merge($query2);
                    $query3 = $merge->all();

                    return response()->json($query3);

                }else if($request->name){
                    $query1 = DB::table('users')->
                    where('gender', '!=', $user->gender)->where('id', '!=', $user->id)
                        ->where(function ($query) use ($user, $request) {
                            $query->where('name', 'LIKE', '%' . $request->name . '%');
                        })->get();

                    $query2 = InfoUser::select("*")->where('answer_id', '=', $answer->answer_id);

                    $merge = $query1->merge($query2);
                    $query3 = $merge->all();

                    return response()->json($query3);

                }else if($request->age){
                    $query1 = DB::table('users')->
                    where('gender', '!=', $user->gender)->where('id', '!=', $user->id)
                        ->where(function ($query) use ($user, $request) {
                            $query->where('age', 'LIKE', $request->age .'%');
                        })->get();

                    $query2 = InfoUser::select("*")->where('answer_id', '=', $answer->answer_id);

                    $merge = $query1->merge($query2);
                    $query3 = $merge->all();

                    return response()->json($query3);

                }else if($request->certified){
                    $query1 = DB::table('users')->
                    where('gender', '!=', $user->gender)->where('id', '!=', $user->id)
                        ->where(function ($query) use ($user, $request) {
                            $query->where('certified', '=', $request->certified);

                        })->get();

                    $query2 = InfoUser::select("*")->where('answer_id', '=', $answer->answer_id);

                    $merge = $query1->merge($query2);
                    $query3 = $merge->all();

                    return response()->json($query3);

                }else if($request->ban_count){
                    $query1 = DB::table('users')->
                    where('gender', '!=', $user->gender)->where('id', '!=', $user->id)
                        ->where(function ($query) use ($user, $request) {
                            $query->where('ban_count', 'LIKE', $request->ban_count .'%');
                        })->get();

                    $query2 = InfoUser::select("*")->where('answer_id', '=', $answer->answer_id);

                    $merge = $query1->merge($query2);
                    $query3 = $merge->all();

                    return response()->json($query3);                }

            }
        }
    }


    // certified user

    /**
     * certifiedUser takes images as a parameter
     * @param Request $request required
     * @return string
     * @response ['message':'You are certified']
     */
    public function certifiedUser(Request $request)
    {
        $valid = Validator::make($request->all(), array(
            'image' => 'required|min:0',
            'image.*' => 'required|image|nullable|mimes:jpeg,png,jpg,gif|max:2048',
        ));

        $images = array();
        if ($files = $request->file('image')) {
            foreach ($files as $file) {
                $name = 'user_' . Auth::user()->id . '.' . $file->getClientOriginalName();
                $file->move(public_path('imgs\certified_users\\'), $name);
                $images[] = $name;
            }
        }

        UserCertification::create([
            'image' => implode('|', $images),
            'user_id' => Auth::user()->id,
        ]);

        return response()->json(['message' => 'You are certified']);
    }

    public function getAllUserNotCertified(){

        return response()->json(['cert'=>User::where('certified','=',0)->get()]);
    }
    public function adminCertify(Request $request){
        $user = User::where('id','=',$request->id)->first();
        $user->certified = $request->action;
        $user->save();
    }
    // update user's info

    /**
     * deleteImage delete user's profile image
     * Delete user's image if he send request ''
     * @param Request $request
     * @return JsonResponse
     * @response ['message':'Updated successfully !!']
     */

    public function deleteImage(Request $request){
	 if ($request->hasFile('image')=='') {
            $user = Auth::user();
            $new = str_replace('/', '\\', $user->image);

            $imagepath = public_path() . $new;
            $imagename = '\user_' . $user->id . '.' . pathinfo($imagepath, PATHINFO_EXTENSION);
            File::delete($imagepath);
            $user->image = null;
            $user->save();

	   return response()->json(['message' => 'Deleted successfully !!']);

    	}

    }

    /**
     * EditInfo Edit user's phone, profile image and his/her answers
     * @param Request $request
     * @return JsonResponse
     * @response ['message':'Updated successfully !!']
     */
    public function EditInfo(Request $request)
    {
        $user = Auth::user();

        $answer = InfoUser::where('user_id', '=', $user->id)->where('question_id', '=', $request->question_id)->first();

        $bannerData = User::find($user->id);



        if ($answer) {
            $answer->answer_id = $request->new_answer;
            $answer_content = SuggestedAnswers::where('id', $request->new_answer)->select('answer')->first();
            $answer->answer = $answer_content->answer;
            $answer->save();
        }

        if ($request->phone) {
            $bannerData->phone = $request->phone;

           $bannerData->save();
        }


        if ($request->hasFile('image')) {
            $new = str_replace('/', '\\', $bannerData->image);
            $image_path = public_path($new);
            if (File::exists($image_path)) {
                File::delete($image_path);
            }
            $bannerImage = $request->file('image');
            $imgName = 'user_' . $user->id . '.' . $bannerImage->getClientOriginalExtension();
            $destinationPath = public_path('\imgs\users_avatars\\');
            $bannerImage->move($destinationPath, $imgName);
            $url = '/imgs/users_avatars/' . $imgName;
        } else {
            $url = $bannerData->image;
        }

        $bannerData->image = $url;

        $bannerData->save();

        return response()->json(['message' => 'Updated successfully !!']);

    }

}
