<?php

namespace Tests\Feature\Result;

use App\Helpers\GradeHelper;
use App\Models\Course;
use App\Models\CourseSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;

uses(RefreshDatabase::class);

beforeEach(function () {
    // 1) Create a student in academic year 1, semester 1
    $this->student = User::factory()->create([
        'year'     => 1,
        'semester' => 1,
    ]);

    // 2) Create two courses in year=1, semester=1
    $this->courseA = Course::factory()->create([
        'year'     => 1,
        'semester' => 1,
        'credit'   => 3,
    ]);
    $this->courseB = Course::factory()->create([
        'year'     => 1,
        'semester' => 1,
        'credit'   => 4,
    ]);

    // 3) One session per course
    $this->sessionA = CourseSession::factory()->create([
        'course_id' => $this->courseA->id,
    ]);
    $this->sessionB = CourseSession::factory()->create([
        'course_id' => $this->courseB->id,
    ]);

    // 4) Two enrollment attempts for courseA (best = 25+45)
    DB::table('enrollments')->insert([
        [
            'student_id'             => $this->student->id,
            'courseSession_id'       => $this->sessionA->id,
            'class_assessment_marks' => 20,
            'final_term_marks'       => 40,
            'is_enrolled'            => 1,
            'created_at'             => now(),
            'updated_at'             => now(),
        ],
        [
            'student_id'             => $this->student->id,
            'courseSession_id'       => $this->sessionA->id,
            'class_assessment_marks' => 25,
            'final_term_marks'       => 45,
            'is_enrolled'            => 1,
            'created_at'             => now(),
            'updated_at'             => now(),
        ],
    ]);

    // 5) One enrollment attempt for courseB (30+50)
    DB::table('enrollments')->insert([
        [
            'student_id'             => $this->student->id,
            'courseSession_id'       => $this->sessionB->id,
            'class_assessment_marks' => 30,
            'final_term_marks'       => 50,
            'is_enrolled'            => 1,
            'created_at'             => now(),
            'updated_at'             => now(),
        ],
    ]);
});

it('returns highest marks and grade for a single course', function () {
    $expected = GradeHelper::getGrade(25 + 45);

    actingAs($this->student, 'sanctum')
        ->getJson("/api/result/show/{$this->courseA->id}")
        ->assertOk()
        ->assertJson([
            'course_id'            => $this->courseA->id,
            'max_final_term_marks' => 70,
            'grade'                => $expected['grade'],
            'gpa'                  => $expected['gpa'],
            'remark'               => $expected['remark'],
            'user_id'              => $this->student->id,
            'user_name'            => $this->student->name,
        ]);
});

it('returns 500 if no enrollment exists for that course', function () {
    $newCourse = Course::factory()->create([
        'year'     => 1,
        'semester' => 1,
        'credit'   => 2,
    ]);

    actingAs($this->student, 'sanctum')
        ->getJson("/api/result/show/{$newCourse->id}")
        ->assertStatus(500)
        ->assertJson(['message' => 'An error occurred while fetching results']);
});

it('returns full semester results with correct CGPA', function () {
    $gradeA = GradeHelper::getGrade(70); // best for A
    $gradeB = GradeHelper::getGrade(80); // B

    $weightedA = $gradeA['gpa'] * $this->courseA->credit;
    $weightedB = $gradeB['gpa'] * $this->courseB->credit;
    $expectedCgpa = round(
        ($weightedA + $weightedB) /
        ($this->courseA->credit + $this->courseB->credit),
        2
    );

    actingAs($this->student, 'sanctum')
        ->getJson('/api/result/show-full-result/1/1') // year=1, semester=1
        ->assertOk()
        ->assertJsonPath('total_cgpa', $expectedCgpa)
        ->assertJsonCount(2, 'courses')
        ->assertJsonFragment([
            'course_id'   => $this->courseA->id,
            'total_marks' => 70,
            'grade'       => $gradeA['grade'],
            'gpa'         => $gradeA['gpa'],
        ])
        ->assertJsonFragment([
            'course_id'   => $this->courseB->id,
            'total_marks' => 80,
            'grade'       => $gradeB['grade'],
            'gpa'         => $gradeB['gpa'],
        ]);
});

it('treats a non-enrolled course as F with zero GPA', function () {
    $courseC = Course::factory()->create([
        'year'     => 1,
        'semester' => 1,
        'credit'   => 2,
    ]);

    actingAs($this->student, 'sanctum')
        ->getJson('/api/result/show-full-result/1/1')
        ->assertOk()
        ->assertJsonFragment([
            'course_id'    => $courseC->id,
            'total_marks'  => null,
            'grade'        => 'F',
            'gpa'          => 0,
            'remark'       => 'Not Enrolled',
            'credit_hours'=> 2,
        ]);
});
