<?php

namespace Database\Factories;

use App\Models\InfoUserQuestion;
use Illuminate\Support\Facades\Hash;

use Illuminate\Database\Eloquent\Factories\Factory;


class InfoUserQuestionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = InfoUserQuestion::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        static $number1 = 1;

        static $number2 =  1;
        return [
            'question_id'=>$number2++,
            'user_id'=>$number1++,
            'updated_at'=>$this->faker->date(),
            'created_at'=>$this->faker->date()
        ];
    }



}
