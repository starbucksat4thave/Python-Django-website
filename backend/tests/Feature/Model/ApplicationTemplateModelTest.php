<?php

use App\Models\ApplicationTemplate;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('application template can be created with valid attributes', function () {
    $data = [
        'type'  => 'leave',
        'title' => 'Leave Application',
        'body'  => '<p>Please approve my leave from April 5 to April 10.</p>',
    ];

    $template = ApplicationTemplate::create($data);

    $this->assertDatabaseHas('application_templates', $data);
    $this->assertInstanceOf(ApplicationTemplate::class, $template);
});

test('applications() returns a HasMany to Application with correct keys', function () {
    $relation = (new ApplicationTemplate)->applications();

    // 1) Relation is a HasMany
    $this->assertInstanceOf(HasMany::class, $relation);

    // 2) Related model is Application
    $this->assertEquals(
        \App\Models\Application::class,
        get_class($relation->getRelated())
    );

    // 3) Foreign key on applications table is template_id (or application_template_id—adjust if yours differs)
    $this->assertEquals(
        'application_template_id',
        $relation->getForeignKeyName(),
        'Expected applications() FK name to be application_template_id'
    );

});

test('application template has correct fillable attributes', function () {
    $expected = [
        'type',
        'title',
        'body',
    ];

    expect((new ApplicationTemplate)->getFillable())->toBe($expected);
});
