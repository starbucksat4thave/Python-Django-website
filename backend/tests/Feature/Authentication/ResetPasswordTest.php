<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use function Pest\Laravel\postJson;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

const RESET_PASSWORD_API_ENDPOINT = '/api/auth/reset-password';
const TEST_NEW_PASSWORD = 'NewPassword123!';
const TEST_EMAIL = 'test@example.com';

// Successful password reset
test('resets password with valid token', function () {
    $user = User::factory()->create();
    $token = Password::createToken($user);

    $response = postJson(RESET_PASSWORD_API_ENDPOINT, [
        'token' => $token,
        'email' => $user->email,
        'password' => TEST_NEW_PASSWORD,
        'password_confirmation' => TEST_NEW_PASSWORD,
    ]);

    $response->assertStatus(201)
        ->assertJson(['message' => __(Password::PASSWORD_RESET)]);

    $this->assertTrue(Hash::check(TEST_NEW_PASSWORD, $user->fresh()->password));
});

// Invalid token case
test('fails with invalid token', function () {
    $user = User::factory()->create();

    $response = postJson(RESET_PASSWORD_API_ENDPOINT, [
        'token' => 'invalid-token',
        'email' => $user->email,
        'password' => TEST_NEW_PASSWORD,
        'password_confirmation' => TEST_NEW_PASSWORD,
    ]);

    $response->assertStatus(400)
        ->assertJson(['message' => __(Password::INVALID_TOKEN)]);
});

// Validation failures
test('validates input requirements', function (array $data, array $errors) {
    $response = postJson(RESET_PASSWORD_API_ENDPOINT, $data);
    $response->assertStatus(422)
        ->assertJsonValidationErrors($errors);
})->with([
    'missing token' => [
        ['email' => TEST_EMAIL, 'password' => 'password', 'password_confirmation' => 'password'],
        ['token']
    ],
    'invalid email' => [
        ['token' => 'token', 'email' => 'not-an-email', 'password' => 'password', 'password_confirmation' => 'password'],
        ['email']
    ],
    'short password' => [
        ['token' => 'token', 'email' => TEST_EMAIL, 'password' => 'short', 'password_confirmation' => 'short'],
        ['password']
    ],
    'mismatched passwords' => [
        ['token' => 'token', 'email' => TEST_EMAIL, 'password' => 'password', 'password_confirmation' => 'different'],
        ['password']
    ],
]);

// Non-existent user case
test('fails for non-existent email', function () {
    $response = postJson(RESET_PASSWORD_API_ENDPOINT, [
        'token' => 'any-token',
        'email' => 'missing@example.com',
        'password' => TEST_NEW_PASSWORD,
        'password_confirmation' => TEST_NEW_PASSWORD,
    ]);

    $response->assertStatus(400)
        ->assertJson(['message' => __(Password::INVALID_USER)]);
});

// Server error handling
test('handles server errors', function () {
    $user = User::factory()->create();

    Password::shouldReceive('reset')
        ->once()
        ->andThrow(new \RuntimeException('Database error'));

    $response = postJson(RESET_PASSWORD_API_ENDPOINT, [
        'token' => 'any-token',
        'email' => $user->email,
        'password' => TEST_NEW_PASSWORD,
        'password_confirmation' => TEST_NEW_PASSWORD,
    ]);

    $response->assertStatus(500)
        ->assertJson([
            'message' => 'Something went wrong',
            'error' => 'Database error'
        ]);
});
