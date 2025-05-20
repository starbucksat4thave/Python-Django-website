<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;

uses(RefreshDatabase::class);

it('generates ID card PDF successfully when authenticated', function () {
    $user = User::factory()->create();

    actingAs($user, 'sanctum')
        ->getJson('/api/id-card')
        ->assertOk()
        ->assertHeader('Content-Type', 'application/pdf')
        ->assertHeader('Content-Disposition')
        ->assertSee('%PDF-', false);
});

it('fails to generate ID card when not authenticated', function () {
    // Use getJson here, not get()
    getJson('/api/id-card')
        ->assertStatus(401)
        ->assertJson(['message' => 'Unauthenticated.']);
});
