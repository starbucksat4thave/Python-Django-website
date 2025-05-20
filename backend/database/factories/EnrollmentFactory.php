<?php

namespace Database\Factories;

use App\Models\CourseSession;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Enrollment>
 */
class EnrollmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'student_id' => User::factory(),
            'courseSession_id' => CourseSession::factory(),
            'is_enrolled' => true,
            'class_assessment_marks' => 0,
            'final_term_marks' => 0
        ];
    }
}
