<?php

namespace App\Http\Controllers\Requests_Blocks_Fav_Reports;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Block;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

/**
 * @authenticated
 * @group Block Controller
 * This class has the following features:
 * 1- create block for user.
 * 2- remove a block.
 * 3- retrieve all blocks.
 */


class BlockController extends Controller
{
    /**
     *  This function to crate block for specific user.
     * @bodyParam  reciever_id int required
     * @return \Illuminate\Http\JsonResponse
     * @response {"message": "Block Created Successfully"}
     */
    public function Blockfriend(Request $request){
        $validator = Validator::make($request->all(), [
            'reciever_id' => 'required|int',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid data', 'Errors in' => $validator->messages()], 400);
        }
        if(Auth::id() == $request->reciever_id){
            return \response()->json(["message" => "receiver id and sender can't be same!"], 400);
        }
        $blo = User::where('id', '=', $request->reciever_id)->first();
        if($blo == NULL){
            return \response()->json(["message" => "No user with this id"], 404);
        }
        foreach (Block::all() as $block){
            if($block->blocker_id == Auth::user()->id && $block->blocked_id == $request->reciever_id){
                return \response()->json(["message" => "You Blocked this user before"], 400);
            }
        }
        foreach (Message::all() as $mess){
            if(Auth::id() == $mess->sender_id && $request->reciever_id == $mess->reciever_id){
                $user = User::where('id','=',$request->reciever_id)->first();
                $block = new Block();
                $block->blocker_id = Auth::id();
                $block->blocked_id = $request->reciever_id;
                $block->blocked_image = $user->image;
                $block->name = $user->name;
                $block->age = $user->age;
                $block->save();
                return \response()->json(["message" => "Block Created Successfully"], 201);
            }
        }
        return \response()->json(["message" => "You not have message with user"], 400);

    }

    /** This function to retrieve all blocks.
     * @return \Illuminate\Http\JsonResponse
     * @response 200
     {
    "blocks": [
    {
    "id": 12,
    "blocker_id": 1,
    "blocked_id": 10,
    "created_at": "1974-05-06T00:00:00.000000Z",
    "updated_at": "2008-08-29T00:00:00.000000Z"
    }]}
     */
    public function getAllBlocks(){
        $blocks = Block::where('blocker_id',Auth::user()->id)->get();
        return response()->json($blocks,200);
    }

    /**
     * This function to remove blocked user.
     * @bodyParam  blockId int required
     * @return \Illuminate\Http\JsonResponse
     * @response status = "201" {"message" : "Block has been Deleted Successfully"}
     * @response status = "400"
     */
    public function removeBlock(Request $request){
        $validator = Validator::make($request->all(), [
            'blockId' => 'required|int',
            'blockerId' => 'int',
            'blockedId' => 'int',
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid data', 'Errors in' => $validator->messages()], 400);
        }
        $block = Block::where('id', '=', $request->blockId)->first();
        if($block == NULL){
            if($request->blockerId!=NULL && $request->blockedId!=NULL)
            {
                $block = Block::where('blocker_id',$request->blockerId)->where('blocked_id',$request->blockedId)->first();
                if($block != NULL){
                    Block::where('id',$block->id)->delete();
                    return \response()->json(["message" => "Block has been Deleted Successfully"], 200);
                }
            }
            return \response()->json(["message" => "no block with this id"], 404);
        } else if($block->blocker_id == Auth::id()){
            Block::where('id',$request->blockId)->delete();
            return \response()->json(["message" => "Block has been Deleted Successfully"], 200);

        }
        else{
            return \response()->json(["message" => "you are not the blocker for this block"], 400);

        }
    }
}
