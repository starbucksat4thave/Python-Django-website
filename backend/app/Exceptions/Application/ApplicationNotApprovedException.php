<?php

namespace App\Exceptions\Application;

use Exception;
use Illuminate\Http\JsonResponse;

class ApplicationNotApprovedException extends Exception
{
    public function render(): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => 'Application is not yet approved or missing file.',
        ], 403);
    }
}
