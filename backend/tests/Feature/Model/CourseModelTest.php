<?php

use App\Models\Course;
use App\Models\Department;
use App\Models\CourseSession;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    Department::factory()->create(); // Ensure at least one department exists
});

// Attribute Tests
test('course has correct fillable attributes', function () {
    $course = new Course();
    $expected = [
        'code', 'name', 'description',
        'credit', 'year', 'semester', 'department_id'
    ];

    expect($course->getFillable())->toBe($expected);
});

test('course can be created with valid attributes', function () {
    $course = Course::factory()->create([
        'name' => 'Introduction to Programming',
        'credit' => 3.0
    ]);

    $this->assertDatabaseHas('courses', [
        'name' => 'Introduction to Programming',
        'credit' => 3.0
    ]);
});

// Relationship Tests
test('course belongs to department', function () {
    $department = Department::factory()->create();
    $course = Course::factory()->create(['department_id' => $department->id]);

    $this->assertInstanceOf(Department::class, $course->department);
    $this->assertEquals($department->id, $course->department->id);
});

test('course has sessions relationship', function () {
    $course = Course::factory()->create();
    $session = CourseSession::factory()->create(['course_id' => $course->id]);

    $this->assertInstanceOf(CourseSession::class, $course->courseSessions->first());
    $this->assertEquals($session->id, $course->courseSessions->first()->id);
});


test('credit value is valid', function () {
    $course = Course::factory()->create();

    expect($course->credit)
        ->toBeFloat()
        ->toBeGreaterThanOrEqual(1.0)
        ->toBeLessThanOrEqual(4.0);
});

test('year and semester are within valid ranges', function () {
    $courses = Course::factory()->count(10)->create();

    $courses->each(function ($course) {
        expect($course->year)
            ->toBeInt()
            ->toBeBetween(1, 4)
            ->and($course->semester)
            ->toBeInt()
            ->toBeBetween(1, 2);

    });
});

test('factory generates unique course codes', function () {
    Course::factory()->count(10)->create();

    $codes = Course::pluck('code')->toArray();
    $uniqueCodes = array_unique($codes);

    expect($codes)->toHaveCount(count($uniqueCodes));
});

// Edge Cases
test('course with no sessions returns empty collection', function () {
    $course = Course::factory()->create();

    expect($course->courseSessions)->toBeEmpty();
});

test('course requires department association', function () {
    $this->expectException(\Illuminate\Database\QueryException::class);

    Course::factory()->create(['department_id' => null]);
});
