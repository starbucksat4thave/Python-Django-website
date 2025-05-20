<?php

use App\Models\User;
use Illuminate\Support\Facades\Password;
use function Pest\Laravel\postJson;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

const FORGET_PASSWORD_URL = '/api/auth/forget-password';

// Successful password reset request
test('sends password reset link for valid email', function () {
    $user = User::factory()->create();

    Password::shouldReceive('sendResetLink')
        ->once()
        ->andReturn(Password::RESET_LINK_SENT);

    $response = postJson(FORGET_PASSWORD_URL, [
        'email' => $user->email,
    ]);

    $response->assertStatus(201)
        ->assertJson(['message' => __(Password::RESET_LINK_SENT)]);
});

// Validation tests
test('returns validation errors for invalid requests', function (array $data) {
    $response = postJson(FORGET_PASSWORD_URL, $data);
    $response->assertStatus(422);
})->with([
    'missing email' => [[]],
    'invalid email format' => [['email' => 'not-an-email']],
    'non-existent email' => [['email' => 'missing@example.com']],
]);

// Password service error responses
test('handles password service errors correctly', function (string $status) {
    $user = User::factory()->create();

    Password::shouldReceive('sendResetLink')
        ->once()
        ->andReturn($status);

    $response = postJson(FORGET_PASSWORD_URL, [
        'email' => $user->email,
    ]);

    $response->assertStatus(400)
        ->assertJson(['message' => __($status)]);
})->with([
    'throttled' => [Password::RESET_THROTTLED],
    'invalid user' => [Password::INVALID_USER],
]);

// Server error handling
test('returns 500 for unexpected errors', function () {
    $user = User::factory()->create();

    Password::shouldReceive('sendResetLink')
        ->once()
        ->andThrow(new \RuntimeException('Mail server error'));

    $response = postJson(FORGET_PASSWORD_URL, [
        'email' => $user->email,
    ]);

    $response->assertStatus(500)
        ->assertJson([
            'message' => 'Something went wrong',
            'error' => 'Mail server error'
        ]);
});
