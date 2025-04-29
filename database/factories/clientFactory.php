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
            'firstName' => $this->faker->firstName,
            'lastName' => $this->faker->lastName,
            'number' => $this->faker->phoneNumber,
            'email' => $this->faker->email,
            'familyOrOrgnization' => $this->faker->company,
            'panCardNumber' => strtoupper($this->faker->bothify('?????####?')),
            'aadharCardNumber' => $this->faker->numerify('############'),
            'occupation' => $this->faker->randomElement(['Engineer', 'Doctor', 'Business', 'Select']),
            'kycStatus' => $this->faker->randomElement(['Pending', 'Verified', 'Rejected', 'Select']),
            'anulIncome' => $this->faker->randomFloat(2, 100000, 1000000),
            'referredBy' => $this->faker->name,
            'totalInvestment' => $this->faker->randomFloat(2, 10000, 1000000),
            'comments' => $this->faker->sentence,
            'relationshipManager' => $this->faker->name,
            'serviceRM' => $this->faker->name,
            'totalSIP' => $this->faker->randomFloat(2, 1000, 50000),
            'primeryContactPerson' => $this->faker->name,
            'meetinSchedule' => $this->faker->randomElement(['Weekly', 'Monthly', 'Select']),
            'firstMeetingDate' => $this->faker->date,
            'typeOfRelation' => $this->faker->randomElement(['Client', 'Referral', 'Select']),
            'maritalStatus' => $this->faker->randomElement(['Single', 'Married', 'Divorced', 'Select']),
        ];
    }
}
