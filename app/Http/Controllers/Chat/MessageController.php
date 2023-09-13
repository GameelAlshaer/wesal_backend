<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User ;
use Twilio\Exceptions\RestException;
use Twilio\Rest\Client;

class MessageController extends Controller
{
    public function __construct()
    {

    }

    public function index(Request $request)
    {
        $users = User::where('id', '<>', $request->user()->id)->get();

        return response()->json(['users' => $users]) ;
    }

    public function chat(Request $request , $id)
    {
        $authUser = $request->user();
        $otherUser = User::find($id);

        $id1 = $authUser->id ;
        $id2 = $id ;

        $channelName = min($id1,$id2).'-'.max($id1,$id2);

        $twilio = new Client(env('TWILIO_AUTH_SID'), env('TWILIO_AUTH_TOKEN'));

        // Fetch channel or create a new one if it doesn't exist
        try {
            $channel = $twilio->chat->v2->services(env('TWILIO_SERVICE_SID'))
                ->channels($channelName)
                ->fetch();
        } catch (RestException $e) {
            $channel = $twilio->chat->v2->services(env('TWILIO_SERVICE_SID'))
                ->channels
                ->create([
                    'uniqueName' => $channelName,
                    'type' => 'private',
                ]);
        }

        // Add first user to the channel
        try {
            $twilio->chat->v2->services(env('TWILIO_SERVICE_SID'))
                ->channels($channelName)
                ->members($authUser->email)
                ->fetch();

        } catch (RestException $e) {
            $member = $twilio->chat->v2->services(env('TWILIO_SERVICE_SID'))
                ->channels($channelName)
                ->members
                ->create($authUser->email);
        }

        // Add second user to the channel
        try {
            $twilio->chat->v2->services(env('TWILIO_SERVICE_SID'))
                ->channels($channelName)
                ->members($otherUser->email)
                ->fetch();

        } catch (RestException $e) {
            $twilio->chat->v2->services(env('TWILIO_SERVICE_SID'))
                ->channels($channelName)
                ->members
                ->create($otherUser->email);
        }
        return response()->json([
            'authUser' => $authUser,
            'otherUser' => $otherUser
        ]);
    }
}
