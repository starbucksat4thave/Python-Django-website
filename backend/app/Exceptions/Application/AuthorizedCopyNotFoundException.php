<?php

namespace App\Exceptions\Application;

use Exception;
use Illuminate\Http\JsonResponse;

class AuthorizedCopyNotFoundException extends Exception
{
    public function render(): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => 'Authorized copy not found on server.',
        ], 404);
    }
}
