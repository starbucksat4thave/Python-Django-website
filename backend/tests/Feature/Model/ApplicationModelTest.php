<?php

use App\Models\Application;
use App\Models\ApplicationTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('application can be created with valid attributes', function () {
    // create related records manually
    $user     = User::factory()->create();
    $template = ApplicationTemplate::create([
        'type'  => 'leave',
        'title' => 'Leave Application',
        'body'  => '<p>Requesting leave for medical reasons</p>',
    ]);
    $approver = User::factory()->create();

    $data = [
        'user_id'                 => $user->id,
        'application_template_id' => $template->id,
        'body'                    => '<p>Details here</p>',
        'attachment'              => 'uploads/doc1.pdf',
        'authorized_copy'         => null,
        'status'                  => 'pending',
        'authorized_by'           => $approver->id,
    ];

    $app = Application::create($data);

    $this->assertDatabaseHas('applications', [
        'id'                       => $app->id,
        'user_id'                  => $user->id,
        'application_template_id'  => $template->id,
        'status'                   => 'pending',
    ]);

    $this->assertInstanceOf(Application::class, $app);
});

test('application belongs to a user', function () {
    $user = User::factory()->create();

    $app = Application::create([
        'user_id'                 => $user->id,
        'application_template_id' => ApplicationTemplate::create([
            'type'  => 'transcript',
            'title' => 'Transcript Request',
            'body'  => '<p>Need transcript</p>',
        ])->id,
        'body'            => 'x',
        'attachment'      => null,
        'authorized_copy' => null,
        'status'          => 'pending',
        'authorized_by'   => null,
    ]);

    $this->assertInstanceOf(User::class, $app->user);
    $this->assertEquals($user->id, $app->user->id);
});

test('application belongs to a template', function () {
    $template = ApplicationTemplate::create([
        'type'  => 'certificate',
        'title' => 'Certificate Request',
        'body'  => '<p>Cert details</p>',
    ]);

    $app = Application::create([
        'user_id'                 => User::factory()->create()->id,
        'application_template_id' => $template->id,
        'body'                    => 'x',
        'attachment'              => null,
        'authorized_copy'         => null,
        'status'                  => 'pending',
        'authorized_by'           => null,
    ]);

    $this->assertInstanceOf(ApplicationTemplate::class, $app->applicationTemplate);
    $this->assertEquals($template->id, $app->applicationTemplate->id);
});

test('application belongs to an approver via authorizedBy()', function () {
    $approver = User::factory()->create();

    $app = Application::create([
        'user_id'                 => User::factory()->create()->id,
        'application_template_id' => ApplicationTemplate::create([
            'type'  => 'leave',
            'title' => 'Leave App',
            'body'  => '<p>Body</p>',
        ])->id,
        'body'            => 'x',
        'attachment'      => null,
        'authorized_copy' => null,
        'status'          => 'approved',
        'authorized_by'   => $approver->id,
    ]);

    $this->assertInstanceOf(User::class, $app->authorizedBy);
    $this->assertEquals($approver->id, $app->authorizedBy->id);
});

test('fillable and casts are correctly defined', function () {
    $expectedFillable = [
        'user_id',
        'application_template_id',
        'body',
        'attachment',
        'authorized_copy',
        'status',
        'authorized_by',
    ];
    expect((new Application)->getFillable())->toBe($expectedFillable);

    $app = new Application([
        'body'            => '<p>test</p>',
        'attachment'      => 123,
        'authorized_copy' => 456,
        'status'          => 'approved',
    ]);

    $this->assertIsString($app->body);
    $this->assertIsString($app->attachment);
    $this->assertIsString($app->authorized_copy);
    $this->assertIsString($app->status);
});
