<?php

use App\Models\User;
use App\Models\Course;
use App\Models\Department;
use App\Models\Enrollment;
use App\Models\CourseSession;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use function Pest\Laravel\postJson;
use function Pest\Laravel\getJson;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

const ENROLLMENT_ENDPOINT = '/api/courses/active/enroll';
const MARKS_ENDPOINT = '/api/courses/active/enrollments/updateMarks';

beforeEach(function () {
    // Create roles
    Role::create(['name' => 'student']);
    Role::create(['name' => 'teacher']);

    // Create department
    $this->department = Department::factory()->create([
        'short_name' => 'CS',
        'name' => 'Computer Science'
    ]);

    // Create course with specific academic requirements
    $this->course = Course::factory()->create([
        'department_id' => $this->department->id,
        'year' => 3,
        'semester' => 1,
        'code' => 'CS101',
        'name' => 'Programming Fundamentals'
    ]);

    // Create teacher
    $this->teacher = User::factory()->create([
        'department_id' => $this->department->id
    ])->assignRole('teacher');

    // Create properly qualified student
    $this->student = User::factory()->create([
        'department_id' => $this->department->id,
        'year' => $this->course->year,
        'semester' => $this->course->semester,
        'session' => '2015'
    ])->assignRole('student');

    Sanctum::actingAs($this->teacher);
    Sanctum::actingAs($this->student);

    // Create current session
    $this->courseSession = CourseSession::factory()->create([
        'teacher_id' => $this->teacher->id,
        'course_id' => $this->course->id,
        'session' => '2015'
    ]);

});


it('prevents non-students from enrolling', function () {
    $regularUser = User::factory()->create();

    $response = $this->actingAs($regularUser)
        ->postJson(ENROLLMENT_ENDPOINT, [
            'course_id' => $this->course->id
        ]);

    $response->assertStatus(403)
        ->assertJson([
            'status' => 'error',
            'message' => 'Only students can enroll in courses.'
        ]);
});

it('prevents duplicate enrollments in same session', function () {
    Enrollment::factory()->create([
        'student_id' => $this->student->id,
        'courseSession_id' => $this->courseSession->id
    ]);

    $response = $this->actingAs($this->student)
        ->postJson(ENROLLMENT_ENDPOINT, [
            'course_id' => $this->course->id
        ]);

    $response->assertStatus(403)
        ->assertJson([
            'status' => 'error',
            'message' => 'You are not eligible to retake this course.'
        ]);
});

it('allows authorized teacher to update marks', function () {
    $enrollment = Enrollment::factory()->create([
        'courseSession_id' => $this->courseSession->id,
        'student_id' => $this->student->id
    ]);

    $response = $this->actingAs($this->teacher)
        ->postJson(MARKS_ENDPOINT, [
            'courseSession_id' => $this->courseSession->id,
            'enrollments' => [
                [
                    'id' => $enrollment->id,
                    'class_assessment_marks' => 25,
                    'final_term_marks' => 65
                ]
            ]
        ]);

    $response->assertStatus(200)
        ->assertJson(['message' => 'Enrollments updated successfully.']);
});

it('prevents unauthorized mark updates', function () {
    $otherTeacher = User::factory()->create()->assignRole('teacher');
    $otherSession = CourseSession::factory()->create(['teacher_id' => $otherTeacher->id]);
    $enrollment = Enrollment::factory()->create(['courseSession_id' => $otherSession->id]);

    $response = $this->actingAs($this->teacher)
        ->postJson(MARKS_ENDPOINT, [
            'courseSession_id' => $this->courseSession->id,
            'enrollments' => [
                [
                    'id' => $enrollment->id,
                    'class_assessment_marks' => 25,
                    'final_term_marks' => 65
                ]
            ]
        ]);

    $response->assertStatus(403);
});

it('validates mark input ranges', function () {
    $enrollment = Enrollment::factory()->create([
        'courseSession_id' => $this->courseSession->id
    ]);

    $response = $this->actingAs($this->teacher)
        ->postJson(MARKS_ENDPOINT, [
            'courseSession_id' => $this->courseSession->id,
            'enrollments' => [
                [
                    'id' => $enrollment->id,
                    'class_assessment_marks' => 35,
                    'final_term_marks' => 80
                ]
            ]
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors([
            'enrollments.0.class_assessment_marks',
            'enrollments.0.final_term_marks'
        ]);
});

it('prevents enrollment for mismatched academic year', function () {
    $wrongStudent = User::factory()->create([
        'department_id' => $this->department->id,
        'year' => $this->course->year + 1,
        'semester' => $this->course->semester
    ])->assignRole('student');

    $response = $this->actingAs($wrongStudent)
        ->postJson(ENROLLMENT_ENDPOINT, [
            'course_id' => $this->course->id
        ]);

    $response->assertStatus(403);
});

it('prevents enrollment for mismatched semester', function () {
    $wrongStudent = User::factory()->create([
        'department_id' => $this->department->id,
        'year' => $this->course->year,
        'semester' => $this->course->semester % 2 + 1
    ])->assignRole('student');

    $response = $this->actingAs($wrongStudent)
        ->postJson(ENROLLMENT_ENDPOINT, [
            'course_id' => $this->course->id
        ]);

    $response->assertStatus(403);
});

it('allows eligible student to enroll successfully', function () {
    Sanctum::actingAs($this->student);

    // Create a course session for the student to enroll in
    $pastCourseSession = CourseSession::factory()->create([
        'course_id' => $this->course->id,
        'teacher_id' => $this->teacher->id,
        'session' => '2014'
    ]);
    Enrollment::factory()->create([
        'courseSession_id' => $pastCourseSession->id,
        'student_id' => $this->student->id,
        'class_assessment_marks' => 10,
        'final_term_marks' => 10
    ]);
//    dd($this->course->courseSessions->last()->enrollments->first()->student_id);

//    dd($this->courseSession->id);

    $response = $this->postJson(ENROLLMENT_ENDPOINT, [
        'course_id' => $this->course->id,
    ]);


    $response->assertStatus(201)
        ->assertJson([
            'status' => 'success',
            'message' => 'Enrollment created successfully.',
        ]);

    $this->assertDatabaseHas('enrollments', [
        'student_id' => $this->student->id,
        'courseSession_id' => $this->courseSession->id,
    ]);
});

it('returns validation error for non-existent course_id', function () {
    $response = $this->actingAs($this->student)
        ->postJson(ENROLLMENT_ENDPOINT, [
            'course_id' => 9999, // Assuming this ID does not exist
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['course_id']);
});

it('prevents unauthorized user from updating enrollment', function () {
    $enrollment = Enrollment::factory()->create([
        'courseSession_id' => $this->courseSession->id,
        'student_id' => $this->student->id,
    ]);

    $unauthorizedUser = User::factory()->create()->assignRole('student');

    $response = $this->actingAs($unauthorizedUser)
        ->postJson("/api/courses/active/enrollments/{$enrollment->id}", [
            'class_assessment_marks' => 20,
            'final_term_marks' => 50,
        ]);

    $response->assertStatus(403);
});

it('allows teacher to view enrollments for their course session', function () {
    Enrollment::factory()->create([
        'courseSession_id' => $this->courseSession->id,
        'student_id' => $this->student->id,
    ]);

    $response = $this->actingAs($this->teacher)
        ->getJson("/api/courses/active/enrollments/{$this->courseSession->id}");

    $response->assertStatus(200)
        ->assertJson([
            'status' => 'success',
        ])
        ->assertJsonStructure([
            'status',
            'data' => [
                '*' => ['id', 'student_id', 'courseSession_id', /* other fields */],
            ],
        ]);
});

it('allows student to view their enrollments', function () {
    Enrollment::factory()->create([
        'courseSession_id' => $this->courseSession->id,
        'student_id' => $this->student->id,
    ]);

    $response = $this->actingAs($this->student)
        ->getJson('/api/courses/active/enrollments');

    $response->assertStatus(200)
        ->assertJson([
            'status' => 'success',
        ])
        ->assertJsonStructure([
            'status',
            'data' => [
                '*' => ['id', 'courseSession_id', 'canReEnroll', /* other fields */],
            ],
        ]);
});

it('returns validation errors when required fields are missing', function () {
    $response = $this->actingAs($this->student)
        ->postJson(ENROLLMENT_ENDPOINT, []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['course_id']);
});

