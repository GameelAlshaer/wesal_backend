<?php

namespace App\Http\Controllers;

use App\Models\Block;
use App\Models\Chat;
use App\Models\Fav;
use App\Models\Questions;
use App\Models\Report;
use App\Models\Requests;
use App\Models\SuggestedAnswers;
use App\Models\User;
use App\Models\Admin;
use App\Models\UserCertification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use JWTAuth;

/**
 * @group Admin Controller 2
 * APIs for admin
 */
class AdminController extends Controller
{
    /**
     * Get All Questions and Answers
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
    public function getAllQuestionsandAnswers()
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
     * Create Question
     *
     * This endpoint lets you create a new question to be asked to the user.
     * @authenticated
     *
     * @bodyParam question string required The question to the user.
     * @bodyParam gender string required The user gender to which the question will be asked Male or Female.
     *
     * @response 200 {
     * "message": "Question added successfully!"
     * }
     *
     * @response 400 {
     *  "message": "Invalid data",
     * "Errors in": {
     * "question": [
     *     "The question field is required."
     * ]
     * }
     * }
     *
     */
    public function createQuestion(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'question' => 'required|string',
            'gender' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid data', 'Errors in' => $validator->messages()], 400);
        } else {
            $q = new Questions();
            $q->question = $req->input('question');
            $q->gender = $req->input('gender');
            $q->save();
            return response()->json(['message' => 'Question added successfully!'], 200);
        }
    }

    /**
     * Edit Question
     *
     * This endpoint lets you edit a question to be asked to the user.
     * @authenticated
     *
     * @bodyParam id int required The id of the question to be edited.
     * @bodyParam question string required The question to the user.
     * @bodyParam gender string required The user gender to which the question will be asked Male or Female.
     *
     * @response 200 {
     * "message": "Question edited successfully!"
     * }
     * @response 400 {
     *  "message": "Invalid data",
     * "Errors in": {
     * "question": [
     *     "The question field is required."
     * ]
     * }
     * }
     * @response 404 {
     * "message": "Question Not Found!"
     * }
     */
    public function editQuestion(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'id' => 'required|int',
            'question' => 'required|string',
            'gender' => 'required|string',
        ]);

        $q = Questions::find($req->input('id'));
        if ($q) {

            if ($validator->fails()) {
                return response()->json(['message' => 'Invalid data', 'Errors in' => $validator->messages()], 400);
            } else {
                $q->update(['question' => $req->input('question') ?? $q->question, 'gender' => $req->input('gender') ?? $q->gender]);
                return response()->json(['message' => 'Question edited successfully!'], 200);
            }
        } else {
            return response()->json(['message' => 'Question Not Found!'], 404);
        }
    }

    /**
     * Delete Question
     *
     * This endpoint lets you delete a question to be asked to the user.
     * @authenticated
     *
     * @bodyParam id int required The id of the question to be deleted.
     * @response 200 {
     * "message": "Question deleted successfully!"
     * }
     * @response 404 {
     * "message": "Question Not Found!"
     * }
     *
     * @response 400 {
     *  "message": "Invalid data",
     * "Errors in": {
     * "question": [
     *     "The id field is required."
     * ]
     * }
     * }
     */
    public function deleteQuestion(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'id' => 'required|int',
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid data', 'Errors in' => $validator->messages()], 400);
        } else {
            $q = Questions::find($req->input('id'));
            if ($q) {
                $q->delete();
                return response()->json(['message' => 'Question deleted successfully!'], 200);
            } else {
                return response()->json(['message' => 'Question Not Found!'], 404);
            }
        }
    }

    /**
     * Add Suggested Answer
     *
     * This endpoint lets you add a suggested answer to a question.
     * @authenticated
     *
     * @bodyParam question_id int required The id of the question.
     * @bodyParam answer string required The suggested answer.
     *
     * @response 200 {
     * "message": "Answer added successfully!"
     * }
     * @response 400 {
     *  "message": "Invalid data",
     * "Errors in": {
     * "question": [
     *     "The question_id field is required."
     * ]
     * }
     * }
     * @response 404 {
     * "message": "Question Not Found!"
     * }
     */
    public function addSuggestedAnswer(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'question_id' => 'required|int',
            'answer' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid data', 'Errors in' => $validator->messages()], 400);
        } else {
            $q = Questions::find($req->input('question_id'));
            if (!$q) {
                return response()->json(['message' => 'Question Not Found!'], 404);
            } else {
                $a = new SuggestedAnswers();
                $a->question_id = $req->input('question_id');
                $a->answer = $req->input('answer');
                $a->save();
                return response()->json(['message' => 'Answer added successfully!'], 200);
            }
        }
    }

    /**
     * Edit Suggested Answer
     *
     * This endpoint lets you edit an answer to a question.
     * @authenticated
     *
     * @bodyParam id int required The id of the answer to be edited.
     * @bodyParam answer string required The answer.
     *
     * @response 200 {
     * "message": "Answer edited successfully!"
     * }
     * @response 400 {
     *  "message": "Invalid data",
     * "Errors in": {
     * "question": [
     *     "The answer field is required."
     * ]
     * }
     * }
     * @response 404 {
     * "message": "Answer Not Found!"
     * }
     *
     */

    public function editSuggestedAnswer(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'answer_id' => 'required|int',
            'answer' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid data', 'Errors in' => $validator->messages()], 400);
        } else {
            $a = SuggestedAnswers::find($req->input('answer_id'));
            if (!$a) {
                return response()->json(['message' => 'Answer Not Found!'], 404);
            } else {
                $a->update(['answer' => $req->input('answer')]);
                return response()->json(['message' => 'Answer edited successfully!'], 200);
            }
        }
    }

    /**
     * Delete Suggested Answer
     *
     * This endpoint lets you delete an answer to a question.
     * @authenticated
     *
     * @bodyParam id int required The id of the answer to be deleted.
     *
     * @response 200 {
     * "message": "Answer deleted successfully!"
     * }
     * @response 404 {
     * "message": "Answer Not Found!"
     * }
     * @response 400 {
     *  "message": "Invalid data",
     * "Errors in": {
     * "question": [
     *     "The answer_id field is required."
     * ]
     * }
     * }
     */

    public function deleteSuggestedAnswer(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'answer_id' => 'required|int',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid data', 'Errors in' => $validator->messages()], 400);
        } else {
            $a = SuggestedAnswers::find($req->input('answer_id'));
            if (!$a) {
                return response()->json(['message' => 'Answer Not Found!'], 404);
            } else {
                $a->delete();
                return response()->json(['message' => 'Answer deleted successfully!'], 200);
            }
        }
    }

    /**
     * Get Users to be Certified
     *
     * This endpoint lets you get the Users who requested to be certified.
     * @authenticated
     *
     * @response 200 {
     *    "body": [
     *        {
     *            "user": [
     *                {
     *                    "id": 2,
     *                    "name": "tdoyle",
     *                    "email": "brandon.schmidt@example.org",
     *                    "email_verified_at": "2021-09-02T09:28:05.000000Z",
     *                    "phone": "1-567-717-3728",
     *                    "birth_day": "1996-06-03",
     *                    "age": 28.5,
     *                    "gender": "Male",
     *                    "answered": 1,
     *                    "image": "https://via.placeholder.com/640x480.png/00cccc?text=ut",
     *                    "reports": 156,
     *                    "ban": 0,
     *                    "ban_count": 1073385,
     *                    "certified": 1,
     *                    "VIP": 0,
     *                    "created_at": "2021-09-02T01:45:50.000000Z",
     *                    "updated_at": "2010-12-02T18:24:33.000000Z",
     *                    "id_number": "89b8cedc-43fa-3876-8e1c-6903be456628",
     *                    "online": 1
     *                }
     *            ]
     *        },
     *        {
     *            "user": [
     *                {
     *                    "id": 8,
     *                    "name": "tyler.grimes",
     *                    "email": "bcrona@example.org",
     *                    "email_verified_at": "2021-09-02T21:56:41.000000Z",
     *                    "phone": "+12796902924",
     *                    "birth_day": "1990-10-15",
     *                    "age": 83.8,
     *                    "gender": "Male",
     *                    "answered": 1,
     *                    "image": "https://via.placeholder.com/640x480.png/006688?text=magnam",
     *                    "reports": 188892190,
     *                    "ban": 0,
     *                    "ban_count": 85657,
     *                    "certified": 1,
     *                    "VIP": 1,
     *                    "created_at": "2021-09-02T23:08:25.000000Z",
     *                    "updated_at": "1977-10-21T00:37:57.000000Z",
     *                    "id_number": "628b2308-0cb0-3c55-8096-69851ec0e27b",
     *                    "online": 0
     *                }
     *            ]
     *        }
     *    ]
     *}
     *
     * @response 404 {
     * "message": "No users found!"
     * }
     */
    public function getUserCertifiedToBe()
    {

        $users = UserCertification::all();
        if (!$users) {
            return response()->json(['message' => 'No users found!'], 404);
        } else {
            $unique_users = $users->unique('user_id');
            $subset = $unique_users->map(function ($unique_users) {
                return collect($unique_users->toArray())
                    ->only(['user_id'])
                    ->all();
            });
            $output = [];
            foreach ($subset as $user) {
                $userinfo = User::where('id', $user)->get();
                $output[] = [
                    'user' => $userinfo,
                ];
            }
            return response()->json(['body' => $output], 200);
        }
    }

    /**
     * Get User Certifiable data
     *
     * This endpoint lets you get the data that the user provided the app with to be certified   Body Parameter: user_id int required The id of said user.
     * @authenticated
     *
     * @bodyParam user_id int required The id of said user.
     *
     * @response 200 {
     * "body": [
     *  {
     * "id": 9,
     * "user_id": 9,
     * "image": "https://via.placeholder.com/640x480.png/008811?text=laboriosam",
     * "created_at": "2021-09-01T23:05:37.000000Z",
     *  "updated_at": "2021-09-01T00:00:00.000000Z"
     * },
     * {
     * "id": 10,
     * "user_id": 9,
     * "image": "https://via.placeholder.com/640x480.png/008811?text=laboriosam",
     * "created_at": "2021-09-02T12:25:10.000000Z",
     * "updated_at": "-000001-11-30T00:00:00.000000Z"
     * }
     * ]
     * }
     *
     * @response 404 {
     * "message": "User Not Found!"
     * }
     * @response 400 {
     *  "message": "Invalid data",
     * "Errors in": {
     * "question": [
     *     "The user_id field is required."
     * ]
     * }
     * }
     */
    public function getUserCertifiableData(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'user_id' => 'required|int',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid data', 'Errors in' => $validator->messages()], 400);
        } else {
            $user = UserCertification::where('user_id', $req->input('user_id'))->get();
            if ($user->count() == 0) {
                return response()->json(['message' => 'User Not Found!'], 404);
            } else {
                return response()->json(['body' => $user], 200);
            }
        }
    }

    /**
     * Delete Certification request
     *
     * This endpoint lets you delete the user that is already certified from the UserCertification Table.
     * @authenticated
     *
     * @bodyParam user_id int required The id of said user.
     *
     * @response 200 {
     * "message": "Request deleted successfully!"
     * }
     * @response 400 {
     *  "message": "Invalid data",
     * "Errors in": {
     * "question": [
     *     "The user_id field is required."
     * ]
     * }
     * }
     * @response 404 {
     * "message": "User Not Found!"
     * }
     */

    public function deleteCertificationRequest(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'user_id' => 'required|int',
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid data', 'Errors in' => $validator->messages()], 400);
        } else {
            $usercert = UserCertification::where('user_id', $req->input('user_id'));
            if ($usercert->count() != 0) {
                $usercert->delete();
                return response()->json(['message' => 'Request deleted successfully!'], 200);
            } else {
                return response()->json(['message' => 'User Not Found!'], 404);
            }
        }
    }

    /**
     * Certify User
     *
     * This endpoint lets you certify a user when their certification data has been checked and validated.
     * @authenticated
     *
     * @bodyParam user_id int required The id of said user.
     *
     * @response 200 {
     * "message": "User certified successfully!"
     * }
     *
     * @response 400 {
     *  "message": "Invalid data",
     * "Errors in": {
     * "question": [
     *     "The user_id field is required."
     * ]
     * }
     * }
     * @response 409 {
     * "message": "User already certified!"
     * }
     */

    public function certifyUser(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'user_id' => 'required|int',
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid data', 'Errors in' => $validator->messages()], 400);
        } else {
            $user = User::find($req->input('user_id'));
            if (!$user) {
                return response()->json(['message' => 'User Not Found!'], 404);
            } else {
                if ($user->certified == 1) {
                    return response()->json(['message' => 'User already certified!'], 409);
                } else {
                    $user->update(['certified' => 1]);
                    return response()->json(['message' => 'User certified successfully!'], 200);
                }
            }
        }
    }


    /**
     * Get Number of Online Users
     *
     * This endpoint lets you get the number of online users.
     * @authenticated
     *
     * @response 200 {
     * "body": 5
     * }
     *
     */
    public function getNumOfOnlineUsers()
    {
        $num = 0;
        foreach (User::all() as $user) {
            if ($user->online == 1) {
                $num++;
            }
        }
        return response()->json(['body' => $num], 200);
        // return response()->json(['body' => $num, 'AccessToken:' => $token], 200);
    }

    /**
     * Get Number of Chats
     *
     * This endpoint lets you get total number of chats between users.
     * @authenticated
     *
     * @response 200 {
     * "body": 5
     * }
     *
     */
    public function getNumOfChats()
    {
        return response()->json(['body' => Chat::count()], 200);
        // return response()->json(['body' => Chat::count(), 'AccessToken:' => $token], 200);
    }

    /**
     * Get Number of Requests
     *
     * This endpoint lets you get number of chat requests of (free) users.
     * @authenticated
     *
     * @response 200 {
     * "body": 5
     * }
     *
     */
    public function getNumOfRequests()
    {
        return response()->json(['body' => Requests::count()], 200);
        // return response()->json(['body' => Requests::count(), 'AccessToken:' => $token], 200);
    }

    /**
     * Get Number of Reports
     *
     * This endpoint lets you get number of reports made by users.
     * @authenticated
     *
     * @response 200 {
     * "body": 5
     * }
     *
     */
    public function getNumOfReports()
    {
        return response()->json(['body' => Report::count()], 200);
        // return response()->json(['body' => Report::count(), 'AccessToken:' => $token], 200);
    }

    /**
     * Get Number of Banned Users
     *
     * This endpoint lets you get number of users that are temporarily banned.
     * @authenticated
     *
     * @response 200 {
     * "body": 5
     * }
     *
     */
    public function getNumOfBannedUsers()
    {
        ///temporarily banned users
        $num = 0;
        foreach (Report::all() as $report) {
            if ($report->action == 4) {
                $num++;
            }
        }
        return response()->json(['body' => $num], 200);
        // return response()->json(['body' => $num, 'AccessToken:' => $token], 200);
    }

    /**
     * Get Number of Blocks
     *
     * This endpoint lets you get number of blocks.
     * @authenticated
     *
     * @response 200 {
     * "body": 5
     * }
     *
     */
    public function getNumOfBlocks()
    {
        return response()->json(['body' => Block::count()], 200);
        // return response()->json(['body' => Block::count(), 'AccessToken:' => $token], 200);
    }


    /**
     * Get Number of Favs
     *
     * This endpoint lets you get number of favourites.
     * @authenticated
     *
     * @response 200 {
     * "body": 5
     * }
     *
     */
    public function getNumOfFavs()
    {
        return response()->json(['body' => Fav::count()], 200);
        // return response()->json(['body' => Fav::count(), 'AccessToken:' => $token], 200);
    }
    /**
     * Admin Login
     * @bodyParam username string required The name of the admin. Example: aya
     * @bodyParam password string required The password of the admin. Example: ayasameh123
     *
     * @response status=200 scenario=success {
     *  "message": "logged in successfully",
     *  "AccessToken:": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC8xMjcuMC4wLjE6ODAwMFwvYXBpXC9sb2dpblwvQWRtaW4iLCJpYXQiOjE2Mjk3OTU4MDYsImV4cCI6MTYyOTc5OTQwNiwibmJmIjoxNjI5Nzk1ODA2LCJqdGkiOiJGeGNIRmFXa2FpNnQ0eldVIiwic3ViIjoyMywicHJ2IjoiZGY4ODNkYjk3YmQwNWVmOGZmODUwODJkNjg2YzQ1ZTgzMmU1OTNhOSJ9.qUBMQCRrE3n-t-EDhu0HJpxOJUJqpxvXl7UIXObzpt0"
     * }
     * @response status=404 scenario="failed" {
     *  "message": "No such admin user, invalid username or password"
     * }
     * @response status=400 scenario="failed" {
     *  "message": "Invalid data"
     *  "Errors in":{"username":["The username field is required."]}
     * }
     */
    public function AdminLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string',
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid data', 'Errors in' => $validator->messages()], 400);
        } else {
            /*$Admin = Admin::create([
                'username' => $request->username,
                'password' => Hash::make($request->password),
                'super_admin'=>1
            ]);*/
            $credentials = $request->only('username', 'password');
            $token = Auth::guard('api_admin')->attempt($credentials);
            if ($token) {
                return response()->json(['message' => 'logged in successfully', 'AccessToken' => $token], 200);
            } else {
                return response()->json(['message' => 'No such admin user, invalid username or password'], 404);
            }
        }
    }
    /**
     * Admin registeration
     *
     * @authenticated
     *
     * @bodyParam username string required The name of the admin. Example: aya
     * @bodyParam password string required The password of the admin. Example: ayasameh123
     * @bodyParam super_admin int required Specify if the new admin is a super admin(1:super admin,0:normal admin). Example: 0
     *
     * @response status=201 scenario=success {
     *  "message": "New admin is created successfully, you can now login",
     * }
     * @response status=401 scenario="unauthorized" {
     *  "message": "you are not a super admin"
     * }
     * @response status=400 scenario="failed" {
     *  "message": "Invalid data"
     *  "Errors in":{"username":["The username field is required."]}
     * }
     * @response status=400 scenario="failed" {
     *  "message": "New admin creation failed, please check the data passed"
     * }
     */
    public function AdminStore(Request $request)
    {
        $SuperAdmin = Auth::guard('api_admin')->user();
        if ($SuperAdmin->super_admin != 1) {
            return response()->json(['message' => 'you are not a super admin'], 401);
        } else {
            $validator = Validator::make($request->all(), [
                'username' => 'required|string|unique:admins',
                'password' => 'required|string',
                'super_admin' => ['required', 'in:0,1'],
            ]);
            if ($validator->fails()) {
                return response()->json(['message' => 'Invalid data', 'Errors in' => $validator->messages()], 400);
            } else {
                $Admin = Admin::create([
                    'username' => $request->username,
                    'password' => Hash::make($request->password),
                    'super_admin' => $request->super_admin
                ]);
                if ($Admin) {
                    return response()->json(['message' => 'New admin is created successfully, you can now login'], 201);
                } else {
                    return response()->json(['message' => 'New admin creation failed, please check the data passed'], 400);
                }
            }
        }
    }
}
