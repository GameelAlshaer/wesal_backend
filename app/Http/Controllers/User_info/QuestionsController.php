<?php

namespace App\Http\Controllers\User_info;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\User_info\SuggestedAnswerController;
use App\Models\SuggestedAnswers;
use App\Models\Questions;
use DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

/**
 * @group User info ,questions and suggested answers
 *
 *
 * APIs for User info ,questions and suggested  features
 *
 */

class QuestionsController extends Controller
{
    /**
     * Get question by id
     *
     * @bodyParam id int required.
     * @authenticated
     * @response{
    "id": 1,
    "question": "Dinah, if I can go back and see how he can EVEN finish, if he would deny it too: but the Hatter with a lobster as a drawing of a feather flock together.\"' 'Only mustard isn't a bird,' Alice.",
    "gender": "Female",
    "created_at": "2007-02-15 00:00:00",
    "updated_at": "1999-10-15"
    }
     * @response 400{
    "message": "Invalid data",
    "0": "id field is required"
    }
     *
     *
     * @response{
    "message": "id doesnt exist"
    }
     *
     */
    public function show(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Invalid data',
                'Errors in' => $validator->messages()
            ], 400);
        } else {
            $question = DB::table('questions')->find($request->id);
            if ($question) {
                return response()->json($question);
            } else {
                return response()->json(['message' => 'id doesnt exist']);
            }
        }
    }
    public function showanswer(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Invalid data',
                'Errors in'=>$validator->messages()], 400);
        }
        else
        {
            $answers = DB::table('suggested_answers')->where('question_id',$request->id)->get();
            if($answers)
            {
                $output=[];
                foreach($answers as $answer){
                    $output[] = [
                        'id' => $answer->id,
                        'answer' => $answer->answer,
                    ];
                }
                return response()->json($output);
            }
            else
            {
                return response()->json(['message'=>'id doesnt exist']);
            }
        }
    }
    /**
     *Get all questions with it's answers
     *
     * This endpoint lets you get all questions.
     * @authenticated
     * 
     * @response 200 [
     *     [
     *         {
     *             "question": {
     *                 "id": 1,
     *                 "question": "Twinkle, twinkle--\"' Here the Queen left off, quite out of a procession,' thought she, 'if people had all to lie down on her face brightened up again.) 'Please your Majesty,' said the Hatter, with.",
     *                 "gender": "Female",
     *                 "created_at": "2000-07-03T00:00:00.000000Z",
     *                 "updated_at": "2008-11-22T00:00:00.000000Z"
     *             },
     *             "answers": [
     *                 {
     *                     "id": 1,
     *                     "question_id": 1,
     *                     "answer": "Alice whispered to the shore, and then quietly marched off after the birds! Why, she'll eat a little queer, won't you?' 'Not a bit,' said the Cat, and vanished again. Alice waited till the eyes.",
     *                     "created_at": "1981-02-21T00:00:00.000000Z",
     *                     "updated_at": "1972-05-08T00:00:00.000000Z"
     *                 }
     *             ]
     *         }
     *     ]
     * ]
     * 
     */
    public function allquestions()
    {
        $output = [];
        $questions = Questions::all();
        foreach ($questions as $question) {
            $answers = SuggestedAnswers::where('question_id', $question->id)->get();
            $output[] = [
                'question' => $question,
                'answers' => $answers,
            ];
        }
        return response()->json([$output], 200);
    }

/**
     *Get all questions with it's answers according to user gender
     *
     * This endpoint lets you get all questions.
     * @authenticated
     * 
     * @response 200 [
     *     [
     *         {
     *             "question": {
     *                 "id": 1,
     *                 "question": "Twinkle, twinkle--\"' Here the Queen left off, quite out of a procession,' thought she, 'if people had all to lie down on her face brightened up again.) 'Please your Majesty,' said the Hatter, with.",
     *                 "gender": "Female",
     *                 "created_at": "2000-07-03T00:00:00.000000Z",
     *                 "updated_at": "2008-11-22T00:00:00.000000Z"
     *             },
     *             "answers": [
     *                 {
     *                     "id": 1,
     *                     "question_id": 1,
     *                     "answer": "Alice whispered to the shore, and then quietly marched off after the birds! Why, she'll eat a little queer, won't you?' 'Not a bit,' said the Cat, and vanished again. Alice waited till the eyes.",
     *                     "created_at": "1981-02-21T00:00:00.000000Z",
     *                     "updated_at": "1972-05-08T00:00:00.000000Z"
     *                 }
     *             ]
     *         }
     *     ]
     * ]
     * 
     */
    public function getAllQuestions()
    {
        $user = Auth::user();
        $output = [];
        $questions = Questions::where('gender', $user->gender)->get();
        foreach ($questions as $question) {
            $answers = SuggestedAnswers::where('question_id', $question->id)->get();
            $output[] = [
                'question' => $question,
                'answers' => $answers,
            ];
        }
        return response()->json([$output], 200);
    }
}