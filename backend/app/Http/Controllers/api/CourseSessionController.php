<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\CourseSession;
use Exception;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CourseSessionController extends Controller
{
    public function show()
    {
        try {
            $teacher_id = Auth::id();
            // Fetch the latest session for each course the teacher teaches
            $courseSessions = CourseSession::with('course.department')
                ->where('teacher_id', $teacher_id)
                ->orderBy('session', 'desc') // Sort by latest session
                ->get()
                ->unique('course_id') // Keep only one session per course
                ->values(); // Re-index the collection

            if ($courseSessions->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No course sessions found.',
                ], Response::HTTP_NOT_FOUND);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Course sessions fetched successfully.',
                'data' => $courseSessions,
            ], Response::HTTP_OK);
        }
        catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch course sessions.',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function showOne($courseSession_id)
    {
        try {
            $teacher_id = Auth::id();

            $courseSession = CourseSession::with([
                'course.department',
                'enrollments.student' // Fetch enrollments related to the course session
            ])
                ->where('teacher_id', $teacher_id)
                ->where('id', $courseSession_id)
                ->first();

            if (!$courseSession) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Course session not found.',
                ], Response::HTTP_NOT_FOUND);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Course session fetched successfully.',
                'data' => $courseSession,
            ], Response::HTTP_OK);
        }
        catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch course session.',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    public function showPastSessions($courseSession_id)
    {
        try {
            $teacher_id = Auth::id();
            $course_id = CourseSession::where('id', $courseSession_id)->first()->course_id;
            // Get past course sessions (excluding the current one)
            $courseSessions = CourseSession::with('course')
                ->where('teacher_id', $teacher_id)
                ->where('course_id', $course_id)
                ->where('id', '!=', $courseSession_id) // Exclude current session
                ->orderByDesc('session') // Sort by session (latest first)
                ->get();

            if ($courseSessions->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No past sessions found.',
                ], Response::HTTP_NOT_FOUND);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Past sessions fetched successfully.',
                'data' => $courseSessions,
            ], Response::HTTP_OK);
        }
        catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch past sessions.',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
