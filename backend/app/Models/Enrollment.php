<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Enrollment extends Model
{
    /** @use HasFactory<\Database\Factories\EnrollmentFactory> */
    use HasFactory;

    protected $fillable = [
        'courseSession_id',
        'student_id',
        'is_enrolled',
        'class_assessment_marks',
        'final_term_marks',
    ];

    protected $casts = [
        'is_enrolled'            => 'boolean',
    ];

    public function courseSession(): BelongsTo
    {
        return $this->belongsTo(CourseSession::class, 'courseSession_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
