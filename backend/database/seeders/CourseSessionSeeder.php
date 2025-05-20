<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\CourseSession;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CourseSessionSeeder extends Seeder
{

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $courses = Course::all();
        $teachers = User::role('teacher')->get();

        $courses->each(function ($course) use ($teachers) {
            $teacher = $teachers->random(); // Pick one random teacher for this course

            CourseSession::factory()->create([
                'course_id' => $course->id,
                'teacher_id' => $teacher->id,
                'session' => fake()->year(),
            ]);
        });

    }
}
