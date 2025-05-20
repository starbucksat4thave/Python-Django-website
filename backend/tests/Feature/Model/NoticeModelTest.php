<?php

use App\Models\Department;
use App\Models\Notice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('notice can be created with valid attributes', function () {
    $dept      = Department::factory()->create();
    $publisher = User::factory()->create();
    $approver1 = User::factory()->create();
    $approver2 = User::factory()->create();

    $notice = Notice::create([
        'title'         => 'Holiday Schedule',
        'content'       => 'University will be closed on Eid.',
        'department_id' => $dept->id,
        'published_by'  => $publisher->id,
        'published_on'  => now(),
        'archived_on'   => null,
        'file'          => 'notices/eid.pdf',
    ]);

    // attach approvers via pivot
    $notice->approvedBy()->attach([
        $approver1->id => ['is_approved' => false],
        $approver2->id => ['is_approved' => true],
    ]);

    $this->assertDatabaseHas('notices', [
        'title'         => 'Holiday Schedule',
        'content'       => 'University will be closed on Eid.',
        'department_id' => $dept->id,
        'published_by'  => $publisher->id,
        'file'          => 'notices/eid.pdf',
    ]);

    $this->assertDatabaseHas('notice_user', [
        'notice_id'   => $notice->id,
        'user_id'     => $approver1->id,
        'is_approved' => false,
    ]);
    $this->assertDatabaseHas('notice_user', [
        'notice_id'   => $notice->id,
        'user_id'     => $approver2->id,
        'is_approved' => true,
    ]);
});

test('notice belongs to a department', function () {
    $dept   = Department::factory()->create();
    $notice = Notice::factory()->create([
        'department_id' => $dept->id,
    ]);

    $this->assertInstanceOf(Department::class, $notice->department);
    $this->assertEquals($dept->id, $notice->department->id);
});

test('notice belongs to a publisher', function () {
    $publisher = User::factory()->create();
    $notice    = Notice::factory()->create([
        'published_by' => $publisher->id,
    ]);

    $this->assertInstanceOf(User::class, $notice->publisher);
    $this->assertEquals($publisher->id, $notice->publisher->id);
});

test('notice has many approvers via approvedBy()', function () {
    $notice    = Notice::factory()->create();
    $approver1 = User::factory()->create();
    $approver2 = User::factory()->create();

    $notice->approvedBy()->attach([
        $approver1->id => ['is_approved' => true],
        $approver2->id => ['is_approved' => false],
    ]);

    $approvers = $notice->approvedBy()->get();

    expect($approvers)->toHaveCount(2)
        ->and($approvers->first()->pivot->is_approved)->toBeInt(1);
});

test('notice has correct fillable attributes', function () {
    $expected = [
        'title',
        'content',
        'department_id',
        'published_by',
        'published_on',
        'archived_on',
        'file',
    ];

    expect((new Notice)->getFillable())->toBe($expected);
});
