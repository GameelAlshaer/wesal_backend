<?php

namespace App\Http\Controllers\Requests_Blocks_Fav_Reports;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\Report;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

/**
 * @authenticated
 * @group Report Controller
 * This class can Create report for message.
 */
class ReportController extends Controller
{


    /**
     * This function to create report message
     * @bodyParam message_id  int required
     * @bodyParam details : comment of user
     * @return \Illuminate\Http\JsonResponse|void
     * @response status=201 {"message" : "report has been Created Successfully"}
     * @response status=404 {"message" : "id of message not found"}
     * @response status=400 {"message" : "You reported this message before!"}
     */

    public function report(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'message_id' => 'required|int',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid data', 'Errors in' => $validator->messages()], 400);
        }

        $rep = Message::where('id', '=', $request->message_id)->first();
        if ($rep != NULL) {
            $re = Report::where('message_id', '=', $request->message_id)->first();
             if ($rep->reciever_id != Auth::id()) {
                return \response()->json(["message" => "you are not receiver for this message!"], 400);
            } else if ($re != NULL) {
                 return \response()->json(["message" => "You reported this message before!"], 400);
             }
        } else {
            return \response()->json(["message" => "no message with this id"], 404);
        }
        foreach (Message::all() as $message) {
            if ($message->id == $request->message_id) {
                if ($message->sender_id == Auth::id()) {
                    foreach (User::all() as $user) {
                        if ($user->id == $message->reciever_id) {
                            $user->reports += 1;
                            $user->save();
                            $report = new Report();
                            $report->sender_img = $user->image;
                            $report->message_id = $request->message_id;
                            $report->action = 0;
                            $report->details = $request->details;
                            $report->save();
                            return \response()->json(["message" => "report has been Created Successfully"], 201);
                        }
                    }
                } else if ($message->reciever_id == Auth::id()) {
                    foreach (User::all() as $user) {
                        if ($user->id == $message->sender_id) {
                            $user->reports += 1;
                            $user->save();
                            $report = new Report();
                            $report->sender_img = $user->image;
                            $report->message_id = $request->message_id;
                            $report->action = 0;
                            $report->details = $request->details;
                            $report->save();
                            return \response()->json(["message" => "report has been Created Successfully"], 201);
                        }
                    }
                } else {
                    return \response()->json(["message" => "id of message is not found"], 404);

                }
            }
        }
    }
}
