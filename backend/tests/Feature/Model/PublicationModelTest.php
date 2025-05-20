<?php

use App\Models\Publication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('publication can be created with valid attributes', function () {
    $data = [
        'title'          => 'Deep Learning Advances',
        'doi'            => '10.1000/xyz123',
        'abstract'       => 'An overview of recent breakthroughs in deep neural networks.',
        'journal'        => 'Journal of AI Research',
        'volume'         => '42',
        'issue'          => '7',
        'pages'          => '123-145',
        'published_date' => '2025-04-01',
        'url'            => 'https://example.com/dl-advances',
        'pdf_link'       => 'https://example.com/dl-advances.pdf',
    ];

    $pub = Publication::create($data);

    $this->assertDatabaseHas('publications', $data);
    $this->assertInstanceOf(Publication::class, $pub);
});

test('publication belongs to many users', function () {
    $pub   = Publication::factory()->create();
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $pub->users()->attach([$user1->id, $user2->id]);

    $attached = $pub->users()->pluck('user_id')->all();

    expect($attached)->toContain($user1->id)
        ->and($attached)->toContain($user2->id)
        ->and($pub->users)->toHaveCount(2);
});

test('publication has correct fillable attributes', function () {
    $expected = [
        'title',
        'doi',
        'abstract',
        'journal',
        'volume',
        'issue',
        'pages',
        'published_date',
        'url',
        'pdf_link',
    ];

    expect((new Publication)->getFillable())->toBe($expected);
});
