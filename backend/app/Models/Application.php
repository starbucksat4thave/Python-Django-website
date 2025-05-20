<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Application extends Model
{
    protected $fillable = [
        'user_id',
        'application_template_id',
        'body',
        'attachment',
        'authorized_copy',
        'status',
        'authorized_by',
    ];

    protected $casts = [
        'body' => 'string',
        'attachment' => 'string',
        'authorized_copy' => 'string',
        'status' => 'string',
    ];


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function applicationTemplate(): BelongsTo
    {
        return $this->belongsTo(ApplicationTemplate::class);
    }

    public function authorizedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'authorized_by');
    }
}
