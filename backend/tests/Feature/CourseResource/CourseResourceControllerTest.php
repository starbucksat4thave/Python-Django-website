<?php

use App\Models\User;
use App\Models\CourseSession;
use App\Models\CourseResource;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use function Pest\Laravel\{actingAs, postJson, getJson, deleteJson, seed};

uses(RefreshDatabase::class);

beforeEach(function () {
    // Fresh DB each time, so seeding here won't duplicate
    seed(RolesAndPermissionsSeeder::class);
    Storage::fake('local');
});

it('allows a teacher to upload a course resource', function () {
    $teacher = User::factory()->create()->assignRole('teacher');
    $courseSession = CourseSession::factory()->create(['teacher_id' => $teacher->id]);

    actingAs($teacher);

    $file = UploadedFile::fake()->create('test.pdf', 100, 'application/pdf');

    postJson('/api/course-resources/upload', [
        'course_session_id' => $courseSession->id,
        'title' => 'Test Resource',
        'description' => 'Test description',
        'file' => $file,
    ])->assertStatus(201);

    // Verify database record
    $resource = CourseResource::first();
    expect($resource)
        ->file_name->toBe('test.pdf')
        ->file_size->toBe(100 * 1024); // 512,000 bytes

    // Verify file storage
    Storage::disk('local')->assertExists($resource->file_path);
});

it('allows a teacher to list course resources', function () {
    $teacher = User::factory()->create()->assignRole('teacher');
    $courseSession = CourseSession::factory()->create(['teacher_id' => $teacher->id]);

    // Manually create resource
    CourseResource::create([
        'course_session_id' => $courseSession->id,
        'uploaded_by'       => $teacher->id,
        'title'             => 'Sample Resource',
        'description'       => 'Sample description',
        'file_name'         => 'sample.pdf',
        'file_path'         => 'course_materials/sample.pdf',
        'file_type'         => 'application/pdf',
        'file_size'         => 500,
    ]);

    actingAs($teacher);

    getJson("/api/course-resources/{$courseSession->id}")
        ->assertOk()
        ->assertJsonStructure(['resources']);
});

it('allows a teacher to download own uploaded file', function () {
    $teacher = User::factory()->create()->assignRole('teacher');
    $courseSession = CourseSession::factory()->create(['teacher_id' => $teacher->id]);

    // Create a fake file with proper metadata
    $file = UploadedFile::fake()->create('test.pdf', 500, 'application/pdf');
    Storage::disk('local')->putFileAs('course_materials', $file, $file->hashName());

    $resource = CourseResource::create([
        'course_session_id' => $courseSession->id,
        'uploaded_by' => $teacher->id,
        'title' => 'Test Document',
        'description' => 'Test Description',
        'file_name' => 'test.pdf',
        'file_path' => 'course_materials/'.$file->hashName(),
        'file_type' => 'application/pdf',
        'file_size' => $file->getSize(), // Get actual fake file size
    ]);

    actingAs($teacher);

    getJson("/api/course-resources/download/{$resource->id}")
        ->assertOk()
        ->assertHeader('content-disposition', 'attachment; filename=test.pdf');
});

it('allows a teacher to update their uploaded resource', function () {
    $teacher = User::factory()->create()->assignRole('teacher');
    $courseSession = CourseSession::factory()->create(['teacher_id' => $teacher->id]);

    Storage::disk('local')->put('course_materials/old.pdf', 'Old file');

    // Manually create resource
    $resource = CourseResource::create([
        'course_session_id' => $courseSession->id,
        'uploaded_by'       => $teacher->id,
        'title'             => 'Old Title',
        'description'       => 'Old Description',
        'file_name'         => 'old.pdf',
        'file_path'         => 'course_materials/old.pdf',
        'file_type'         => 'application/pdf',
        'file_size'         => 100,
    ]);

    actingAs($teacher);

    $newFile = UploadedFile::fake()->create('new_file.pdf', 100, 'application/pdf');

    postJson("/api/course-resources/{$resource->id}", [
        '_method'     => 'PUT',
        'title'       => 'Updated Title',
        'description' => 'Updated description',
        'file'        => $newFile,
    ])->assertStatus(200)
        ->assertJsonFragment(['title' => 'Updated Title']);

    Storage::disk('local')->assertMissing('course_materials/old.pdf');
    Storage::disk('local')->assertExists('course_materials/' . $newFile->hashName());
});

it('allows a teacher to delete a resource', function () {
    $teacher = User::factory()->create()->assignRole('teacher');
    $courseSession = CourseSession::factory()->create(['teacher_id' => $teacher->id]);

    Storage::disk('local')->put('course_materials/deletable.pdf', 'Content to delete');

    // Manually create resource
    $resource = CourseResource::create([
        'course_session_id' => $courseSession->id,
        'uploaded_by'       => $teacher->id,
        'title'             => 'Deletable Resource',
        'description'       => 'Resource to delete',
        'file_name'         => 'deletable.pdf',
        'file_path'         => 'course_materials/deletable.pdf',
        'file_type'         => 'application/pdf',
        'file_size'         => 500,
    ]);

    actingAs($teacher);

    deleteJson("/api/course-resources/{$resource->id}")
        ->assertStatus(200)
        ->assertJson(['message' => 'Resource deleted successfully']);

    Storage::disk('local')->assertMissing('course_materials/deletable.pdf');
});
