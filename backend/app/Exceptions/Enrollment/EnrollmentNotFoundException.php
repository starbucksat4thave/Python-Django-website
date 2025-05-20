<?php

namespace App\Exceptions\Enrollment;

use Exception;
use Illuminate\Http\JsonResponse;

class EnrollmentNotFoundException extends Exception
{
    public function render(): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => 'Enrollment not found.',
        ], 404);
    }
}
