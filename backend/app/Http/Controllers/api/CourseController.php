<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseSession;
use Symfony\Component\HttpFoundation\Response;

class CourseController extends Controller
{
    public function showAll(): \Illuminate\Http\JsonResponse
    {
        try {
            $courses = Course::all();

            return response()->json([
                'status' => 'success',
                'message' => 'Course sessions fetched successfully.',
                'data' => $courses,
            ], Response::HTTP_OK);
        }
        catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch course sessions.',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show($course_id): \Illuminate\Http\JsonResponse
    {
        try {
            $course = Course::find($course_id);

            if (!$course) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Course not found.',
                ], Response::HTTP_NOT_FOUND);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Course fetched successfully.',
                'data' => $course,
            ], Response::HTTP_OK);
        }
        catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch course.',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
