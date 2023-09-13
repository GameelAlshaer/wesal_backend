<?php

namespace Database\Factories;

use App\Models\Report;
use Illuminate\Database\Eloquent\Factories\Factory;


class ReportFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Report::class;

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
            'message_id' => $number1++,
            'details' => $this->faker->realText(),
            'sender_img' => $this->faker->imageUrl(),
            'action' => $this->faker->numberBetween(0, 3),
            'updated_at' => $this->faker->date(),
            'created_at' => $this->faker->date()
        ];
    }


}
