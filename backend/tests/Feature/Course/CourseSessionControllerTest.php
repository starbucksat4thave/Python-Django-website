<?php

use App\Models\User;
use App\Models\Course;
use App\Models\CourseSession;
use function Pest\Laravel\getJson;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

const ACTIVE_SESSIONS_ENDPOINT = '/api/courses/active';

beforeEach(function () {
    $this->teacher = User::factory()->create();
    $this->token = $this->teacher->createToken('test-token')->plainTextToken;
    $this->course = Course::factory()->create();
    $this->currentYear = (string) now()->year;
});

// CourseSessionController@show tests
it('retrieves latest course sessions for teacher', function () {
    CourseSession::factory()->create([
        'teacher_id' => $this->teacher->id,
        'course_id' => $this->course->id,
        'session' => '2020'
    ]);

    CourseSession::factory()->create([
        'teacher_id' => $this->teacher->id,
        'course_id' => $this->course->id,
        'session' => $this->currentYear // Latest session
    ]);

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->token,
    ])->getJson(ACTIVE_SESSIONS_ENDPOINT);

    $response->assertStatus(200)
        ->assertJsonPath('data.0.session', $this->currentYear)
        ->assertJsonCount(1, 'data');
});

// CourseSessionController@showOne tests
it('retrieves specific course session with enrollments', function () {
    $session = CourseSession::factory()->create([
        'teacher_id' => $this->teacher->id,
        'session' => $this->currentYear
    ]);

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->token,
    ])->getJson(ACTIVE_SESSIONS_ENDPOINT."/{$session->id}");

    $response->assertStatus(200)
        ->assertJsonPath('data.session', $this->currentYear);
});

// CourseSessionController@showPastSessions tests
it('retrieves past course sessions', function () {
    $currentSession = CourseSession::factory()->create([
        'teacher_id' => $this->teacher->id,
        'course_id' => $this->course->id,
        'session' => $this->currentYear
    ]);

    $pastSessions = CourseSession::factory()->count(2)->create([
        'teacher_id' => $this->teacher->id,
        'course_id' => $this->course->id,
        'session' => (string)(now()->year - 1) // Previous year
    ]);

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->token,
    ])->getJson(ACTIVE_SESSIONS_ENDPOINT."/{$currentSession->id}/past-sessions");

    $response->assertStatus(200)
        ->assertJsonCount(2, 'data');
});

