<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class BookFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'title' => fake()->sentence(3),
            'author' => fake()->name(),
            'created_at' => fake()->dateTimeBetween('-2 years'),
            'updated at' => fake()->dateTimeBetween('created at', 'now'),
        ];
    }
}
