<?php

namespace App\Exceptions\Application;

use Exception;
use Illuminate\Http\JsonResponse;

class AttachmentNotFoundException extends Exception
{
    public function render(): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => 'Attachment not found.',
        ], 404);
    }
}
