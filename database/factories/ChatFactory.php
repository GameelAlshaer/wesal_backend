<?php

namespace Database\Factories;

use App\Models\Chat;
use Illuminate\Database\Eloquent\Factories\Factory;


class ChatFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Chat::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        static $number1 = 1;

        static $number2 =  10;
        return [
        //  'id'=>$this->faker->randomNumber(),
         'user_1'=>$number1++,
         'user_2'=>$number2--,
         'updated_at'=>$this->faker->date(),
         'created_at'=>$this->faker->date()
        ];
    }



}
