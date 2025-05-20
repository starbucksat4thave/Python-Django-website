<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'credit',
        'year',
        'semester',
        'department_id',
    ];

    public function courseSessions(): hasMany
    {
        return $this->hasMany(CourseSession::class);
    }
    public function department(): BelongsTo
    {
        return $this->BelongsTo(Department::class);
    }
}
