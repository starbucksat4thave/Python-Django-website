<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Permission\Models\Permission;

class Role extends \Spatie\Permission\Models\Role
{
    protected $fillable = ['name'];

    protected $attributes = [
        'guard_name' => 'web', // Default guard_name
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'model_has_roles', 'role_id', 'model_id');
    }


}
