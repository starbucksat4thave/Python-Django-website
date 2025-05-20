<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\CourseSession;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Random\RandomException;

class EnrollmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * @throws RandomException
     */
    public function run(): void
    {
        //enroll students to courses
        $courses = CourseSession::all();
        $students = User::role('student')->get()->random(10);

        $courses->each(function ($course) use ($students) {
            $students->each(function ($student) use ($course) {
                Enrollment::factory()->create([
                    'courseSession_id' => $course->id,
                    'student_id' => $student->id,
                    'is_enrolled' => true,
                    'class_assessment_marks' => random_int(0, 30),
                    'final_term_marks' => random_int(0, 70),
                ]);
            });
        });
    }
}
