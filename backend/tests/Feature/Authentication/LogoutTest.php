<?php

use App\Models\User;
use Laravel\Sanctum\Sanctum;
use function Pest\Laravel\postJson;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

const LOGOUT_API_ENDPOINT = '/api/auth/logout';

test('authenticated user can logout successfully', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;

    $response = postJson(LOGOUT_API_ENDPOINT, [], [
        'Authorization' => 'Bearer ' . $token
    ]);

    $response->assertStatus(200)
        ->assertJson(['message' => 'User logged out successfully']);

    $this->assertCount(0, $user->tokens);
});

test('unauthenticated user cannot logout', function () {
    $response = postJson(LOGOUT_API_ENDPOINT);

    $response->assertStatus(401)
        ->assertJson(['message' => 'Unauthenticated.']);
});

test('logging out deletes only current token', function () {
    $user = User::factory()->create();

    $token1 = $user->createToken('token1')->plainTextToken;
    $plainToken1 = explode('|', $token1)[1];

    $token2 = $user->createToken('token2')->plainTextToken;
    $plainToken2 = explode('|', $token2)[1];

    postJson(LOGOUT_API_ENDPOINT, [], [
        'Authorization' => 'Bearer ' . $token1
    ])->assertStatus(200);

    $this->assertDatabaseMissing('personal_access_tokens', [
        'token' => hash('sha256', $plainToken1)
    ]);

    $this->assertDatabaseHas('personal_access_tokens', [
        'token' => hash('sha256', $plainToken2)
    ]);
});
