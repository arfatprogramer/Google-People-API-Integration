<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\client>
 */
class clientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'FirstName' => $this->faker->firstName(),
            'lastName' => $this->faker->lastName(),
            'number' => $this->faker->phoneNumber(),
            'familyOrOrgnization' => $this->faker->company(),
            'email' => $this->faker->unique()->safeEmail(),
            'panCard' => strtoupper($this->faker->bothify('?????####?')),
            'aadharCard' => $this->faker->numerify('############'),
            'ocupation' => $this->faker->randomElement(['Salaried', 'Business', 'Student', 'Retired']),
            'kycStatus' => $this->faker->randomElement(['Completed', 'Pending', 'Rejected']),
            'anulIncome' => $this->faker->randomFloat(2, 100000, 1000000),
            'reffredBy' => $this->faker->name(),
            'totalInvestment' => $this->faker->randomFloat(2, 10000, 500000),
            'comment' => $this->faker->sentence(),
            'relationshipManager' => $this->faker->name(),
            'serviceRM' => $this->faker->name(),
            'totalSIP' => $this->faker->randomFloat(2, 500, 50000),
            'primeryContactPerson' => $this->faker->name(),
            'meetinSchedule' => $this->faker->randomElement(['Monthly', 'Quarterly', 'Yearly']),
            'firstMeetingDate' => $this->faker->date(),
            'typeOfRelation' => $this->faker->randomElement(['Individual', 'Joint', 'Corporate']),
            'maritalStatus' => $this->faker->randomElement(['Single', 'Married', 'Divorced']),
        ];
    }
}
