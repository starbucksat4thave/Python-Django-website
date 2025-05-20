<?php

use App\Models\Department;
use App\Models\User;
use App\Models\Notice;
use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// Attribute Tests
test('department has correct fillable attributes', function () {
    $department = new Department();
    $expected = ['name', 'code', 'faculty', 'short_name'];

    expect($department->getFillable())->toBe($expected);
});

test('department can be created with valid attributes', function () {
    $department = Department::factory()->create([
        'name' => 'Computer Science',
        'short_name' => 'CS',
        'faculty' => 'Science Faculty'
    ]);

    $this->assertDatabaseHas('departments', [
        'name' => 'Computer Science',
        'short_name' => 'CS',
        'faculty' => 'Science Faculty'
    ]);
});

test('department factory generates unique names', function () {
    Department::factory()->count(5)->create();

    $names = Department::pluck('name')->toArray();
    $uniqueNames = array_unique($names);

    expect($names)->toHaveCount(count($uniqueNames));
});

// Relationship Tests
test('department has users relationship', function () {
    $department = Department::factory()->create();
    $user = User::factory()->create(['department_id' => $department->id]);

    $this->assertInstanceOf(User::class, $department->users->first());
    $this->assertEquals($user->id, $department->users->first()->id);
});

test('department has notices relationship', function () {
    $department = Department::factory()->create();
    $user = User::factory()->create();

    $notice = Notice::factory()->create([
        'department_id' => $department->id,
        'published_by' => $user->id
    ]);

    $this->assertInstanceOf(Notice::class, $department->notices->first());
    $this->assertEquals($notice->id, $department->notices->first()->id);
});

test('department has courses relationship', function () {
    $department = Department::factory()->create();
    $course = Course::factory()->create(['department_id' => $department->id]);

    $this->assertInstanceOf(Course::class, $department->courses->first());
    $this->assertEquals($course->id, $department->courses->first()->id);
});

// Edge Case Tests
test('department can have multiple users', function () {
    $department = Department::factory()->create();
    User::factory()->count(3)->create(['department_id' => $department->id]);

    expect($department->users)->toHaveCount(3);
});

test('department with no courses returns empty collection', function () {
    $department = Department::factory()->create();

    expect($department->courses)->toBeEmpty();
});

// Validation Test
test('department requires name and short_name', function () {
    $this->expectException(\Illuminate\Database\QueryException::class);

    Department::factory()->create([
        'name' => null,
        'short_name' => null
    ]);
});
