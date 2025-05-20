<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    /** @use HasFactory<\Database\Factories\DepartmentFactory> */
    use HasFactory;

    protected $fillable = ['name', 'code', 'faculty', 'short_name'];

    // Each department can have many users
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
    public function notices(): HasMany
    {
        return $this->hasMany(Notice::class);
    }
    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }
}
