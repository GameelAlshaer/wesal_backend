<?php

namespace Database\Factories;

use App\Models\Block;
use Illuminate\Database\Eloquent\Factories\Factory;


class BlockFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Block::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {

        static $number1 = 1;

        static $number2 = 10;
        return [
            //  'id'=>$this->faker->randomNumber(),
            'blocker_id' => $number1++,
            'blocked_id' => $number2--,
            'name' => $this->faker->name(),
            'blocked_image' => $this->faker->imageUrl(),
            'age' => $this->faker->randomNumber(1,50),
            'updated_at' => $this->faker->date(),
            'created_at' => $this->faker->date()
        ];
    }


}
