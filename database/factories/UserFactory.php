<?php

namespace Database\Factories;

use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

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
            'name' => $this->faker->userName(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => $this->faker->randomNumber(), // password
            'phone' => $this->faker->phoneNumber(),
            'birth_day' => $this->faker->date(),
            'gender' => $strings[array_rand($strings, 1)],
            'image' => $this->faker->imageUrl(),
            'reports' => $this->faker->randomNumber(),
            'age' => $this->faker->randomFloat(1, 18, 90),
            'ban' => $this->faker->boolean(),
            'ban_count' => $this->faker->randomNumber(),
            'certified' => $this->faker->boolean(),
            'VIP' => $this->faker->boolean(),
            'updated_at' => $this->faker->dateTime(),
            'created_at' => $this->faker->time(),
            'email_verified_at' => $this->faker->time(),
            'remember_token' => Str::random(10),
            'id_number' => $this->faker->uuid(),
            'online' => $this->faker->boolean(),
            'answered' => $this->faker->boolean()
        ];
    }
    public function unverified()
    {
        return $this->state(function (array $attributes) {
            return [
                'email_verified_at' => null,
            ];
        });
    }
}
