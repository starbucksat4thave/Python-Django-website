<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Publication;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PublicationController extends Controller
{
    public function show($id): JsonResponse
    {
        try {
            $publication = Publication::findOrFail($id);
            return response()->json([
                'publication' => $publication,
            ], 200);
        }catch (\Exception $e){
            Log::error('Show Error', [
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'message' => 'Publication not found',
            ], 404);
        }
    }

    public function showAll(){
        try {
            $user = Auth::user();
            $publications = $user->publications()->get()->makeHidden('pivot');

            return response()->json([
                'publications' => $publications,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Show All Error', [
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'message' => 'Error fetching publications',
            ], 500);
        }
    }
}
