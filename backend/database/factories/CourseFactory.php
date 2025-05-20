<?php

namespace Database\Factories;

use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Course>
 */
class CourseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Generate a random department
        $departments = Department::all();
        $department = $departments->first();

        // Generate the year (1-4) and semester (1-2)
        $year = $this->faker->numberBetween(1, 4);  // Year: 1-4
        $semester = $this->faker->numberBetween(1, 2); // Semester: 1 or 2
        $randomDigits = $this->faker->unique()->numberBetween(0, 99); // Random number between 00 and 99

        // Format the course code like 'CSE1202' (where CSE is the department, 1 is the year, 2 is the semester, and 02 is the random number)
        $code = strtoupper($department->short_name) . $year . $semester . str_pad($randomDigits, 2, '0', STR_PAD_LEFT);
        return [
            'name' => $this->faker->name(),
            'code' => $code,
            'credit' => $this->faker->randomFloat(1, 1, 4),
            'semester' => $semester,
            'year' => $year,
            'department_id' => $department->id,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
