<?php

namespace Database\Factories;

use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $departmentID = Department::query()->first()?->id ?? Department::factory()->create()->id;
//        $departmentCode = Department::query()->find($departmentID)->code;
        //year from 1950 to 2050
        $session = $this->faker->numberBetween(1950, 2050);
        //university id is 6-digit number, last 2 digit session year, 2 digit department id, 2 digit random number
        $universityId = sprintf('%02d%02d%02d', $session % 100, $departmentID % 100, $this->faker->numberBetween(0, 99));

        return [
            'name' => $this->faker->name(),
            'image' => $this->faker->imageUrl(),
            'university_id' => $universityId,
            'session' => $session,
            'dob' => $this->faker->date(),
            'phone' => $this->faker->numerify('01########'), // BD phone format
            'address' => $this->faker->address,
            'year' => $this->faker->numberBetween(1, 4),
            'semester' => $this->faker->numberBetween(1, 2),
            'designation' => $this->faker->randomElement(['student', 'teacher', 'staff']),
            'status' => $this->faker->randomElement(['active', 'inactive']),
            'city' => $this->faker->randomElement(['Dhaka', 'Chittagong', 'Rajshahi', 'Khulna', 'Sylhet']),
            'department_id' => $departmentID,
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'remember_token' => Str::random(10),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
