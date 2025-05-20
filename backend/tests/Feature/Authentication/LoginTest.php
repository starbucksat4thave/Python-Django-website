<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use function Pest\Laravel\postJson;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

const TEST_USER_EMAIL = 'user@example.com';
const LOGIN_API_ENDPOINT = '/api/auth/login';

beforeEach(function () {
    $this->user = User::factory()->create([
        'email' => TEST_USER_EMAIL,
        'password' => Hash::make('password123'),
    ]);
});

it('allows a regular user to log in successfully', function () {
    $response = postJson(LOGIN_API_ENDPOINT, [
        'email' => TEST_USER_EMAIL,
        'password' => 'password123',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'token',
            'user' => [
                'id',
                'name',
                'email',
                'image',
                'university_id',
                'department_id',
                'session',
                'year',
                'semester',
                'dob',
                'phone',
                'address',
                'city',
                'designation',
                'publication_count',
            ],
        ]);
});

it('denies login with invalid credentials', function () {
    $response = postJson(LOGIN_API_ENDPOINT, [
        'email' => TEST_USER_EMAIL,
        'password' => 'wrongpassword',
    ]);

    $response->assertStatus(401)
        ->assertJson([
            'message' => 'Invalid credentials',
        ]);
});

it('denies login for admin users', function () {
    $adminRole = Role::create(['name' => 'admin']);
    $this->user->assignRole($adminRole);

    $response = postJson(LOGIN_API_ENDPOINT, [
        'email' => TEST_USER_EMAIL,
        'password' => 'password123',
    ]);

    $response->assertStatus(403)
        ->assertJson([
            'message' => 'Admins cannot log in here',
        ]);
});
