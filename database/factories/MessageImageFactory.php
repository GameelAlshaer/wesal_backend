<?php

namespace Database\Factories;

use App\Models\MessageImage;
use Illuminate\Database\Eloquent\Factories\Factory;


class MessageImageFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = MessageImage::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        static $number1 =  1;
        return [

         'message_id'=>$number1++,
         'image'=>$this->faker->imageUrl(),
         'updated_at'=>$this->faker->date(),
         'created_at'=>$this->faker->date()
        ];
    }



}
