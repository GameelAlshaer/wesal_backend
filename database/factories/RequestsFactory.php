<?php

namespace Database\Factories;

use App\Models\Requests;
use Illuminate\Database\Eloquent\Factories\Factory;


class RequestsFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Requests::class;

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
         'sender_id'=>$number1++,
         'reciever_id'=>$number2--,
         'status'=>$this->faker->randomNumber(),
         'updated_at'=>$this->faker->date(),
         'created_at'=>$this->faker->date()
        ];
    }



}
