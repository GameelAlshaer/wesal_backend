<?php

namespace App\Http\Controllers\Requests_Blocks_Fav_Reports;

use App\Http\Controllers\Controller;
use App\Models\Requests;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

/**
 * @authenticated
 * @group Request Controller
 * this class can do the following features:
 * 1- Create request with another user.
 * 2- Update status of request from pending to accept and start chatting or refused it.
 * 3- Retrieve all requests
 * 4- delete request
 */
class RequestsController extends Controller
{
    /**
     * This function to create request with another user.
     * @bodyParam recevier int required
     * @return \Illuminate\Http\JsonResponse
     * @response status=400 scenario="failed" if you requested this user before
     * {"message" : "You Make this request before!"}
     * @response status=201 {"message" => "Requests Created Successfully"}
     */
    public function requests(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'recevier' => 'required|int',
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid data', 'Errors in' => $validator->messages()], 400);
        }
        $requests = User::where('id', '=', $request->recevier)->first();
        if (Auth::id() == $request->recevier) {
            return \response()->json(["message" => "You can't create request with yourself!"], 400);
        }
        if ($requests == NULL) {
            return \response()->json(["message" => "No user with this id"], 404);
        }
        foreach (Requests::all() as $requ) {
            if ($requ->sender_id == Auth::user()->id && $requ->reciever_id == $request->recevier) {
                return \response()->json(["message" => "You Make this request before!"], 400);
            }
        }
        $req = new Requests();
        $req->reciever_id = $request->recevier;
        $req->sender_id = Auth::user()->id;
        $req->status = 0;
        $req->save();
        return \response()->json(["message" => "Requests Created Successfully"], 201);
    }

    /**
     * This function to change status of request from pending to confirm ... etc.
     * @bodyParam sender int required
     * @bodyParam  replay int required  take one of the following choices 1 --> accept , 2 --> refuse required integer
     * @return \Illuminate\Http\JsonResponse|void
     * @response {"message" : "Requests Updated Successfully"}
     */
    public function decisionForRequest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sender' => 'required|int',
            'replay' => 'required|int',
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid data', 'Errors in' => $validator->messages()], 400);
        }
        $dec = Requests::where('sender_id', '=', $request->sender)->first();
        if ($dec != NULL && $dec->reciever_id == Auth::id()) {
            $dec->status = $request->replay;
			$dec->save();
            return \response()->json(["message" => "Requests Updated Successfully"], 200);
        } elseif ($dec == NULL) {
            return \response()->json(["message" => "This user_id doesn't exist"], 404);
        } else {
            return \response()->json(["message" => "This user_id does not have a request"], 400);

        }

    }
    public function RequestsRecieved()
    {
        $req_send = [];
        $req_rec =[];
        foreach (Requests::all() as $req) {
            foreach (User::all() as $user) {
                if ($req->sender_id == $user->id && $req->reciever_id == Auth::id()){
                    $req_rec[] = array(
                        "id" => $req->id,
                        "name" => $user->name,
                        "status" => $req->status,
                        "sender_id" =>$user->id,
                        "age" => $user->age,
                        "image" => $user->image,
                    );
                    break;
                }
            }
        }
        return response()->json( $req_rec);
    }
    public function RequestsSent()
    {
        $req_send = [];
        $req_rec =[];
        foreach (Requests::all() as $req) {
            foreach (User::all() as $user) {
                if ($req->sender_id == Auth::id() && $req->reciever_id == $user->id){
                    $req_send[] = array(
                        "id" => $req->id,
                        "name" => $user->name,
                        "req_id" =>$user->id,
                        "status" => $req->status,
                        "age" => $user->age,
                        "image" => $user->image);
                    break;
                }
            }
        }

        return response()->json($req_send);
    }
    /**
     * this function to return all request for auth user.
     * @return \Illuminate\Http\JsonResponse
     * @response
     * {
     * "requests_sent": [
     * {
     * "id": 1,
     * "sender_id": 1,
     * "reciever_id": 10,
     * "status": 12,
     * "created_at": "1996-01-11T00:00:00.000000Z",
     * "updated_at": "2007-10-29T00:00:00.000000Z"
     * },
     * {
     * "id": 11,
     * "sender_id": 1,
     * "reciever_id": 2,
     * "status": 0,
     * "created_at": "2021-08-17T16:15:48.000000Z",
     * "updated_at": "2021-08-17T00:00:00.000000Z"
     * },
     * {
     * "id": 18,
     * "sender_id": 1,
     * "reciever_id": 3,
     * "status": 1,
     * "created_at": "2021-08-17T18:46:39.000000Z",
     * "updated_at": "2021-08-17T00:00:00.000000Z"
     * },
     * {
     * "id": 19,
     * "sender_id": 1,
     * "reciever_id": 10,
     * "status": 616849219,
     * "created_at": "1994-06-23T00:00:00.000000Z",
     * "updated_at": "2006-10-09T00:00:00.000000Z"
     * },
     * {
     * "id": 29,
     * "sender_id": 1,
     * "reciever_id": 10,
     * "status": 962946913,
     * "created_at": "2010-04-24T00:00:00.000000Z",
     * "updated_at": "1982-06-13T00:00:00.000000Z"
     * }
     * ],
     * "requests_received": [
     * {
     * "id": 28,
     * "sender_id": 10,
     * "reciever_id": 1,
     * "status": 22,
     * "created_at": "1971-07-30T00:00:00.000000Z",
     * "updated_at": "1998-01-19T00:00:00.000000Z"
     * },
     * {
     * "id": 38,
     * "sender_id": 10,
     * "reciever_id": 1,
     * "status": 4060416,
     * "created_at": "1977-01-12T00:00:00.000000Z",
     * "updated_at": "1973-04-20T00:00:00.000000Z"
     * }
     * ]
     * }
     */

    public function getAllRequests()
    {
		$req_send = [];
		$req_rec =[];
        foreach (Requests::all() as $req) {
            foreach (User::all() as $user) {
                if ($req->sender_id == Auth::id() && $req->reciever_id == $user->id){
                    $req_send[] = array(
                        "id" => $req->id,
                        "name" => $user->name,
                        "req_id" =>$user->id,
                        "status" => $req->status,
                        "age" => $user->age,
						"image" => $user->image);
                    break;
                }
            }
        }
        foreach (Requests::all() as $req) {
            foreach (User::all() as $user) {
                if ($req->sender_id == $user->id && $req->reciever_id == Auth::id()){
                    $req_rec[] = array(
                        "id" => $req->id,
                        "name" => $user->name,
                        "status" => $req->status,
						"sender_id" =>$user->id,
                        "image" => $user->image,
                        "age" => $user->age,);
                    break;
                }
            }
        }
        return response()->json(['requests_sent' => $req_send, 'requests_received' => $req_rec]);
    }

    /**
     * this function to delete specific user.
     * @bodyParam id int required id of request
     * @return \Illuminate\Http\JsonResponse
     * @response {"message" : "Requests has been Deleted Successfully"}
     */
    public function deleteRequest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|int'
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid data', 'Errors in' => $validator->messages()], 400);
        }
        $req = Requests::where('id', '=', $request->id)->first();
        if ($req == NULL) {
            return \response()->json(["message" => "no request with this id"], 404);
        } else if ($req->sender_id == Auth::id()) {
            Requests::where('id', $request->id)->delete();
            return \response()->json(["message" => "Request has been Deleted Successfully"], 200);
        } else {
            return \response()->json(["message" => "you are not the sender for this request"], 400);
        }
    }

    public function getUser(Request $request){
        return response()->json(User::where('id','=',$request->id)->first());
    }
}
