<?php

use App\Models\Course;
use App\Models\User;
use function Pest\Laravel\getJson;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

const COURSES_ENDPOINT = '/api/courses';

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->token = $this->user->createToken('test-token')->plainTextToken;
});

// CourseController@showAll tests
it('retrieves all courses when authenticated', function () {
    Course::factory()->count(3)->create();

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->token,
    ])->getJson(COURSES_ENDPOINT);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'status',
            'message',
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'code',
                    'credit',
                    'department_id',
                    'created_at',
                    'updated_at'
                ]
            ]
        ])
        ->assertJsonCount(3, 'data');
});

it('returns empty data array when no courses exist', function () {
    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->token,
    ])->getJson(COURSES_ENDPOINT);

    $response->assertStatus(200)
        ->assertJsonPath('data', []);
});

it('denies access when unauthenticated', function () {
    $response = getJson(COURSES_ENDPOINT);
    $response->assertStatus(401);
});

// CourseController@show tests
it('retrieves specific course when authenticated', function () {
    $course = Course::factory()->create();

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->token,
    ])->getJson(COURSES_ENDPOINT."/{$course->id}");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'status',
            'message',
            'data' => [
                'id',
                'name',
                'code',
                'credit',
                'department_id',
                'created_at',
                'updated_at'
            ]
        ])
        ->assertJsonPath('data.id', $course->id);
});

it('returns 404 for non-existent course', function () {
    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->token,
    ])->getJson(COURSES_ENDPOINT.'/999');

    $response->assertStatus(404)
        ->assertJson([
            'status' => 'error',
            'message' => 'Course not found.'
        ]);
});
