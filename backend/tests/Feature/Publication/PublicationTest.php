<?php

use App\Models\User;
use App\Models\Publication;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user      = User::factory()->create();
    $this->otherUser = User::factory()->create();

    // Insert two publications
    $this->pub1 = Publication::create([
        'doi'            => '10.1000/xyz123',
        'title'          => 'First Publication',
        'abstract'       => 'Abstract One',
        'journal'        => 'Journal One',
        'volume'         => '1',
        'issue'          => '1',
        'pages'          => '1–10',
        'published_date' => '2025-01-01',
        'url'            => 'https://example.com/1',
        'pdf_link'       => 'https://example.com/1.pdf',
    ]);
    $this->pub2 = Publication::create([
        'doi'            => '10.1000/xyz456',
        'title'          => 'Second Publication',
        'abstract'       => 'Abstract Two',
        'journal'        => 'Journal Two',
        'volume'         => '2',
        'issue'          => '2',
        'pages'          => '11–20',
        'published_date' => '2025-02-01',
        'url'            => 'https://example.com/2',
        'pdf_link'       => 'https://example.com/2.pdf',
    ]);

    DB::table('publication_user')->insert([
        ['publication_id' => $this->pub1->id, 'user_id' => $this->user->id],
        ['publication_id' => $this->pub2->id, 'user_id' => $this->user->id],
    ]);
});

it('fails to list publications when not authenticated', function () {
    getJson('/api/publications')
        ->assertStatus(401)
        ->assertJson(['message' => 'Unauthenticated.']);
});

it('lists only this user’s publications when authenticated', function () {
    actingAs($this->user, 'sanctum')
        ->getJson('/api/publications')
        ->assertOk()
        ->assertJsonCount(2, 'publications')
        ->assertJsonFragment(['title' => 'First Publication'])
        ->assertJsonFragment(['title' => 'Second Publication']);
});

it('returns empty list for a user with no publications', function () {
    actingAs($this->otherUser, 'sanctum')
        ->getJson('/api/publications')
        ->assertOk()
        ->assertJsonCount(0, 'publications');
});

it('fails to show a publication when not authenticated', function () {
    getJson("/api/publications/{$this->pub1->id}")
        ->assertStatus(401)
        ->assertJson(['message' => 'Unauthenticated.']);
});

it('shows a single publication by its ID when authenticated', function () {
    actingAs($this->user, 'sanctum')
        ->getJson("/api/publications/{$this->pub1->id}")
        ->assertOk()
        ->assertJson([
            'publication' => [
                'id'    => $this->pub1->id,
                'title' => 'First Publication',
            ],
        ]);
});

it('returns 404 for non-existent publication when authenticated', function () {
    actingAs($this->user, 'sanctum')
        ->getJson('/api/publications/9999')
        ->assertStatus(404)
        ->assertJson(['message' => 'Publication not found']);
});
