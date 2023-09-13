<?php

namespace App\Http\Controllers\Requests_Blocks_Fav_Reports;

use App\Http\Controllers\Controller;
use App\Models\Block;
use App\Models\Fav;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

/**
 * @authenticated
 * @group Fav Controller
 * This class has the following features:
 * 1- add someone to fav list.
 * 2- delete fav from fav table.
 * 3- retrieve all fav persons.
 */
class FavController extends Controller
{
    /**
     * This function to add someone to fav table.
     * @bodyParam  recevier_id int required
     * @return \Illuminate\Http\JsonResponse
     * @response {"message" : "You Add this user before"}
     */
    public function addFriend(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'recevier_id' => 'required|int',
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid data', 'Errors in' => $validator->messages()], 400);
        }
        if (Auth::id() == $request->recevier_id) {
            return \response()->json(["message" => "receiver id and sender can't be same!"], 400);
        }
        $fav = User::where('id', '=', $request->recevier_id)->first();
        if ($fav == NULL) {
            return \response()->json(["message" => "no user with this id"], 404);
        }
        $f = Fav::where('user_1', '=', Auth::id())->where('user_2', '=', $request->recevier_id)->first();

        if ($f != NULL) {
            return \response()->json(["message" => "You added this user before"], 400);
        }
        foreach (Block::all() as $block) {
            if ($block->blocker_id == Auth::id() && $block->blocked_id == $request->recevier_id) {
                return \response()->json(["message" => "You Blocked this user before you can't add him to fav table!"], 400);
            }
        }
        $getUser = User::where('id', '=', $request->recevier_id)->first();
        $user = new Fav();
        $user->user_1 = Auth::id();
        $user->user_2 = $request->recevier_id;
        $user->user2_image = $getUser->image;
        $user->name = $getUser->name;
        $user->age = $getUser->age;
        $user->save();
        return \response()->json(["message" => "Adding has been Created Successfully"], 201);
    }

    /** This function can retrieve alll persons from fav table.
     * @return \Illuminate\Http\JsonResponse
     * @response
     * {
     * "User": [
     * {
     * "id": 1,
     * "user_1": 1,
     * "user_2": 10,
     * "created_at": "1974-07-03T00:00:00.000000Z",
     * "updated_at": "1981-01-31T00:00:00.000000Z"
     * },
     * {
     * "id": 11,
     * "user_1": 1,
     * "user_2": 3,
     * "created_at": "2021-08-17T19:25:55.000000Z",
     * "updated_at": "2021-08-17T00:00:00.000000Z"
     * },
     * {
     * "id": 12,
     * "user_1": 1,
     * "user_2": 10,
     * "created_at": "1974-11-28T00:00:00.000000Z",
     * "updated_at": "1971-09-05T00:00:00.000000Z"
     * },
     * {
     * "id": 22,
     * "user_1": 1,
     * "user_2": 10,
     * "created_at": "1995-11-18T00:00:00.000000Z",
     * "updated_at": "2005-07-14T00:00:00.000000Z"
     * }
     * ]
     * }
     */
    public function getAllFriends()
    {
        $users = Fav::where('user_1', Auth::user()->id)->get();
        return response()->json($users);
    }

    /**
     * This function can remove person from fav table.
     * @bodyParam id  int required
     * @return \Illuminate\Http\JsonResponse
     * @response {"message" : "User has been Deleted Successfully"}
     */
    public function removeFromFav(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|int',
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid data', 'Errors in' => $validator->messages()], 400);
        }
        $fav = Fav::where('id', '=', $request->id)->first();
        if ($fav == NULL) {
            return \response()->json(["message" => "no fav user with this id"], 404);
        } else if ($fav->user_1 == Auth::id()) {
            Fav::where('id', $request->id)->delete();
            return \response()->json(["message" => "User has been Deleted Successfully"], 200);
        } else {
            return \response()->json(["message" => "You not are the sender"], 400);
        }
    }

    /**
     * This function return all users who liked user profile.
     * @return \Illuminate\Http\JsonResponse
     */
    public function showAllWhoSendLike()
    {  
        return response()->json(Fav::where('user_2', '=', Auth::user()->id)->get());
    }
}
