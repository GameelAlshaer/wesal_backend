<?php

namespace App\Http\Controllers\User_info;

use App\Http\Controllers\Controller;
use App\Models\InfoUser;
use App\Models\Questions;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\SuggestedAnswers;
use App\Models\InfoUserQuestion;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\User_info\QuestionsController;

/**
 * @group User info ,questions and suggested answers
 *
 *
 * APIs for User info ,questions and suggested  features
 *
 */

class SuggestedAnswerController extends Controller
{
    /**
     * save
     * @authenticated
     * @bodyParam question_id int the id of the question.
     *@bodyParam answer string required the answer.
     *
     *
     * @response{
     *    "message":"Answer is added successfully"
     * }
     * @response 400{
    "message": "Invalid data",
    "0": "fill the required fields"
    }
     * @response
     * {
    "message": "Question id doesnt exist"
    }
     *
     * @response
     * {
    "message": "Answer is edited successfully",
    "0": 1
    }
     *
     */
    public function save(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'question_id' => 'required',
            'answer' => 'required|string',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Invalid data',
                'Errors in' => $validator->messages()
            ], 400);
        } else {
            $questionidexists = Questions::where('id', $request->question_id)->get()->first();
            if (!$questionidexists) {
                return response()->json([
                    "message" => "Question id doesnt exist"
                ]);
            } else {
                //to check if the user answered this question before or not
                $questionid = InfoUserQuestion::where('user_id', Auth::user()->id)->where('question_id', $request->question_id)->select('question_id')->get()->first();
                if (!$questionid) {
                    // SuggestedAnswers::create([
                    //     'question_id' => $request->question_id,
                    //     'answer' => $request->answer
                    // ]);

                    $answerid = SuggestedAnswers::where('question_id', $request->question_id)
                        ->where('answer', $request->answer)
                        ->pluck('id')
                        ->toArray();
                    InfoUser::create([
                        'question_id' => $request->question_id,
                        'user_id' => Auth::user()->id,
                        'answer_id' => $answerid[0],
                        'answer' => $request->answer,
                    ]);
                    InfoUserQuestion::create([
                        'user_id' => Auth::user()->id,
                        'question_id' => $request->question_id,
                    ]);
                    //to check if all questions are answered
                    $userquestions = InfoUserQuestion::where('user_id', Auth::user()->id)->select('question_id')->count();
                    $questions = Questions::where('gender', Auth::user()->gender)->select('id')->get()->count();
                    if ($userquestions == $questions) {
                        User::where('id', Auth::user()->id)->update([
                            'answered' => 1,
                        ]);
                    }
                    return response()->json([
                        'message' => 'Answer is saved successfully',
                        'Answered' => (User::find(Auth::user()->id))->answered,
                        // 'Answered questions' => $userquestions,
                        // 'Gender questions' => $questions
                    ]);
                } else {
                    $previous_answer = InfoUser::where('user_id', Auth::user()->id)->where('question_id', $request->question_id)->first();
                    $answer_id = SuggestedAnswers::where('answer', $request->answer)->where('question_id', $request->question_id)->pluck('id')->first();
                    $previous_answer->update(['answer' => $request->answer, 'answer_id' => $answer_id]);
                    //to check if all questions are answered
                    $userquestions = InfoUserQuestion::where('user_id', Auth::user()->id)->select('question_id')->count();
                    $questions = Questions::where('gender', Auth::user()->gender)->select('id')->get()->count();
                    if ($userquestions == $questions) {
                        User::where('id', Auth::user()->id)->update([
                            'answered' => 1,
                        ]);
                    }
                    return response()->json([
                        'message' => 'Answer is edited successfully',
                        'Answered' => (User::find(Auth::user()->id))->answered
                    ]);
                }
            }
        }
    }
}
