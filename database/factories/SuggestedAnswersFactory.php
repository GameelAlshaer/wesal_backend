<?php

namespace Database\Factories;

use App\Models\SuggestedAnswers;
use Illuminate\Database\Eloquent\Factories\Factory;


class SuggestedAnswersFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SuggestedAnswers::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        static $number1 = 1;


        return [
        //  'id'=>$this->faker->randomNumber(),
         'question_id'=>$number1++,
         'answer'=>$this->faker->realText(),
         'updated_at'=>$this->faker->date(),
         'created_at'=>$this->faker->date()
        ];
    }



}
