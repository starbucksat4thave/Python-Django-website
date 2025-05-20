<?php

namespace App\Exceptions\Enrollment;

use Exception;
use Illuminate\Http\JsonResponse;

class NotAStudentException extends Exception
{
    public function render(): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => 'Only students can enroll in courses.',
        ], 403);
    }
}
