<?php

namespace App\Http\Controllers;
use App\Models\Block;
use App\Models\Chat;
use App\Models\Fav;
use App\Models\InfoUser;
use App\Models\InfoUserQuestion;
use App\Models\Message;
use App\Models\MessageImage;
use App\Models\Report;
use App\Models\UserCertification;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
//use Illuminate\Console\Scheduling\Schedule;
//use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Models\User;
use App\Http\Controllers\Controller;
use http\Env\Response;
use App\Models\SuggestedAnswers;
use App\Models\Questions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

/**
 * @group Admin Controller-1
 * @authenticated
 * APIS for admin ( to see all users with any method,
 * to ban fake users, to see reports and take action on it)
 */

class AdminController1 extends Controller
{

    /** Get All Users' Info
     *
     * This function to return all users with all their info
     *
     * @response 200 "All_Users_info": [
     * {
     * "id": 1,
     * "name": "electa.reilly",
     * "email": "bbauch@example.com",
     * "email_verified_at": "2021-08-25T19:01:58.000000Z",
     * "phone": "812.867.3712",
     * "birth_day": "2002-10-25",
     * "age": 1366,
     * "gender": "Male",
     * "image": "https://via.placeholder.com/640x480.png/00bb22?text=ut",
     * "reports": 7,
     * "ban": 0,
     * "ban_count": 780,
     * "certified": 1,
     * "VIP": 0,
     * "created_at": "2021-08-25T01:47:16.000000Z",
     * "updated_at": "2021-08-25T11:09:25.000000Z",
     * "mac_address": "lWWFYF2V8P",
     * "id_number": "cb2a52b5-e196-31c4-9b88-08c73069151d",
     * "online": 0
     * },
     * {
     * "id": 2,
     * "name": "bashirian.misael",
     * "email": "jayde.langosh@example.org",
     * "email_verified_at": "2021-08-25T09:44:09.000000Z",
     * "phone": "(564) 298-5747",
     * "birth_day": "1977-11-20",
     * "age": 8846844,
     * "gender": "Male",
     * "image": "https://via.placeholder.com/640x480.png/00dd00?text=veritatis",
     * "reports": 579,
     * "ban": 1,
     * "ban_count": 5495324,
     * "certified": 0,
     * "VIP": 0,
     * "created_at": "2021-08-25T19:13:52.000000Z",
     * "updated_at": "2013-10-29T15:06:04.000000Z",
     * "mac_address": "gk9F0GFdaT",
     * "id_number": "a887f7d1-f4b2-3f07-ba3f-c36c3b498e2b",
     * "online": 0
     * }]
     */
    public function showAllUsersInfo()
    {
        $users = User::get()->all();
        return response()->json([
            'All_Users_info' => $users,
            'status' => 200
        ]);
        if(!$users)
        {
            return response()->json([
                'All_Users_info' => $users,
                'status' => 400
            ]);
        }
    }


    public function getUserbyID(Request $request){
        return response()->json(User::where('id','=',$request->id)->first());
    }


    public function getUserQues(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Invalid data',
                'Errors in' => $validator->messages()], 400);
        } else {
            $a = InfoUser::where('user_id', $request->user_id)->get()->first();
            if (!$a) {
                return response()->json([
                    'message' => 'This user id doesnt exist'
                ]);
            } else {
                $output = [];
                $infousers = InfoUser::where('user_id', $request->user_id)
                    ->get();


                foreach ($infousers as $infouser) {

                    $question = Questions::where('id', $infouser->question_id)
                        ->select('question', 'id')
                        ->get()
                        ->first();

                    $answerid = InfoUser::where('user_id', $request->user_id)
                        ->where('question_id', $infouser->question_id)
                        ->pluck('answer_id')
                        ->toArray();

                    $answer = SuggestedAnswers::where('id', $answerid[0])
                        ->select('answer')
                        ->get();
                    $hide = InfoUser::where('question_id',$infouser->question_id)
                        ->where('user_id',$request->user_id)
                        ->select('hidden')
                        ->get();
                    $output[] = [[$question],$hide, $answer];
                }
                return response()->json($output);

            }
        }
    }
    /** show list of users
     *
     * This function to return list of users with their info with any (one)(required) method as input
     * (id, full name,age, email, VIP ( (int = 1) to get VIP users),
     * gender ( Female or Male),  date of creation(timestamp), birthday(date))
     *
     * @queryParam id int The id of the user.
     * @queryParam name string The name of the user.
     * @queryParam email string The email of the user.
     * @queryParam gender string The gender of the user.
     * @queryParam dateOfCreation timestamp required The date of creation .
     * @queryParam birthdayDate date required The birthday_Date of the user.
     * @queryParam VIP int '1' to get VIP users.
     * @queryParam age int  The age of the user.
     *
     * @response 200 {
     * "msg": "users are found by This email",
     * "Users_info": [
     * {
     * "id": 1,
     * "name": "electa.reilly",
     * "email": "bbauch@example.com",
     * "email_verified_at": "2021-08-25T19:01:58.000000Z",
     * "phone": "812.867.3712",
     * "birth_day": "2002-10-25",
     * "age": 1366,
     * "gender": "Male",
     * "image": "https://via.placeholder.com/640x480.png/00bb22?text=ut",
     * "reports": 7,
     * "ban": 0,
     * "ban_count": 780,
     * "certified": 1,
     * "VIP": 0,
     * "created_at": "2021-08-25T01:47:16.000000Z",
     * "updated_at": "2021-08-25T11:09:25.000000Z",
     * "mac_address": "lWWFYF2V8P",
     * "id_number": "cb2a52b5-e196-31c4-9b88-08c73069151d",
     * "online": 0
     * }
     * ]
     * }
     * @response {
     * "msg": " No users are found by This name",
     * "Users_info": []
     * }
     */
    public function showUsersByMethod(Request $request)
    {
        if(!$request->dateOfCreation && !$request->name && !$request->gender && !$request->VIP && !$request->online &&
            !$request->id && !$request->email  && !$request->birthdayDate && !$request->age&& !$request->free&& !$request->cert)
        {
            return response()->json([
                'msg' => 'No inputs',
                'status' => 400
            ]);
        }
        $method = "";
        if($request->id) {
            $method = "id";
            $id = $request->id;
            $users= User::where('id' , $id )->get()->all();
        }
        else if($request->name && !$request->gender && !$request->VIP && !$request->cert ) {
            $method = "name";
            $name = $request->name;
            $users=User::where('name', 'LIKE', "%{$name}%")
                ->orWhere('name', 'LIKE', "%{$name}%")
                ->get();
            //$users= User::whereLike('name' , $name )->get();
        }
        else if($request->gender && !$request->name && !$request->VIP && !$request->cert) {
            $method = "gender";
            $gender = $request->gender;
            $users= User::where('gender' , $gender)->get()->all();
        }
        else if($request->age) {
            $method = "age";
            $age = $request->age;
            $users=User::where('age', 'LIKE', "%{$age}%")
                ->orWhere('age', 'LIKE', "%{$age}%")
                ->get();
        }
        else if($request->email) {
            $method = "email";
            $email = $request->email;
            $users=User::where('email', 'LIKE', "%{$email}%")
                ->orWhere('email', 'LIKE', "%{$email}%")
                ->get();
        }
        else if($request->VIP && !$request->gender && !$request->name && !$request->cert) {
            $method = "VIP";
            $VIP = $request->VIP;
            $users= User::where('VIP' , $VIP )->get()->all();
        }
        else if($request->birthdayDate) {
            $method = "birth_day";
            $birth_day = $request->birthdayDate;
            $users=User::where('birth_day', 'LIKE', "%{$birth_day}%")
                ->orWhere('birth_day', 'LIKE', "%{$birth_day}%")
                ->get();
        }
        else if($request->free) {
            $method = "free";
            $free = $request->free;
            $users= User::where('VIP' , 0)->get()->all();
        }
        
        else if($request->cert && !$request->gender && !$request->VIP && !$request->name) {
            $method = "cert";
            $cert = $request->cert;
            $users= User::where('certified' , $cert)->get()->all();
        }
        else if ($request->name && $request->VIP && $request->gender && $request->cert)
        {
            $users= User::where('name' ,'LIKE', "%{$request->name}%")
             ->where('VIP', 'LIKE', "%{$request->VIP}%")
             ->where('certified' ,'LIKE', "%{$request->cert}%")
             ->where('gender',$request->gender)
             ->get()->all();
        }
        
        else if ($request->name && $request->VIP && $request->gender )
        {
            $users= User::where('name' ,'LIKE', "%{$request->name}%")
             ->where('VIP', 'LIKE', "%{$request->VIP}%")
             ->where('gender',$request->gender)
             ->get()->all();
        }
        
        else if ($request->name && $request->VIP  && $request->cert)
        {
            $users= User::where('name' ,'LIKE', "%{$request->name}%")
             ->where('VIP', 'LIKE', "%{$request->VIP}%")
             ->where('certified' ,'LIKE', "%{$request->cert}%")
             ->get()->all();
        }
        
        else if ($request->name  && $request->gender && $request->cert)
        {
            $users= User::where('name' ,'LIKE', "%{$request->name}%")
             ->where('certified' ,'LIKE', "%{$request->cert}%")
             ->where('gender',$request->gender)
             ->get()->all();
        }
        
        else if ( $request->VIP && $request->gender && $request->cert)
        {
            $users= User::where('VIP', 'LIKE', "%{$request->VIP}%")
             ->where('certified' ,'LIKE', "%{$request->cert}%")
             ->where('gender',$request->gender)
             ->get()->all();
        }
        else if ( $request->name && $request->gender)
        {
            $users= User::where('name', 'LIKE', "%{$request->name}%")
             ->where('gender',$request->gender)
             ->get()->all();
        }
        
        else if ( $request->name && $request->VIP)
        {
            $users= User::where('name', 'LIKE', "%{$request->name}%")
             ->where('VIP', 'LIKE', "%{$request->VIP}%")
             ->get()->all();
        }
        
        else if ( $request->name && $request->gender)
        {
            $users= User::where('name', 'LIKE', "%{$request->name}%")
             ->where('gender',$request->gender)
             ->get()->all();
        }
        
        else if ( $request->VIP && $request->gender)
        {
            $users= User::where('VIP', 'LIKE', "%{$request->VIP}%")
             ->where('gender',$request->gender)
             ->get()->all();
        }
        
        else if ( $request->VIP && $request->cert)
        {
            $users= User::where('VIP', 'LIKE', "%{$request->VIP}%")
             ->where('certified', 'LIKE', "%{$request->cert}%")
             ->get()->all();
        }
        
        
        else if ( $request->gender && $request->cert)
        {
            $users= User::where('gender',$request->gender)
             ->where('certified', 'LIKE', "%{$request->cert}%")
             ->get()->all();
        }
        if(! $users)
            return response()->json([
                'msg' => ' No users found by This ' .$method,
                'Users_info' => $users
            ]);
        return response()->json([
            'msg' => 'users found by This '.$method,
            'Users_info' => $users
        ]);
    }


    /**
     * Banning fake users
     *
     * This function to delete user's account with his/her id (required)
     *
     * @queryParam user_id int required The id of the user.
     *
     * @response 200 {
     * "msg": "Ban has been done successfully by deleting the account of this user"
     * }
     * @response 400 {
     * "msg": "no user found by This id ",
     * "status": 400
     * }
     */
    public function banningFakeUsers(Request $request)
    {
        // send id
        if(!$request->user_id)
        {
            return response()->json([
                'msg' => 'No inputs',
                 'status' => 400
            ]);
        }
        $id = $request->user_id;
        try{
            $user = User::findOrFail($id);//$user = User::where('id' , $user_id )->firstOrFail();
        } catch (ModelNotFoundException $e)
        {
            return response()->json([
                'msg' => 'no user found by This id ',
                'status' => 400
            ]);
        }

        if(!$user)
        {
            return response()->json([
                'msg' => 'no user found by This id ',
                'status' => 400
            ]);
        }

        $id = $user->id;
        //UserCertification::where('user_id',$id)->delete();
        //InfoUser::where('user_id', $id)->delete();
        User::where('id', $id)->delete();
        return response()->json([
            'msg' => 'Ban has been done successfully by deleting the account of this user',
            'status' => 200
        ]);

    }

    /**
     * Show all reports
     *
     * This function to show all reports with all details ( with action = 0 ) that no action taken on it yet
     *
     * @response 200 {
     * "AllReports_info": [
     * {
     * "id": 1,
     * "message_id": 1,
     * "details": "Consectetur ut ut nihil ea voluptatibus reiciendis iste tempore. Dolores aut possimus perspiciatis ut est fugiat dolore. Id quo voluptas et voluptatem ab aliquam. Consequuntur est aspernatur animi dolor repellat placeat dolores.",
     * "action": 0,
     * "created_at": "2021-08-26T15:19:47.000000Z",
     * "updated_at": "2021-08-26T00:00:00.000000Z"
     * },
     * {
     * "id": 2,
     * "message_id": 2,
     * "details": "Accusantium repellat omnis possimus. Id voluptatibus voluptas facilis iusto. Molestias nostrum a dolores perspiciatis officiis. Omnis ut quae quo itaque explicabo est.",
     * "action": 0,
     * "created_at": "1977-01-22T00:00:00.000000Z",
     * "updated_at": "2004-01-27T00:00:00.000000Z"
     * },
     * {
     * "id": 3,
     * "message_id": 3,
     * "details": "Quo consequatur ad eos sunt. Et aut odit necessitatibus tempora quos sequi omnis. Non nisi eum consequuntur fugit cum pariatur reiciendis. Velit quo in qui error.",
     * "action": 0,
     * "created_at": "2005-07-17T00:00:00.000000Z",
     * "updated_at": "2008-05-06T00:00:00.000000Z"
     * }
     * ],
     * "status": 200
     * }
     */
    public function showAllReports()
    {
        $action = 0;
        $reports=Report::where('action', $action)->get()->all();
        if(!$reports)
        {
            return response()->json([
                'msg' => 'No Current reports without taken action on it',
                'status' => 400
            ]);
        }
        foreach ($reports as $report) {
            $msg=Message::where('id',$report->message_id)->first();
            if($msg->content!=null || !empty($msg->content)){
                $output[] = [
                    'reports' => $report,
                    'msg_content'=>$msg->content,
                    'isImg'=>0
                ];
            }
            else{
                $output[] = [
                    'reports' => $report,
                    'msg_content'=>$msg->img_url,
                    'isImg'=>1
                ];
            }
        }
        return response()->json([$output],200);
    
        


    }

    /**
     * Take action on actual reports
     *
     * This function to take action on report with  input 'report_id' (int) required to do report on it
     * and input 'action_type' (int) required to know what action to do
     * action_type can be = 1 || 2 || 3 || 4
     * ( (1) for no action
     * or (2) for removing the report from the user
     * or (3) for banning user
     * or (4) for temp banning user for limited time (weekly checks to remove the ban from him))
     *
     * for banning the user (3) it checks if ban counts exceeds 20 then the account of this user will be deleted
     *
     * @queryParam report_id int required The id of the user.
     * @queryParam action_type int required The id of the user.
     *
     * @response 200 {
     * "msg": "Action has been taken successfully by banning the user"
     * }
     *
     * @response 400{
     * "msg": "choose action to be taken on this report",
     * "status": 400
     * }
     */
    public function showReportbyID(Request $request)
    {
        if( !$request->id)
        {
            return response()->json([
                'msg' => 'No inputs report_id ',
                'status' => 400
            ]);
        }
        ///$reports=Report::where('action', $action)->get()->all();
        return response()->json(Report::where('id','=',$request->id)->first());
    }
    public function takeActionOnReport(Request $request)
    {
        // by report id ->get msg id -> get user id (Sender user) -> then do action
        //get message_id from report Model then get the sender or receiver id and do the action
        if(!$request->action_type | !$request->report_id)
        {
            return response()->json([
                'msg' => 'No inputs report_id orr action_type ',
                'status' => 400
            ]);
        }
        $report_id = $request->report_id;
        try{
            $report = Report::findOrFail($report_id);
        } catch (ModelNotFoundException $e)
        {
            return response()->json([
                'msg' => 'no report found by This input id ',
                'status' => 400
            ]);
        }
        if($report->action == 1 | $report->action == 2 | $report->action == 3 | $report->action == 4 )
        {
            return response()->json([
                'msg' => 'Already action has been taken before on this report ',
                'status' => 400
            ]);
        }
        $message_id = $report->message_id;
        try{
            $message = Message::findOrFail($message_id);
        } catch (ModelNotFoundException $e)
        {
            return response()->json([
                'msg' => 'no message found by this report id ',
                'status' => 400
            ]);
        }
        try{
            $user = User::findOrFail($message->sender_id);//$user = User::where('id' , $user_id )->firstOrFail();
        } catch (ModelNotFoundException $e)
        {
            return response()->json([
                'msg' => 'no user found by This id ',
                'status' => 400
            ]);
        }
        if($request->action_type == 3) // ban user
        {
            $count = $user->ban_count;
            if($count==null) $count=0;
            $new_count =  $count + 1;
            $user->ban_count = $new_count;
            $user->ban = true;
            $user->save();
            $report->action = 3;
            $report->save();

            if($user->ban_count > 20)
            {
                $id = $user->id;
                UserCertification::where('user_id',$id)->delete();
                InfoUser::where('user_id', $id)->delete();
                User::where('id', $id)->delete();
                return response()->json([
                    'msg' => 'Action has been taken by banning the user and ban_counts exceeds more than 20, the account of this is deleted',
                    'status' => 200
                ]);
            }


            return response()->json([
                'status' => true,
                'msg' => 'Action has been taken successfully by banning the user',
            ]);
        }
        else if ( $request->action_type == 1 ) // no action
        {
            $report->action = 1;
            $report->save();
            return response()->json([
                'status' => true,
                'msg' => 'Action is taken to do nothing on this report"No Action" ',

            ]);
        }

        else if($request->action_type == 4) // temp banning for limited time
        {
            $count = $user->ban_count;
            if($count==null) $count=0;
            $new_count =  $count + 1;
            $user->ban_count = $new_count;
            $user->ban = true;
            $user->save();
            $report->action = 4;
            $report->save();
            // temp banning user
            return response()->json([
                'status' => true,
                'msg' => 'Action has been taken successfully by temporary banning the user for limited time ',
            ]);

        }

        else if($request->action_type == 2) //remove the report from the user
        {
            $report->action = 2;
            $report->save();
            $reports_count = $user->reports;
            if($reports_count===null) $reports_count=0;
            if($reports_count > 0)
            {
                $new_reports_count =  $reports_count - 1;
                $user->reports = $new_reports_count;
            }
            $user->save();
            return response()->json([
                'status' => true,
                'msg' => 'Action has been taken successfully by removing the report from the user',
            ]);
        }
        else
        {
            // choose action
            return response()->json([
                'msg' => 'choose action to be taken on this report',
                'status' => 400
            ]);
        }


    }


}

