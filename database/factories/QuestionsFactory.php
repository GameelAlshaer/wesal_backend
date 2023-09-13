<?php

namespace Database\Factories;

use App\Models\Questions;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class QuestionsFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Questions::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $strings = array(
            'Male',
            'Female',
        );
        return [
            //  'id'=>$this->faker->randomNumber(),
            'question' => $this->faker->realText(),
            'gender' => $strings[array_rand($strings, 1)],
            'updated_at' => $this->faker->date(),
            'created_at' => $this->faker->date()
        ];
    }
}
