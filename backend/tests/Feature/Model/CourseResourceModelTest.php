<?php

use App\Models\CourseResource;
use App\Models\CourseSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('course resource can be created with valid attributes', function () {
    $session = CourseSession::factory()->create();
    $teacher = User::factory()->create();

    $resource = CourseResource::create([
        'course_session_id' => $session->id,
        'uploaded_by'       => $teacher->id,
        'title'             => 'Week 1 Lecture',
        'description'       => 'Overview of basics',
        'file_name'         => 'week1.pdf',
        'file_path'         => 'course_materials/week1.pdf',
        'file_type'         => 'application/pdf',
        'file_size'         => 204800,
    ]);

    $this->assertDatabaseHas('course_resources', [
        'course_session_id' => $session->id,
        'uploaded_by'       => $teacher->id,
        'title'             => 'Week 1 Lecture',
        'description'       => 'Overview of basics',
        'file_name'         => 'week1.pdf',
        'file_path'         => 'course_materials/week1.pdf',
        'file_type'         => 'application/pdf',
        'file_size'         => 204800,
    ]);
});

test('course resource belongs to course session', function () {
    $session = CourseSession::factory()->create();

    $resource = CourseResource::create([
        'course_session_id' => $session->id,
        'uploaded_by'       => User::factory()->create()->id,
        'title'             => 'Sample',
        'description'       => 'Desc',
        'file_name'         => 'a.txt',
        'file_path'         => 'course_materials/a.txt',
        'file_type'         => 'text/plain',
        'file_size'         => 100,
    ]);

    $this->assertInstanceOf(CourseSession::class, $resource->courseSession);
    $this->assertEquals($session->id, $resource->courseSession->id);
});

test('course resource belongs to uploader', function () {
    $teacher = User::factory()->create();

    $resource = CourseResource::create([
        'course_session_id' => CourseSession::factory()->create()->id,
        'uploaded_by'       => $teacher->id,
        'title'             => 'Sample',
        'description'       => 'Desc',
        'file_name'         => 'b.txt',
        'file_path'         => 'course_materials/b.txt',
        'file_type'         => 'text/plain',
        'file_size'         => 150,
    ]);

    $this->assertInstanceOf(User::class, $resource->uploadedBy);
    $this->assertEquals($teacher->id, $resource->uploadedBy->id);
});

test('course resource has correct fillable attributes', function () {
    $expected = [
        'course_session_id',
        'uploaded_by',
        'title',
        'description',
        'file_name',
        'file_path',
        'file_type',
        'file_size',
    ];

    expect((new CourseResource)->getFillable())->toBe($expected);
});

test('file_size is cast to integer', function () {
    $resource = new CourseResource([
        'file_size' => '5120',
    ]);

    expect($resource->file_size)->toBeInt()->toBe(5120);
});
