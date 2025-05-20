<?php

use App\Models\Enrollment;
use App\Models\CourseSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('enrollment can be created with valid attributes', function () {
    $courseSession = CourseSession::factory()->create();
    $student = User::factory()->create();

    $enrollment = Enrollment::create([
        'courseSession_id' => $courseSession->id,
        'student_id' => $student->id,
        'is_enrolled' => true,
        'class_assessment_marks' => 85,
        'final_term_marks' => 90,
    ]);

    $this->assertDatabaseHas('enrollments', [
        'is_enrolled' => true,
        'class_assessment_marks' => 85,
        'final_term_marks' => 90,
        'courseSession_id' => $courseSession->id,
        'student_id' => $student->id
    ]);
});

test('enrollment belongs to course session', function () {
    $courseSession = CourseSession::factory()->create();
    $enrollment = Enrollment::factory()->create(['courseSession_id' => $courseSession->id]);

    $this->assertInstanceOf(CourseSession::class, $enrollment->courseSession);
    $this->assertEquals($courseSession->id, $enrollment->courseSession->id);
});

test('enrollment belongs to student', function () {
    $student = User::factory()->create();
    $enrollment = Enrollment::factory()->create(['student_id' => $student->id]);

    $this->assertInstanceOf(User::class, $enrollment->student);
    $this->assertEquals($student->id, $enrollment->student->id);
});

test('enrollment has correct fillable attributes', function () {
    $enrollment = new Enrollment();

    $expected = [
        'courseSession_id',
        'student_id',
        'is_enrolled',
        'class_assessment_marks',
        'final_term_marks'
    ];

    expect($enrollment->getFillable())->toBe($expected);
});


test('enrollment can be marked as not enrolled', function () {
    $enrollment = Enrollment::factory()->create(['is_enrolled' => false]);

    $this->assertFalse($enrollment->is_enrolled);
});

test('enrollment requires course session and student', function () {
    $this->expectException(\Illuminate\Database\QueryException::class);

    Enrollment::factory()->create([
        'courseSession_id' => null,
        'student_id' => null
    ]);
});
