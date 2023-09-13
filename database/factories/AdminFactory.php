<?php

namespace Database\Factories;


use App\Models\Admin;


use Illuminate\Database\Eloquent\Factories\Factory;


class AdminFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Admin::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            // 'id' =>$this->faker->randomNumber(),
            'username'=>$this->faker->userName() ,
            'password'=>$this->faker->password(),
            'super_admin'=>$this->faker->boolean(),
            'updated_at'=>$this->faker->date(),
            'created_at'=>$this->faker->date()

        ];
    }



}
