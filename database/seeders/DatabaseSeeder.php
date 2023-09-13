<?php

namespace Database\Seeders;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Questions;
use App\Models\Admin;
use App\Models\InfoUser;
use App\Models\InfoUserQuestion;
use App\Models\SuggestedAnswers;
use App\Models\Requests;
use App\Models\Block;
use App\Models\Fav;
use App\Models\Chat;
use App\Models\Message;
use App\Models\MessageImage;
use App\Models\Report;
use App\Models\UserCertification;








class DatabaseSeeder extends Seeder
{
    //   /**
    //  * The current Faker instance.
    //  *
    //  * @var \Faker\Generator
    //  */
    // protected $faker;
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {

        User::factory(10)->create();
        Questions::factory(10)->create();
        Admin::factory(10)->create();
        SuggestedAnswers::factory(10)->create();
        InfoUser::factory(10)->create();
        InfoUserQuestion::factory(10)->create();
        Requests::factory(10)->create();
        Block::factory(10)->create();
        Fav::factory(10)->create();
        Chat::factory(10)->create();
        Message::factory(10)->create();
        MessageImage::factory(10)->create();
        Report::factory(10)->create();
        UserCertification::factory(10)->create();






    }
}
