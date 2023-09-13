<?php

namespace Database\Factories;


use App\Models\UserCertification;


use Illuminate\Database\Eloquent\Factories\Factory;


class UserCertificationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = UserCertification::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        static $number = 1;
        return [
        //  'id'=>$this->faker->randomNumber(),
         'user_id'=>$number++,
         'image' => $this->faker->imageUrl(),

        ];
    }



}
