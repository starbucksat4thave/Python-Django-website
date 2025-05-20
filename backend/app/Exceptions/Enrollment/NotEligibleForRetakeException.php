<?php

namespace App\Exceptions\Enrollment;

use Exception;
use Illuminate\Http\JsonResponse;

class NotEligibleForRetakeException extends Exception
{
    public function render(): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => 'You are not eligible to retake this course.',
        ], 403);
    }
}
