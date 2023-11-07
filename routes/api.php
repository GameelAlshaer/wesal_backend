<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\Auth\FacebookController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\Chat\MessageController;
use App\Http\Controllers\Chat\TokenController;
use App\Http\Controllers\Requests_Blocks_Fav_Reports\BlockController;
use App\Http\Controllers\Requests_Blocks_Fav_Reports\FavController;
use App\Http\Controllers\Requests_Blocks_Fav_Reports\ReportController;
use App\Http\Controllers\Requests_Blocks_Fav_Reports\RequestsController;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminController1;
use App\Http\Controllers\User_info\QuestionsController;
use App\Http\Controllers\User_info\InfoUserController;
use App\Http\Controllers\User_info\SuggestedAnswerController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['middleware' => 'guest' ], function() {

    Route::post('/register', [RegisteredUserController::class, 'store']);
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
    Route::get('/auth/google', [GoogleController::class, 'redirectToGoogle']);
    Route::get('/auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);
    Route::get('/auth/facebook', [FacebookController::class, 'redirectToFacebook']);
    Route::get('/auth/facebook/callback', [FacebookController::class, 'handleFacebookCallback']);
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
    Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])->name('password.update');
    Route::post('/login/Admin', [AdminController::class, 'AdminLogin']);
    Route::get('test/{id}',[\App\Http\Controllers\PaypalController::class,'test']);
    Route::get('paypal/return',[\App\Http\Controllers\PaypalController::class,'paypalReturn'])->name('paypal.return');
    Route::get('paypal/cancel',[\App\Http\Controllers\PaypalController::class,'paypalCancel'])->name('paypal.cancel');
});


Route::group(['middleware' => 'auth' ], function(){
    Route::post('/confirm-password', [ConfirmablePasswordController::class, 'store']);
    //change route name
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('Logout');
});

Route::group(['middleware' => ['auth','verified'] ], function(){
    //User info
    Route::get('get-question-by-id',[QuestionsController::class,'show']);
    Route::get('get-question-answers',[QuestionsController::class,'showanswer']);
    Route::get('get-all-questions-with-answers',[QuestionsController::class,'allquestions']);
    Route::get('get-all-questions-with-gender',[QuestionsController::class,'getAllQuestions']);
    Route::post('save-answer',[SuggestedAnswerController::class,'save']);
    Route::get('show-user',[InfoUserController::class,'show']);

});
Route::group(['middleware' => ['auth','verified','QuestionsAreAnswered','VIP'] ], function(){
    //User info
    Route::get('hide',[InfoUserController::class,'hide']);
    Route::get('unhide',[InfoUserController::class,'unhide']);

});
//other routes for authenticated users, their emails are verified, all the questions are answered
//NOTE: VIP middleware is now available for the VIP routes!!!
Route::group(['middleware' => ['auth','verified','QuestionsAreAnswered'] ], function(){
    Route::get('preference',[InfoUserController::class,'preference']);
    //Route::post('/search',[UserController::class,'searchGender'])->name('search');
    Route::delete('/delete',[UserController::class,'deleteAccount'])->name('delete');
    Route::post('/filter',[UserController::class,'filter'])->name('filter');
    Route::post('/certified',[UserController::class,'certifiedUser'])->name('certified');
    Route::post('/EditInfo',[UserController::class,'EditInfo'])->name('EditInfo');
    Route::post('/deleteImage',[UserController::class,'deleteImage'])->name('deleteImage');
    Route::get('profile',function (){return Auth::user();});
    Route::post('getUser',[RequestsController::class,'getUser']);
    Route::post('request',[RequestsController::class,'requests']);
    Route::post('decision',[RequestsController::class,'decisionForRequest']);
    Route::get('getAllRequests',[RequestsController::class,'getAllRequests']);
    Route::get('RequestsSent',[RequestsController::class,'RequestsSent']);
    Route::get('RequestsRecieved',[RequestsController::class,'RequestsRecieved']);
    Route::delete('deleteRequest',[RequestsController::class,'deleteRequest']);
    Route::post('blockFriend',[BlockController::class,'Blockfriend']);
    Route::get('getAllBlocks',[BlockController::class,'getAllBlocks']);
    Route::delete('removeBlock',[BlockController::class,'removeBlock']);
    Route::post('addFriend',[FavController::class,'addFriend']);
    Route::get('getAllFriends',[FavController::class,'getAllFriends']);
    Route::delete('removeFromFav',[FavController::class,'removeFromFav']);
    Route::post('report',[ReportController::class,'report']);
    Route::group(['middleware' => ['VIP'] ], function(){
        Route::get('showAllLiked',[FavController::class,'showAllWhoSendLike']);
    });
    // Chat controller
    Route::post('startchat',[ChatController::class,'startChat']);
    Route::post('sendmsg',[ChatController::class,'sendMsg']);
    Route::group(['middleware' => ['VIP'] ], function(){
        Route::post('sendpic',[ChatController::class,'sendPic']);
        Route::delete('deletemsg/{id}',[ChatController::class,'deleteMsg']);
    });
    //Route::get('numofreports',[ChatController::class,'numOfReports']);
    Route::get('numofmsgs',[ChatController::class,'numOfMsgs']);
    Route::get('statusofmsgs/{id}',[ChatController::class,'statusOfMsgs']);
    Route::post('readmsg',[ChatController::class,'readMsg']);
    Route::get('listallchats',[ChatController::class,'ListAllChats']);
    Route::get('fetchmsgs/{id}',[ChatController::class,'fetchMsgs']);
    Route::get('numofmsgsofcertainchat/{id}',[ChatController::class,'numOfMsgsOfCertainChat']);
    Route::get('test',function (){
        return 'ok' ;
    });
});


Route::get('/verify-email/{id}/{hash}', [VerifyEmailController::class, '__invoke'])
    ->middleware(['auth', 'signed', 'throttle:6,1'])
    ->name('verification.address');


   /* Route::get('/verifyemail/{id}/{hash}', [VerifyEmailController::class, '__invoke'])
    ->middleware(['auth', 'signed', 'throttle:6,1'])
    ->name('verification.address');*/


Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
    ->middleware(['auth', 'throttle:6,1'])
    ->name('verification.send');

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

////////////////////////////Admin Controller 2/////////////////////////
Route::group(['middleware' => 'admin' ], function(){
    Route::get('/getAllQuestionsandAnswers',[AdminController::class,'getAllQuestionsandAnswers']);
    Route::get('/getUserCertifiedToBe',[AdminController::class,'getUserCertifiedToBe']);
    Route::post('/createQuestion',[AdminController::class,'createQuestion']);
    Route::patch('/editQuestion',[AdminController::class,'editQuestion']);
    Route::delete('/deleteQuestion',[AdminController::class,'deleteQuestion']);
    Route::post('/addSuggestedAnswer',[AdminController::class,'addSuggestedAnswer']);
    Route::patch('/editSuggestedAnswer',[AdminController::class,'editSuggestedAnswer']);
    Route::delete('/deleteSuggestedAnswer',[AdminController::class,'deleteSuggestedAnswer']);
    Route::get('/getUserCertifiedToBe',[AdminController::class,'getUserCertifiedToBe']);
    Route::get('/getUserCertifiableData',[AdminController::class,'getUserCertifiableData']);
    Route::delete('/deleteCertificationRequest',[AdminController::class,'deleteCertificationRequest']);
    Route::patch('/certifyUser',[AdminController::class,'certifyUser']);

    Route::get('/getNumOfOnlineUsers',[AdminController::class,'getNumOfOnlineUsers']);
    Route::get('/getNumOfChats',[AdminController::class,'getNumOfChats']);
    Route::get('/getNumOfRequests',[AdminController::class,'getNumOfRequests']);
    Route::get('/getNumOfReports',[AdminController::class,'getNumOfReports']);
    Route::get('/getNumOfBannedUsers',[AdminController::class,'getNumOfBannedUsers']);
    Route::get('/getNumOfBlocks',[AdminController::class,'getNumOfBlocks']);
    Route::get('/getNumOfFavs',[AdminController::class,'getNumOfFavs']);

    Route::get('getUserbyID',[AdminController1::class,'getUserbyID']);
    Route::get('getUserQues',[AdminController1::class,'getUserQues']);
    /////////////Registeration - logging out of admins/////////
    Route::post('/register/Admin', [AdminController::class, 'AdminStore']);
    Route::post('/logout/Admin', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    /////////////////Admin controller 1///////////////////////
    Route::group(['prefix' => 'admin'], function () {
        Route::get('getAllUsersInfo', [AdminController1::class, 'showAllUsersInfo']);
        Route::get('getAllUsersByMethod',[AdminController1::class,'showUsersByMethod']);
        Route::post('banningFakeUser', [AdminController1::class, 'banningFakeUsers']);
        Route::get('getAllReports', [AdminController1::class, 'showAllReports']);
        Route::get('showReportbyID', [AdminController1::class, 'showReportbyID']);
        Route::put('takeActionOnReport', [AdminController1::class, 'takeActionOnReport']);
        Route::get('/getAllNotCertifiedUsers',[UserController::class,'getAllUserNotCertified'])->name('ceritfyUser');
        Route::post('/adminCertify',[UserController::class,'adminCertify'])->name('adminCertify');
    });
});

// _________________________________________________________________________________________________________________
// Twilio Chat APIs
    Route::group(['middleware' => ['auth','verified','QuestionsAreAnswered'] ], function(){
        Route::get('/messages/{id}' , [MessageController::class,'chat']);
        Route::post('/test' , function (){
            return response()->json(['param' => \request()]) ;
        });
        Route::post('/token', [TokenController::class,'generate']);
    });

