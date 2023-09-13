<?php

namespace App\Console\Commands;

use App\Models\Report;
use App\Models\User;
use http\Message;
use Illuminate\Console\Command;

class unbanning_users extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'unbanning_users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'unbanning user after the limited time because of the report';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        foreach (Report::all() as $report)
        {
            if($report->action == 4)
            {
                $message_id = $report->message_id;
                $message = \App\Models\Message::findOrFail($message_id);
                $user_id = $message->sender_id;
                $user = User::findOrFail($user_id);
               if($user->ban == 1)
               {
                   $user->update(['ban' => 0]);
                   if($user->ban_count > 0)
                   {
                       $new_count = $user->ban_count -1 ;
                       $user->ban_count = $new_count;
                       $user->save();
                   }
               }
            }
        }
        return 0;
    }
}
