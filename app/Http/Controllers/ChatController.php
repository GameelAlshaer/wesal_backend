<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Chat;
use App\Models\Block;
use App\Models\User;
use App\Models\Message;
use App\Models\MessageImage;
use App\Notifications\NewMsg;
use App\Models\Requests as Requests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Events\MessageSent;
use App\Events\MessageSeen;
use App\Events\DeleteMessage;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * @group Chat Controller
 * @authenticated
 * APIs for handling chat features such as (start new chat, send new msg or pic,
 * delete msg, num of msgs or reports, status of msg)
 */
class ChatController extends Controller
{
    /**
     * Start New Chat
     * @bodyParam userid2 int required the id of the user to start chat with
     * @response status=201 scenario=succes{
     * "user : ": 12,
     *   "successfully started chat with user : ": "9",
     *   "Chat details :": {
     *      "user_1": 12,
     *       "user_2": "9",
     *      "updated_at": "2021-08-25T21:05:48.000000Z",
     *       "created_at": "2021-08-25T21:05:48.000000Z",
     *       "id": 17
     *  }
     * }
     * @response status=405 scenario="failed"{
     *      "Request between user : ": 12,
     *      "and user : ": "9",
     *      "message": "is not approved, cannot start chat",
     * }
     * @response status=400 scenario="failed"{
     *      "message": "Invalid data",
     *       "Errors in": {
     *           "userid2": [
     *               "The userid2 field is required."
     *          ]
     *       }
     * }
     * @response status=404 scenario="failed"{
     *      'message'=> 'no user found by This id '
     * }
     * @response status=404 scenario="failed"{
     *      'message'=> 'no request found between the 2 users'
     * }
     * @response status=403 scenario="failed"{
     *      'message'=> 'you blocked this user, cannot start chat'
     * }
     * @response status=403 scenario="failed"{
     *      'message'=> 'this user blocked you,  cannot start chat'
     * }
     */
    public function startChat(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'userid2' => 'required|int',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid data', 'Errors in' => $validator->messages()], 400);
        } else {
            $chat1 = Chat::where('user_1', Auth::user()->id)->where('user_2', $request->userid2)->get();
            $chat2 = Chat::where('user_2', Auth::user()->id)->where('user_1', $request->userid2)->get();
            if (!$chat1->isEmpty() || !$chat2->isEmpty()) {
                return response()->json(['message' => 'already have a chat'], 200);
            }
            try {
                $user = User::findOrFail($request->userid2);
            } catch (ModelNotFoundException $e) {
                return response()->json(['message' => 'no user found by This id ',], 404);
            }
            $blockStatus = Block::where('blocker_id', Auth::user()->id)->where('blocked_id', $request->userid2)->get();
            $blockStatus2 = Block::where('blocked_id', Auth::user()->id)->where('blocker_id', $request->userid2)->get();
            if (!$blockStatus->isEmpty()) {
                return response()->json(['message' => 'you blocked this user,  cannot start chat'], 403);
            }
            if (!$blockStatus2->isEmpty()) {
                return response()->json(['message' => 'this user blocked you, cannot start chat'], 403);
            }
            if (Auth::user()->VIP) //sender is VIP
            {
                $NewChat = new Chat();
                $NewChat->user_1 = Auth::user()->id;
                $NewChat->user_2 = $request->userid2;
                $NewChat->save();
                return response()->json(['user : ' => Auth::user()->id, 'successfully started chat with user : ' => $request->userid2, 'Chat details :' => $NewChat], 201);
            } else { //sender isn't VIP
                $requestStatus = Requests::where('sender_id', Auth::user()->id)->where('reciever_id', $request->userid2)->get();
                $requestStatus2 = Requests::where('reciever_id', Auth::user()->id)->where('sender_id', $request->userid2)->get();
                if ($requestStatus->isEmpty() && $requestStatus2->isEmpty()) {
                    return response()->json(['message' => 'no request found between the 2 users'], 400);
                }
                if (!$requestStatus->isEmpty()) {
                    if ($requestStatus->pluck('status')[0] == 1) // assmption that 1 means that req is approved
                    {
                        $NewChat = new Chat();
                        $NewChat->user_1 = Auth::user()->id;
                        $NewChat->user_2 = $request->userid2;
                        $NewChat->save();
                        return response()->json(['user : ' => Auth::user()->id, 'successfully started chat with user : ' => $request->userid2, 'Chat details :' => $NewChat], 201);
                    } else { // otherwise [denied,pending,..]
                        return response()->json(['Request between user : ' => Auth::user()->id, 'and user : ' => $request->userid2, 'message' => 'isnot approved, cannot start chat'], 405);
                    }
                }
                if (!$requestStatus2->isEmpty()) {
                    if ($requestStatus2->pluck('status')[0] == 1) // assmption that 1 means that req is approved
                    {
                        $NewChat = new Chat();
                        $NewChat->user_1 = Auth::user()->id;
                        $NewChat->user_2 = $request->userid2;
                        $NewChat->save();
                        return response()->json(['user : ' => Auth::user()->id, 'successfully started chat with user : ' => $request->userid2, 'Chat details :' => $NewChat], 201);
                    } else { // otherwise [denied,pending,..]
                        return response()->json(['Request between user : ' => Auth::user()->id, 'and user : ' => $request->userid2, 'message' => 'isnot approved, cannot start chat'], 405);
                    }
                }

            }

        }
    }

    /**
     * Send New Msg
     * @bodyParam chat_id int required the id of the chat
     * @bodyParam content string required the content of the msg
     * @bodyParam replymsg int
     * @response status=201 scenario=succes{
     *     "msg sent"
     * }
     * @response status=405 scenario="failed"{
     *    "can not send more than 4 msgs to this account"
     * }
     * @response status=400 scenario="failed"{
     *      "message": "Invalid data",
     *       "Errors in": {
     *           "userid2": [
     *               "The chat_id field is required."
     *          ]
     *       }
     * }
     * @response status=404 scenario="failed"{
     *      'message'=> 'no chat found by This id '
     * }
     * @response status=403 scenario="failed"{
     *      'message'=> 'you blocked this user, cannot send msg'
     * }
     * @response status=403 scenario="failed"{
     *      'message'=> 'this user blocked you, cannot send msg'
     * }
     * @response status=405 scenario="failed"{
     *      'message'=> 'you don't have access to this chat'
     * }
     *
     */
//    public function sendMsg(Request $request)
//    {
////       return response()->json('message' => $request->getContent());
//        $validator = Validator::make($request->all(), [
//            'chat_id' => 'required|int',
//            'content' => 'required|string',
//            'replymsg' => 'int',
//        ]);
//
//        if ($validator->fails()) {
//            return response()->json(['message' => 'Invalid data', 'Errors in' => $validator->messages()], 400);
//        } else {
//            try {
//                $chat = Chat::findOrFail($request->chat_id);
//            } catch (ModelNotFoundException $e) {
//                return response()->json(['message' => 'no chat found by This id '], 404);
//            }
//
//            // set the receiver and the sender
//            if (Auth::user()->id != $chat->user_1 && Auth::user()->id != $chat->user_2) {
//                return response()->json(['message' => 'you dont have access to this chat '], 405);
//            }
//            if ($chat->user_1 == Auth::user()->id) {
//                $reciever_id = $chat->user_2;
//            } else {
//                $reciever_id = $chat->user_1;
//            }
//
//            // check if one of the users has blocked the other one
////            $blockStatus = Block::where('blocker_id', Auth::user()->id)->where('blocked_id', $reciever_id)->get();
////            $blockStatus2 = Block::where('blocked_id', Auth::user()->id)->where('blocker_id', $reciever_id)->get();
////            if (!$blockStatus->isEmpty()) {
////                return response()->json(['message' => 'you blocked this user, cannot send a message'], 403);
////            }
////            if (!$blockStatus2->isEmpty()) {
////                return response()->json(['message' => 'this user blocked you, cannot send a message'], 403);
////            }
////            else{
////                return response()->json(['message' => 'block error'], 404);
////            }
//
//
//            if (Auth::user()->VIP) {
//                //sender is VIP
//                $numOfsendMsgs = Message::where('chat_id', $request->chat_id)->where('sender_id', Auth::user()->id)->where('reciever_id', $reciever_id)->get();
//                $limit = count($numOfsendMsgs);
//                $numOfrecMsgs = Message::where('chat_id', $request->chat_id)->where('sender_id', $reciever_id)->where('reciever_id', Auth::user()->id)->get();
//                $limit2 = count($numOfrecMsgs);
//                if ($limit < 4 or $limit2 > 0) {
//                    $NewMsg = new Message();
//                    $NewMsg->chat_id = $request->chat_id;
//                    $NewMsg->sender_id = Auth::user()->id;
//                    $NewMsg->reciever_id = $reciever_id;
//                    $NewMsg->content = $request->content;
//                    $NewMsg->img_url = null;
//                    $NewMsg->status = 0;
//                    $NewMsg->isImg = 0;
//                    $NewMsg->replyMsg = $request->replymsg;
//                    $NewMsg->save();
//                    $ReplyMessage = Message::where('id', $request->replymsg)->get();
//
//                    // until this line everything works fine
////                    return response()->json(['message' => 'broadcast worked ?'], 404);
//                    try {
//                        MessageSent::dispatch(Auth::user(), $NewMsg, $ReplyMessage, $NewMsg->chat_id);
//                        broadcast(new MessageSent(Auth::user(), $NewMsg, $ReplyMessage, $NewMsg->chat_id))->toOthers();
//                    }catch (\Exception $e){
//                        return response()->view($e);
//                    }
//
//                    return response()->json(['message' => 'broadcast worked ?'], 404);
//
//                    try{
//                        User::findOrFail($reciever_id)->notify(new NewMsg());
//                        // this catch caused an error => catch any , not just ModelNotFound
//                    } catch (\Exception $e) {
////                        return response()->json(['message' => 'receiver not found by This id'], 404);
////                        return response()->json(['message' => Auth::user()->id], 404);
//                    }
//                    return response()->json(['msg sent', 'msg_id' => $NewMsg->id, 'created_at' => $NewMsg->created_at], 201);
//                }
//                else {
//                    return response()->json(['can not send more than 4 msgs to this account'], 405);
//                }
//            }
//            else {
//                //sender isn't VIP
//                $NewMsg = new Message();
//                $NewMsg->chat_id = $request->chat_id;
//                $NewMsg->sender_id = Auth::user()->id;
//                $NewMsg->reciever_id = $reciever_id;
//                $NewMsg->content = $request->content;
//                $NewMsg->img_url = null;
//                $NewMsg->status = 0;
//                $NewMsg->isImg = 0;
//                $NewMsg->replyMsg = $request->replymsg;
//                $NewMsg->save();
//                $ReplyMessage = Message::where('id', $request->replymsg)->get();
//                broadcast(new MessageSent(Auth::user(), $NewMsg, $ReplyMessage, $NewMsg->chat_id))->toOthers();
//                User::findOrFail($reciever_id)->notify(new NewMsg);
//                return response()->json(['msg sent', 'msg_id' => $NewMsg->id, 'created_at' => $NewMsg->created_at], 201);
//            }
//        }
//    }

    public  function sendMsg(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'chat_id' => 'required|int',
            'content' => 'required|string',
            'replymsg' => 'int',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid data','Errors in'=>$validator->messages()], 400);
        } else {
            try{
                $chat = Chat::findOrFail($request->chat_id);
            } catch (ModelNotFoundException $e)
            {
                return response()->json(['message' => 'no chat found by This id '],404);
            }

            if ( Auth::user()->id != $chat->user_1 && Auth::user()->id != $chat->user_2)
            {
                return response()->json(['message' => 'you dont have access to this chat '],405);
            }
            if ($chat->user_1 == Auth::user()->id)
            {
                $reciever_id = $chat->user_2;
            }else{
                $reciever_id = $chat->user_1;
            }
            $blockStatus = Block::where('blocker_id',Auth::user()->id)->where('blocked_id',$reciever_id)->get();
            $blockStatus2 = Block::where('blocked_id',Auth::user()->id)->where('blocker_id',$reciever_id)->get();
            if (!$blockStatus->isEmpty() )
            {
                return response()->json(['message' => 'you blocked this user, cannot send a message'],403);
            }
            if (!$blockStatus2->isEmpty())
            {
                return response()->json(['message' => 'this user blocked you, cannot send a message'],403);
            }

            if(Auth::user()->VIP ) //sender is VIP
            {
                $numOfsendMsgs = Message::where('chat_id',$request->chat_id)->where('sender_id',Auth::user()->id)->where('reciever_id',$reciever_id)->get();
                $limit = count($numOfsendMsgs);
                $numOfrecMsgs = Message::where('chat_id',$request->chat_id)->where('sender_id',$reciever_id)->where('reciever_id',Auth::user()->id)->get();
                $limit2 = count($numOfrecMsgs);
                if($limit < 4 or $limit2 > 0){
                    $NewMsg = new Message();
                    $NewMsg->chat_id = $request->chat_id;
                    $NewMsg->sender_id = Auth::user()->id;
                    $NewMsg->reciever_id = $reciever_id;
                    $NewMsg->content = $request->content;
                    $NewMsg->img_url = null;
                    $NewMsg->status = 0;
                    $NewMsg->isImg = 0;
                    $NewMsg->replyMsg = $request->replymsg;
                    $NewMsg->save();
                    $ReplyMessage = Message::where('id',$request->replymsg)->get();

                    broadcast(new MessageSent(Auth::user(), $NewMsg,$ReplyMessage , $NewMsg->chat_id))->toOthers();

                    User::findOrFail($reciever_id)->notify(new NewMsg);
                    return response()->json(['msg sent','msg_id'=>$NewMsg->id,'created_at'=>$NewMsg->created_at],201);
                }
                else{
                    return response()->json(['can not send more than 4 msgs to this account'],405);
                }
            }
            else{ //sender isn't VIP
                $NewMsg = new Message();
                $NewMsg->chat_id = $request->chat_id;
                $NewMsg->sender_id = Auth::user()->id;
                $NewMsg->reciever_id = $reciever_id;
                $NewMsg->content = $request->content;
                $NewMsg->img_url = null;
                $NewMsg->status = 0;
                $NewMsg->isImg = 0;
                $NewMsg->replyMsg = $request->replymsg;
                $NewMsg->save();
                $ReplyMessage = Message::where('id',$request->replymsg)->get();

                broadcast(new MessageSent(Auth::user(), $NewMsg,$ReplyMessage, $NewMsg->chat_id))->toOthers();

                User::findOrFail($reciever_id)->notify(new NewMsg);
                return response()->json(['msg sent','msg_id'=>$NewMsg->id,'created_at'=>$NewMsg->created_at],201);
            }
        }
    }
    /**
     * Send New Pic
     * @bodyParam chat_id int required the id of the chat
     * @bodyParam image  required
     * @bodyParam content string
     * @bodyParam replymsg int
     * @response status=201 scenario=succes{
     *     "pic sent"
     * }
     * @response status=403 scenario="failed"{
     *    "This feature isnot available for Free members"
     * }
     * @response status=400 scenario="failed"{
     *      "message": "Invalid data",
     *       "Errors in": {
     *           "chat_id": [
     *               "The chat_id field is required."
     *          ]
     *       }
     * }
     * @response status=404 scenario="failed"{
     *      'message'=> 'no chat found by This id '
     * }
     * @response status=403 scenario="failed"{
     *      'message'=> 'you blocked this user, cannot send msg'
     * }
     * @response status=403 scenario="failed"{
     *      'message'=> 'this user blocked you, cannot send msg'
     * }
     * @response status=405 scenario="failed"{
     *      'message'=> 'you dont have access to this chat'
     * }
     */
    public function sendPic(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'chat_id' => 'required|int',
            'image' => 'image',
            'content' => 'string',
            'replymsg' => 'int',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid data', 'Errors in' => $validator->messages()], 400);
        } else {
            try {
                $chat = Chat::findOrFail($request->chat_id);
            } catch (ModelNotFoundException $e) {
                return response()->json(['message' => 'no chat found by This id ',], 404);
            }
            if (Auth::user()->id != $chat->user_1 && Auth::user()->id != $chat->user_2) {
                return response()->json(['message' => 'you dont have access to this chat '], 405);
            }
            if ($chat->user_1 == Auth::user()->id) {
                $reciever_id = $chat->user_2;
            } else {
                $reciever_id = $chat->user_1;
            }
            $blockStatus = Block::where('blocker_id', Auth::user()->id)->where('blocked_id', $reciever_id)->get();
            $blockStatus2 = Block::where('blocked_id', Auth::user()->id)->where('blocker_id', $reciever_id)->get();
            if (!$blockStatus->isEmpty()) {
                return response()->json(['message' => 'you blocked this user, cannot send pic'], 403);
            }
            if (!$blockStatus2->isEmpty()) {
                return response()->json(['message' => 'this user blocked you, cannot send pic'], 403);
            }
            $NewMsg = new Message();
            $NewMsg->chat_id = $request->chat_id;
            $NewMsg->sender_id = Auth::user()->id;
            $NewMsg->reciever_id = $reciever_id;
            $NewMsg->status = 0;
            $NewMsg->isImg = 1;
            $NewMsg->replyMsg = $request->replymsg;
            $Image = $request->file('image');
            $ImageName = 'chat_' . $NewMsg->chat_id . '_sender_' . $NewMsg->sender_id . '_at_' . date('Y_m_d_H_i_s') . '.' . $Image->getClientOriginalExtension();
            $path = $request->file('image')->move(public_path('/imgs/chat_imgs'), $ImageName);
            $PhotoUrl = '/imgs/chat_imgs/' . $ImageName;
            $NewMsg->img_url = $PhotoUrl;
            $NewMsg->content = $request->content;
            $NewMsg->save();
            $ReplyMessage = Message::where('id', $request->replymsg)->get();
            broadcast(new MessageSent(Auth::user(), $NewMsg, $ReplyMessage, $NewMsg->chat_id))->toOthers();
            User::findOrFail($reciever_id)->notify(new NewMsg);
            return response()->json(['pic sent', 'msg_id' => $NewMsg->id, 'created_at' => $NewMsg->created_at, 'content' => $NewMsg->content, 'imgUrl' => $NewMsg->img_url], 201);
        }
    }
//**********************************Commented**************************
    /**
     * Get Number Of Reports For Each User
     * @response scenario=succes{
     *     [
     *       [
     *          {
     *               "name": "keshawn.thompson",
     *               "reports": 243873
     *           },
     *           {
     *              "name": "beryl.stark",
     *               "reports": 98310538
     *           },
     *           {
     *               "name": "nkuhic",
     *               "reports": 11399
     *           },
     *       ]
     *   ]
     * }
     */
//    public function numOfReports(){
//         $users = User::select('name','reports')->orderBy('id','asc')->get();
//         return response()->json([$users],200);
//    }
    //**********************************Commented**************************

    /**
     * Get Number Of Msgs For Current User
     * @response scenario=succes{
     *         4
     * }
     */
    //assumption that status of the msgs(sent 0/seen 1)
    public function numOfMsgs()
    {
        $msgs = Message::where('reciever_id', Auth::user()->id)->where('status', "=", 0)->get();
        $num = count($msgs);
        return response()->json([$num], 200);
    }

    /**
     * Get Number Of Msgs Of Certain Chat For Current User
     * @response scenario=succes{
     *         1
     * }
     */
    public function numOfMsgsOfCertainChat($chatid)
    {
        $msgs = Message::where('chat_id', $chatid)->where('reciever_id', Auth::user()->id)->where('status', "=", 0)->get();
        $num = count($msgs);
        return response()->json([$num], 200);
    }

    /**
     * Get Status Of Msg For Certain Msg
     * @response status=200 scenario=succes{
     *         'delivered'
     * }
     * @response status=404 scenario="failed"{
     *      'message'=> 'no msg found by This id '
     * }
     * @response status=403 scenario="failed"{
     *     " msg status isn't correct"
     * }
     * @response status=403 scenario="failed"{
     *     " you arenot the sender of this msg "
     * }
     */
    public function statusOfMsgs($id)
    {
        try {
            $msgs = Message::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'no msg found by This id '], 404);
        }
        if (Auth::user()->id == $msgs->sender_id) {
            if ($msgs->status == 0)
                $status = 'sent';
            else if ($msgs->status == 1)
                $status = 'seen';
            else
                return response()->json(["msg status isn't correct "], 403);
            return response()->json([$status], 200);
        } else {
            return response()->json(["you arenot the sender of this msg "], 403);
        }

    }

    /**
     * Delete Msg
     * @response scenario=succes{
     *         "msg is deleted successfully "
     * }
     * @response status=404 scenario="failed"{
     *      'message'=> 'no msg found by This id '
     * }
     * @response status=403 scenario="failed"{
     *     " you arenot the sender of this msg "
     * }
     */
    public function deleteMsg($id)
    {
        try {
            $msg = Message::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'no msg found by This id '], 404);
        }
        $sender_id = $msg->sender_id;
        if (Auth::user()->id == $sender_id) {
            $msgStatus = $msg->status;
            $msg->isDeleted = true;
            $msg->save();
            broadcast(new DeleteMessage($id, $msg->chat_id))->toOthers();
            return response()->json(["msg is deleted successfully "], 200);
        } else {
            return response()->json(["you arenot the sender of this msg "], 403);
        }
    }

    /**
     * Msg Seen
     * @bodyParam chat_id int required the id of the chat
     * @bodyParam time  required
     * @response status=400 scenario="failed"{
     *      "message": "Invalid data",
     *       "Errors in": {
     *           "chat_id": [
     *               "The chat_id field is required."
     *          ]
     *       }
     * }
     * @response status=200 scenario=succes{
     *         'msgs seen'
     * }
     * @response status=404 scenario="failed"{
     *      'message'=> 'no chat found by This id '
     * }
     */
    public function readMsg(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'chat_id' => 'required|int',
            'time' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid data', 'Errors in' => $validator->messages()], 400);
        } else {
            try {
                $chat = Chat::findOrFail($request->chat_id);
            } catch (ModelNotFoundException $e) {
                return response()->json(['message' => 'no chat found by This id ',], 404);
            }

            $unreadmsgs = Message::where('chat_id', $request->chat_id)->where('reciever_id', Auth::user()->id)->where('created_at', '<=', $request->time)->where('status', 0)->orderBy('created_at', 'DESC')->get();
            foreach ($unreadmsgs as $msg) {
                $msg->status = 1;
                $msg->save();
            }

            if (!$unreadmsgs->isEmpty()) {
                broadcast(new MessageSeen(Auth::user(), $unreadmsgs[0], $request->chat_id))->toOthers();
                return response()->json(["msgs seen"], 200);
            }
            return response()->json(["No Unread msgs to be seen"], 200);
        }
    }

    /**
     * Get List Of User's Chats
     * @response scenario=succes{
     * [
     *      {
     *           "name": "nrogahn",
     *           "image": "https://via.placeholder.com/640x480.png/008866?text=magni",
     *           "user_id": 1,
     *           "chat_id": 10,
     *           "online": 1,
     *           "content": "HE was.' 'I never could abide figures!' And with that she had looked under it, and yet it was too slippery; and when she had not gone (We know it to the beginning again?' Alice ventured to say.",
     *           "created_at": "2021-09-15T14:01:42.000000Z",
     *           "status": 0,
     *           "sender_id": 11,
     *           "sender_name": "omniaO30",
     *          "unreadcount": 0
     *      },
     *   ]
     */

    public function ListAllChats()
    {
        $output = [];
        $users = [];
        $chatids = [];
        foreach (Chat::all() as $chat) {
            if ($chat->user_1 == Auth::user()->id) {
                array_push($users, $chat->user_2);
                array_push($chatids, $chat->id);
            }
            if ($chat->user_2 == Auth::user()->id) {
                array_push($users, $chat->user_1);
                array_push($chatids, $chat->id);
            }
        }
        $usersData = [];
        foreach ($users as $user) {
            array_push($usersData, User::where('id', $user)->select('name', 'image', 'id', 'online')->get());
        }
        $i = 0;
        foreach ($usersData as $user) {
            $blockStatus = Block::where('blocker_id', Auth::user()->id)->where('blocked_id', $user->pluck('id')[0])->get();
            $blockStatus2 = Block::where('blocked_id', Auth::user()->id)->where('blocker_id', $user->pluck('id')[0])->get();
            $block;
            $blocker_id = "";
            $block_id = "";
            if (!$blockStatus->isEmpty() || !$blockStatus2->isEmpty()) {
                $block = true;
                $blocker_id = !$blockStatus->isEmpty() ? $blockStatus[0]->blocker_id : $blockStatus2[0]->blocker_id;
                $block_id = !$blockStatus->isEmpty() ? $blockStatus[0]->id : $blockStatus2[0]->id;
            } else {
                $block = false;
            }
            $msg = Message::select('id', 'content', 'created_at', 'status', 'sender_id', 'isImg', 'img_url', 'isDeleted')->where('chat_id', $chatids[$i])->orderBy('created_at', 'DESC')->first();
            $requestStatus = Requests::where('sender_id', Auth::user()->id)->where('reciever_id', $user->pluck('id')[0])->get();
            $requestStatus2 = Requests::where('reciever_id', Auth::user()->id)->where('sender_id', $user->pluck('id')[0])->get();
            $reqStatus = 0;

            if ((!$requestStatus->isEmpty() || !$requestStatus2->isEmpty()) && ($requestStatus->pluck('status')[0] == 1 || $requestStatus2->pluck('status')[0] == 1)) {
                $reqStatus = 1;
            }
            if ($msg != null) {
                $sender_name = User::where('id', $msg->sender_id)->select('name')->get();
                $unreadcount = Message::where('chat_id', $chatids[$i])->where('reciever_id', Auth::user()->id)->where('status', "=", 0)->get();
                $num = count($unreadcount);
                $output[] = [
                    'name' => $user->pluck('name')[0],
                    'image' => $user->pluck('image')[0],
                    'user_id' => $user->pluck('id')[0],
                    'chat_id' => $chatids[$i],
                    'online' => $user->pluck('online')[0],
                    'block' => $block,
                    'blocker_id' => $blocker_id,
                    'block_id' => $block_id,
                    'msg_id' => $msg->id,
                    'content' => $msg->content,
                    'img_url' => $msg->img_url,
                    'created_at' => $msg->created_at,
                    'status' => $msg->status,
                    'isImg' => $msg->isImg,
                    'isDeleted' => $msg->isDeleted,
                    'sender_id' => $msg->sender_id,
                    'sender_name' => $sender_name->pluck('name')[0],
                    'unreadcount' => $num,
                    'RequestStatus' => $reqStatus,
                ];
            } else {
                $ChatCreatedAt = Chat::where('id', $chatids[$i])->select('created_at')->get();
                $output[] = [
                    'name' => $user->pluck('name')[0],
                    'image' => $user->pluck('image')[0],
                    'user_id' => $user->pluck('id')[0],
                    'chat_id' => $chatids[$i],
                    'online' => $user->pluck('online')[0],
                    'block' => $block,
                    'blocker_id' => $blocker_id,
                    'block_id' => $block_id,
                    'msg_id' => '',
                    'content' => '',
                    'img_url' => '',
                    'isDeleted' => 0,
                    'created_at' => $ChatCreatedAt[0]->created_at,
                    'status' => '',
                    'isImg' => '',
                    'sender_id' => '',
                    'sender_name' => '',
                    'unreadcount' => '',
                    'RequestStatus' => $reqStatus,
                ];
            }
            $i++;
        }
        return response()->json($output);
    }

    /**
     * Fetch Msgs from certain Chat
     * @response scenario=succes{
     * [
     *      {
     *          "id": 10,
     *          "chat_id": 10,
     *          "sender_id": 1,
     *          "sender_name": "zreichert",
     *          "reciever_id": 11,
     *          "reciever_name": "omniaO30",
     *          "content": "What would become of it; so, after hunting all about it!' Last came a rumbling of little Alice herself, and nibbled a little feeble, squeaking voice, ('That's Bill,' thought Alice,) 'Well, I should.",
     *          "status": 52,
     *          "isImg": null,
     *          "replyMsg": null,
     *          "created_at": "2021-09-20T16:30:48.000000Z",
     *          "reply_id": "",
     *          "reply_chat_id": "",
     *          "reply_sender_id": "",
     *          "reply_sender_name": "",
     *          "reply_reciever_id": "",
     *          "reply_reciever_name": "",
     *          "reply_content": "",
     *          "reply_status": "",
     *         "reply_isImg": "",
     *          "reply_created_at": ""
     *      },
     *  ]
     * }
     * @response status=404 scenario="failed"{
     *      'message'=> 'no chat found by This id '
     * }
     */
    public function fetchMsgs($chatID)
    {
        try {
            $chat = Chat::findOrFail($chatID);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'no chat found by This id '], 404);
        }
        $output = [];
        $msgs = Message::where('chat_id', $chatID)->get();
        foreach ($msgs as $msg) {
            $reply_id = '';
            $reply_chat = '';
            $reply_sender_id = '';
            $reply_rec_id = '';
            $reply_sender_name = '';
            $reply_rec_name = '';
            $reply_status = '';
            $reply_content = '';
            $reply_imgurl = '';
            $reply_img = '';
            $reply_time = '';
            if ($msg->replyMsg) {
                $sender_name_rep = User::where('id', $msg->reciever_id)->select('name')->get();
                $reciever_name_rep = User::where('id', $msg->sender_id)->select('name')->get();
                $replyMsg = Message::findOrFail($msg->replyMsg);
                $reply_id = $replyMsg->id;
                $reply_chat = $replyMsg->chat_id;
                $reply_sender_id = $replyMsg->sender_id;
                $reply_rec_id = $replyMsg->reciever_id;
                $reply_sender_name = $sender_name_rep->pluck('name')[0];
                $reply_rec_name = $reciever_name_rep->pluck('name')[0];
                $reply_status = $replyMsg->status;
                $reply_content = $replyMsg->content;
                $reply_imgurl = $replyMsg->img_url;
                $reply_img = $replyMsg->isImg;
                $reply_time = $replyMsg->created_at;
            }
            $sender_name = User::where('id', $msg->sender_id)->select('name')->get();
            $reciever_name = User::where('id', $msg->reciever_id)->select('name')->get();
            $output[] = [
                'id' => $msg->id,
                'chat_id' => $msg->chat_id,
                'sender_id' => $msg->sender_id,
                'sender_name' => $sender_name->pluck('name')[0],
                'reciever_id' => $msg->reciever_id,
                'reciever_name' => $reciever_name->pluck('name')[0],
                'content' => $msg->content,
                'img_url' => $msg->img_url,
                'status' => $msg->status,
                'isDeleted' => $msg->isDeleted,
                'isImg' => $msg->isImg,
                'replyMsg' => $msg->replyMsg,
                'created_at' => $msg->created_at,
                ///
                'reply_id' => $reply_id,
                'reply_chat_id' => $reply_chat,
                'reply_sender_id' => $reply_sender_id,
                'reply_sender_name' => $reply_sender_name,
                'reply_reciever_id' => $reply_rec_id,
                'reply_reciever_name' => $reply_rec_name,
                'reply_content' => $reply_content,
                'reply_img_url' => $reply_imgurl,
                'reply_status' => $reply_status,
                'reply_isImg' => $reply_img,
                'reply_created_at' => $reply_time,
            ];
        }
        return response()->json($output);
    }


}
