<?php

use App\Models\CourseSession;
use App\Models\Course;
use App\Models\Department;
use App\Models\User;
use App\Models\Enrollment;
use App\Models\CourseResource;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// Attribute Tests
test('course session has correct fillable attributes', function () {
    $session = new CourseSession();
    $expected = ['course_id', 'teacher_id', 'session'];

    expect($session->getFillable())->toBe($expected);
});

test('course session can be created with valid attributes', function () {
    $session = CourseSession::factory()->create([
        'session' => '2024'
    ]);

    $this->assertDatabaseHas('course_sessions', [
        'session' => '2024'
    ]);
});

// Relationship Tests
test('course session belongs to course', function () {
    Department::factory()->create();
    $course = Course::factory()->create();
    $session = CourseSession::factory()->create(['course_id' => $course->id]);

    $this->assertInstanceOf(Course::class, $session->course);
    $this->assertEquals($course->id, $session->course->id);
});

test('course session belongs to teacher', function () {
    $teacher = User::factory()->create();
    $session = CourseSession::factory()->create(['teacher_id' => $teacher->id]);

    $this->assertInstanceOf(User::class, $session->teacher);
    $this->assertEquals($teacher->id, $session->teacher->id);
});

test('course session has enrollments', function () {
    $session = CourseSession::factory()->create();
    Enrollment::factory()->create(['courseSession_id' => $session->id]);

    $this->assertInstanceOf(Enrollment::class, $session->enrollments->first());
    $this->assertEquals(1, $session->enrollments->count());
});

test('course session has resources', function () {
    $session = CourseSession::factory()->create();

    // Manually create resource
    $resource = CourseResource::create([
        'course_session_id' => $session->id,
        'uploaded_by' => User::factory()->create()->id,
        'title' => 'Test Resource',
        'description' => 'Test Description',
        'file_name' => 'test.pdf',
        'file_path' => 'course_materials/test.pdf',
        'file_type' => 'application/pdf',
        'file_size' => 1024,
    ]);

    $this->assertInstanceOf(CourseResource::class, $session->courseResources->first());
    $this->assertEquals(1, $session->courseResources->count());
});

// Factory Tests
test('factory generates valid session format', function () {
    $session = CourseSession::factory()->create();

    expect($session->session)
        ->toBeString()
        ->toMatch('/^\d{4}$/'); // YYYY format
});

test('factory creates valid relationships', function () {
    $session = CourseSession::factory()->create();

    $this->assertNotNull($session->course_id);
    $this->assertNotNull($session->teacher_id);
    $this->assertInstanceOf(Course::class, $session->course);
    $this->assertInstanceOf(User::class, $session->teacher);
});

// Edge Cases
test('session with no enrollments returns empty collection', function () {
    $session = CourseSession::factory()->create();

    expect($session->enrollments)->toBeEmpty();
});

test('session with no resources returns empty collection', function () {
    $session = CourseSession::factory()->create();

    expect($session->courseResources)->toBeEmpty();
});

// Validation Test
test('session requires course and teacher', function () {
    $this->expectException(\Illuminate\Database\QueryException::class);

    CourseSession::factory()->create([
        'course_id' => null,
        'teacher_id' => null
    ]);
});
