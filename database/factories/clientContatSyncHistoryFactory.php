<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\clientContatSyncHistory>
 */
class clientContatSyncHistoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [

                'created'=>$this->faker->randomNumber(5, false),
                'updated'=>$this->faker->randomNumber(5, false),
                'deleted'=>$this->faker->randomNumber(5, false),
                'error'=>$this->faker->randomNumber(5, false),

        ];
    }
}
