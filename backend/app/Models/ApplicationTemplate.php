<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApplicationTemplate extends Model
{
    protected $fillable = [
        'type',
        'title',
        'body',
    ];

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }
}
