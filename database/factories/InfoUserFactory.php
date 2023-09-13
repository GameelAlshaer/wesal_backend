<?php

namespace Database\Factories;

use App\Models\InfoUser;

use Illuminate\Database\Eloquent\Factories\Factory;


class InfoUserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = InfoUser::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        // $x = 0;
        static $number = 1;
        static $number2 =  1;
        static $number3 =  1;
        return [
        //  'id'=>$this->faker->randomNumber(),
         'user_id'=>$number++,
         'question_id'=>$number2++,
          'hidden'=>$this->faker->boolean(),
         'answer_id'=>$number3++,
         'answer'=>$this->faker->realText(),
         'updated_at'=>$this->faker->date(),
         'created_at'=>$this->faker->date()

        ];
    }



}
