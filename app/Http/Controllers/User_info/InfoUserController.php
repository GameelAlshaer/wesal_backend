<?php

namespace App\Http\Controllers\User_info;

use App\Http\Controllers\Controller;
use App\Models\InfoUserQuestion;
use http\Env\Response;
use Illuminate\Http\Request;
use App\Models\InfoUser;
use App\Models\SuggestedAnswers;
use App\Models\User;
use App\Models\Questions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

/**
 * @group User info ,questions and suggested answers
 *
 *
 * APIs for User info ,questions and suggested  features
 *
 */
class InfoUserController extends Controller
{
    /**
     * Preference
     * @authenticated
     *
     *
     *
     * @response
     * [
     * {
     * "id": 2,
     * "name": "stone88",
     * "email": "schowalter.jannie@example.net",
     * "email_verified_at": "2021-09-01T17:05:53.000000Z",
     * "phone": "814-367-6631",
     * "birth_day": "1979-06-02",
     * "age": 539,
     * "gender": "Female",
     * "image": "https://via.placeholder.com/640x480.png/007744?text=rerum",
     * "reports": 62294,
     * "answered": null,
     * "ban": 0,
     * "ban_count": 94,
     * "certified": 0,
     * "VIP": 0,
     * "created_at": "2021-09-01T06:55:48.000000Z",
     * "updated_at": "1992-12-08T17:01:40.000000Z",
     * "mac_address": "grIi2lS4eQ",
     * "id_number": "d49cf2e9-8577-3ca6-963b-4611ccfe7790",
     * "online": 0
     * }
     * ]
     *
     */
    public function preference()
    {
//        $users = User::where('id','!=',Auth::user()->id)->where('gender','!=',Auth::user()->gender)->get();
        $users = User::where('id', '!=', Auth::user()->id)
            ->where('gender', '!=', Auth::user()->gender)
            ->orderby('reports', 'ASC')
            ->orderby('ban', 'ASC')
            ->orderby('VIP', 'DESC')
            ->orderby('age', 'ASC')
            ->orderby('certified', 'DESC')
            ->get();
        $answers = InfoUser::where('user_id', Auth::user()->id)->pluck('answer')->toArray();
        $useranswerscount = InfoUser::where('user_id', Auth::user()->id)->get()->count();
        $output = [];
        $id = 0;
        $sortedids = [];
        foreach ($users as $user) {
            $ii = 0;
            $currentuseranswers = InfoUser::where('user_id', $user->id)->pluck('answer')->toArray();
            $count = InfoUser::where('user_id', $user->id)->get()->count();
            for ($i = 0; $i < $count; $i++) {
                for ($j = 0; $j < $useranswerscount; $j++) {
                    if ($currentuseranswers[$i] === $answers[$j]) {
                        $ii++;
                    }
                }
            }
            $percentage = 1 - ($ii / $useranswerscount);
            $userid = $user->id;
            $userids[] = [
                'p' => $percentage,
                'userid' => $userid
            ];
        }
        $collection = collect($userids);
        $sortedids = $collection->sortBy('p');
        foreach ($sortedids as $sortedid) {
            $output[] = [
                'user' => User::where('id', $sortedid['userid'])->get(),
            ];
        }
        return response()->json($output);
    }

    /*public function hide(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'question_id'=>'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Invalid data',
                'Errors in'=>$validator->messages()], 400);
        }
        else
        {
            $a=InfoUser::where('question_id',$request->question_id)
                ->where('user_id',Auth::user()->id)
                ->get()
                ->first();
            if(!$a)
            {
                return response()->json([
                    'message'=>'this question id doesnt exist'
                ]);
            }
            else{
                $output=[];

                $infousers=InfoUser::where('user_id',Auth::user()->id)
                    ->where('question_id','!=',$request->question_id)
                    ->get();


                foreach ($infousers as $infouser)
                {

                    $question=Questions::where('id',$infouser->question_id)
                        ->select('question')
                        ->get();


                    $answerid=InfoUser::where('user_id',Auth::user()->id)
                        ->where('question_id',$infouser->question_id)
                        ->pluck('answer_id')
                        ->toArray();

                    $answer=SuggestedAnswers::where('id',$answerid[0])
                        ->select('answer')
                        ->get();

                    $output[]=[
                        $question,
                        $answer
                    ];
                }
                return response()->json($output);

            }
            }

    }*/
    /**
     * Show
     * @authenticated
     *
     * @bodyParam user_id int the id of the user.
     *
     *
     * @response
     *
     * [
     * {
     * "question": "I shan't grow any more--As it is, I suppose?' said Alice. 'I don't see any wine,' she remarked. 'There isn't any,' said the March Hare moved into the garden. Then she went on. 'Would you tell me,'."
     * },
     * [
     * "answer":"No, I dont"
     * ],
     * [
     * {
     * "question": "Dinah stop in the distance, screaming with passion. She had already heard her voice sounded hoarse and strange, and the reason they're called lessons,' the Gryphon replied very politely, 'if I had."
     * },
     * [
     * {
     * "answer": "yess"
     * }
     * ]
     * ]
     *
     *
     *
     * @response {
     * "message": "this user id doesnt exist"
     * }
     */
    public function show(Request $request)
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
                    $hide = InfoUser::where('question_id', $infouser->question_id)
                        ->where('user_id', $request->user_id)
                        ->select('hidden')
                        ->get();
                    $output[] = [[$question], $hide, $answer];
                }
                return response()->json($output);

            }
        }
    }

    /**
     * Hide
     * @authenticated
     * @bodyParam question_id int the id of the answer.
     *
     *
     *
     * @response {
     * "message": "Your question is hidden successfully"
     * }
     *
     * @response {
     * "message": "this question id doesn't exist"
     * }
     */
    public function hide(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'question_id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Invalid data',
                'Errors in' => $validator->messages()], 400);
        } else {
            $questionid = InfoUser::where('question_id', $request->question_id)->get()->first();
            if ($questionid) {
                InfoUser::where('question_id', $request->question_id)->update([
                    'hidden' => 1,
                ]);
                return response()->json([
                    "message" => "Your question is hidden successfully"
                ]);
            } else {
                return response()->json(['message' => 'Question id doesnt exist']);
            }
        }
    }

    /**
     * Unhide
     * @authenticated
     * @bodyParam question_id int the id of the answer.
     *
     *
     *
     * @response {
     * "message": "Your question is unhidden successfully"
     * }
     *
     * @response {
     * "message": "this question id doesnt exist"
     * }
     */
    public function unhide(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'question_id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Invalid data',
                'Errors in' => $validator->messages()], 400);
        } else {
            $questionid = InfoUser::where('question_id', $request->question_id)->get()->first();;
            if ($questionid) {
                InfoUser::where('question_id', $request->question_id)->update([
                    'hidden' => 0,
                ]);
                return response()->json([
                    "message" => "Your question is unhidden successfully"
                ]);
            } else {
                return response()->json(['message' => 'Question id doesnt exist']);
            }
        }
    }
}
