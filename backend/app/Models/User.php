<?php

namespace App\Models;

use App\Mail\WelcomeUserMail;
use App\Models\Course;
use App\Models\Department;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Laravel\Sanctum\HasApiTokens;
use Database\Factories\UserFactory;
use Illuminate\Notifications\Notifiable;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements CanResetPasswordContract
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable,CanResetPassword, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'university_id',
        'department_id',
        'session',
        'year',
        'semester',
        'dob',
        'phone',
        'address',
        'city',
        'designation',
        'publication_count',
        'image',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Define the many-to-many relationship with the Department model
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    // Define the one-to-many relationship with the Notice model
    public function notices(): HasMany
    {
        return $this->HasMany(Notice::class, 'published_by');
    }

    // Define the one-to-many relationship with the Course Session model
    public function courseSessions(): HasMany
    {
        return $this->hasMany(CourseSession::class, 'teacher_id');
    }

    // Define one-to-many relationship with the Enrollment model
    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class, 'student_id');
    }

    public function publications(): BelongsToMany
    {
        return $this->belongsToMany(Publication::class)->withTimestamps();
    }

    public function approvedNotices(): belongsToMany
    {
        return $this->belongsToMany(Notice::class, 'notice_user', 'user_id', 'notice_id')
                    ->withPivot('is_approved')
                    ->withTimestamps();
    }
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return  $this->hasRole(['admin', 'super-admin']);
    }
}
