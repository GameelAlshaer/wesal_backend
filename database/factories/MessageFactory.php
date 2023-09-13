<?php

namespace Database\Factories;

use App\Models\Message;
use Illuminate\Database\Eloquent\Factories\Factory;


class MessageFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Message::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        static $number1 = 1;

        static $number2 =  10;

        static $number3 =  1;

        return [
         'chat_id'=>$number3++,
         'sender_id'=>$number1++,
         'reciever_id'=>$number2--,
         'content'=>$this->faker->realText(),
         'status'=>$this->faker->randomNumber(),
         'updated_at'=>$this->faker->date(),
         'created_at'=>$this->faker->date()
        ];
    }



}
