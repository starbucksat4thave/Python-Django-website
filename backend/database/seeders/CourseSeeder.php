<?php

namespace Database\Seeders;

use App\Models\Course;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Course::factory(8)->create([
            'year' => 1,
            'semester' => 1,
        ]);

        Course::factory(8)->create([
            'year' => 1,
            'semester' => 2,
        ]);

        Course::factory(8)->create([
            'year' => 2,
            'semester' => 1,
        ]);

        Course::factory(8)->create([
            'year' => 2,
            'semester' => 2,
        ]);

        Course::factory(8)->create([
            'year' => 3,
            'semester' => 1,
        ]);

        Course::factory(8)->create([
            'year' => 3,
            'semester' => 2,
        ]);

        Course::factory(8)->create([
            'year' => 4,
            'semester' => 1,
        ]);

        Course::factory(8)->create([
            'year' => 4,
            'semester' => 2,
        ]);

        Course::factory()->create([
            'code' => 'CSE1101',
            'name' => 'Computer Fundamentals',
            'description' => 'Computer Science is the study of computers and computing concepts. It includes both hardware and software, as well as networking and the Internet.',
            'credit' => 3,
            'year' => 1,
            'semester' => 1,
        ]);
    }
}
