<?php

namespace App\Events;

use App\Models\User;
use App\Models\Message;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */

    public $user, $message,$replyMsg, $chatId;
    public function __construct(User $user, Message $message, $replyMsg,$chatId)
    {
        $this->user = $user;
        $this->message = $message;
        $this->chatId = $chatId;
        $this->replyMsg = $replyMsg;
        $this->dontBroadcastToCurrentUser();
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        //console.log("event channel");
        return new PrivateChannel('chat.'.$this->chatId);
    }


    public function broadcastAs(){
        return "MessageSent";
    }
}
