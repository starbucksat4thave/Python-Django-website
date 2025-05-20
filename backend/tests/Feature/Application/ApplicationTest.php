<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\ApplicationTemplate;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

uses()->group('application');
uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('local');

    // Create roles
    Role::create(['name' => 'student']);
    Role::create(['name' => 'teacher']);

    // Create test users
    $this->student = User::factory()->create()->assignRole('student');
    $this->teacher = User::factory()->create()->assignRole('teacher');

    // Seed application templates
    $this->seed(\Database\Seeders\ApplicationTemplateSeeder::class);

    // Get first template for testing
    $this->template = ApplicationTemplate::first();
});

// ApplicationTemplateController Tests
it('retrieves all application templates', function () {
    $response = $this->actingAs($this->student) // Add authentication
    ->getJson('/api/applications/templates');

    $response->assertStatus(200)
        ->assertJsonCount(2, 'data')
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'type', 'title']
            ]
        ]);
});

it('shows a specific application template with placeholders', function () {
    $response = $this->actingAs($this->student) // Add authentication
    ->getJson("/api/applications/templates/{$this->template->id}");

    $response->assertStatus(200)
        ->assertJson([
            'data' => [
                'placeholders' => ['name', 'id', 'program', 'start_date', 'end_date', 'reason']
            ]
        ]);
});

it('returns 404 for non-existent template', function () {
    $response = $this->actingAs($this->student) // Add authentication
    ->getJson('/api/applications/templates/999');
    $response->assertStatus(404);
});

// ApplicationController Tests
it('successfully submits an application with attachment', function () {
    $file = UploadedFile::fake()->create('document.pdf', 1000);

    $response = $this->actingAs($this->student)
        ->postJson('/api/applications/submit', [
            'application_template_id' => $this->template->id,
            'placeholders' => json_encode([ // Proper JSON format
                'name' => 'John Doe',
                'id' => '12345',
                'program' => 'Computer Science',
                'start_date' => '2024-01-01',
                'end_date' => '2024-01-05',
                'reason' => 'Family emergency'
            ]),
            'attachment' => $file
        ]);

    $response->assertStatus(201)
        ->assertJson([
            'data' => [
                'status' => 'pending'
            ]
        ]);
});

it('validates application submission', function () {
    $response = $this->actingAs($this->student)
        ->postJson('/api/applications/submit', [
            'placeholders' => json_encode('invalid') // Send valid JSON structure
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors([
            'application_template_id',
            'placeholders'
        ]);
});

it('retrieves pending applications for authorized teacher', function () {
    Application::create([
        'user_id' => $this->student->id,
        'application_template_id' => $this->template->id,
        'body' => 'Test content', // Add required field
        'status' => 'pending',
        'authorized_by' => $this->teacher->id
    ]);

    $response = $this->actingAs($this->teacher)
        ->getJson('/api/applications/pending');

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'user' => ['id', 'name'],
                    'application_template' => ['id', 'title']
                ]
            ]
        ]);
});

it('approves an application and generates PDF', function () {
    $application = Application::create([
        'user_id' => $this->student->id,
        'application_template_id' => $this->template->id,
        'body' => 'Test content', // Add required field
        'status' => 'pending',
        'authorized_by' => $this->teacher->id
    ]);

    $response = $this->actingAs($this->teacher)
        ->postJson("/api/applications/{$application->id}/authorize", [
            'action' => 'approve'
        ]);

    $response->assertStatus(200);
    expect($application->fresh()->status)->toBe('approved');
    Storage::disk('local')->assertExists($application->fresh()->authorized_copy);
});

it('prevents unauthorized approval attempts', function () {
    $application = Application::create([
        'user_id' => $this->student->id,
        'application_template_id' => $this->template->id,
        'body' => 'Test content', // Add required field
        'status' => 'pending',
        'authorized_by' => $this->teacher->id
    ]);

    $response = $this->actingAs($this->student)
        ->postJson("/api/applications/{$application->id}/authorize", [
            'action' => 'approve'
        ]);

    $response->assertStatus(500);
});

it('downloads authorized copy when approved', function () {
    $application = Application::create([
        'user_id' => $this->student->id,
        'application_template_id' => $this->template->id,
        'body' => 'Test content', // Add required field
        'status' => 'approved',
        'authorized_copy' => 'authorized_applications/test.pdf',
        'authorized_by' => $this->teacher->id
    ]);

    Storage::put($application->authorized_copy, 'PDF content');

    $response = $this->actingAs($this->student)
        ->getJson("/api/applications/{$application->id}/download");

    $response->assertOk()
        ->assertHeader('Content-Disposition');
});

it('retrieves student applications', function () {
    Application::create([
        'user_id' => $this->student->id,
        'application_template_id' => $this->template->id,
        'body' => 'Test 1', // Add required field
        'status' => 'pending'
    ]);

    Application::create([
        'user_id' => $this->student->id,
        'application_template_id' => $this->template->id,
        'body' => 'Test 2', // Add required field
        'status' => 'approved'
    ]);

    $response = $this->actingAs($this->student)
        ->getJson('/api/applications/my-applications');

    $response->assertStatus(200)
        ->assertJsonCount(2, 'data');
});

it('manages application attachments', function () {
    $application = Application::create([
        'user_id' => $this->student->id,
        'application_template_id' => $this->template->id,
        'body' => 'Test content', // Add required field
        'attachment' => 'application_attachments/test.pdf'
    ]);

    Storage::put($application->attachment, 'content');

    // Test download
    $response = $this->actingAs($this->student)
        ->getJson("/api/applications/{$application->id}/attachment");

    $response->assertOk()
        ->assertHeader('Content-Disposition');

    // Test missing attachment
    Storage::delete($application->attachment);
    $response = $this->actingAs($this->student)
        ->getJson("/api/applications/{$application->id}/attachment");

    $response->assertStatus(404);
});
