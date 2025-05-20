<?php

namespace App\Exceptions\Enrollment;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class UnauthorizedAccessException extends Exception
{
    public function render(): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => 'You do not have permission to update some enrollments.',
        ], 403);
    }
}
