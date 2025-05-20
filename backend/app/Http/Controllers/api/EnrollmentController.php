<?php

namespace App\Http\Controllers\api;

use App\Exceptions\Enrollment\EnrollmentNotFoundException;
use App\Exceptions\Enrollment\NotAStudentException;
use App\Exceptions\Enrollment\NotEligibleForRetakeException;
use App\Exceptions\Enrollment\UnauthorizedAccessException;
use App\Http\Controllers\Controller;
use App\Models\CourseSession;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class EnrollmentController extends Controller
{
    public function storeAll($courseSession): void
    {
        $this->authorize('create', Enrollment::class);
        $course = $courseSession->course;
        // Fetch students matching the course's year and semester
        $students = User::role('student')
            ->where('department_id', $course->department_id)
            ->where('session', $courseSession->session)
            ->where('year', $course->year)
            ->where('semester', $course->semester)
            ->get();


        // Create enrollments for each matching student
        foreach ($students as $student) {
            Enrollment::create([
                'courseSession_id' => $courseSession->id,
                'student_id' => $student->id,
                'is_enrolled' => false, // True when payment is done
            ]);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            // Validate the incoming request data
            $validatedData = $request->validate([
                'course_id' => 'required|exists:courses,id',
            ]);

            $user = Auth::user();
            $courseSessionId = CourseSession::where('course_id', $validatedData['course_id'])->orderByDesc('session')->value('id');


            // Check if the user is a student
            if (!$user->hasRole('student')) {
                throw new NotAStudentException('Only students can enroll in courses.', 403);
            }

            // Check if the student can enroll in the course session
            if (!$this->canRetake($user->id, $courseSessionId)) {
                throw new NotEligibleForRetakeException('You are not eligible to retake this course.', 403);
            }

            // Create a new enrollment record
            Enrollment::create([
                'courseSession_id' => $courseSessionId,
                'student_id' => $user->id,
                'is_enrolled' => false, // True when payment is done
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Enrollment created successfully.',
            ], Response::HTTP_CREATED);

        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed. Check your data.',
                'errors' => $e->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            Log::error('Enrollment creation failed: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()? : 'An unexpected error occurred.',
            ], $e->getCode()?: Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    public function canRetake($studentId, $courseSessionId): bool
    {
        // Retrieve the student with their department and enrollments
        $student = User::with('department', 'enrollments.courseSession.course')->find($studentId);

        // Retrieve the course session with its department
        $courseSession = CourseSession::with('course.department')->find($courseSessionId);

        // Initialize the eligibility flag
        $canRetake = true;
        // Check if the student and course session belong to the same department
        if (($student->department_id !== $courseSession->course->department_id) ||
            ($student->enrollments->contains('courseSession_id', $courseSessionId))) {
            return false;
        }
        // Filter enrollments for the specific course and sort by session in descending order
        $enrollments = $student->enrollments
            ->filter(function ($enrollment) use ($courseSession) {
                return $enrollment->courseSession->course_id === $courseSession->course_id;
            })
            ->sortByDesc('courseSession.session');


        if ($enrollments->isEmpty()) {
            return false;
        }
        else {
            // Check if the student has passed any previous session
            foreach ($enrollments as $enrollment) {
                if (($enrollment->class_assessment_marks + $enrollment->final_term_marks) >= 40) {
                    $canRetake = false;
                    break;
                }
            }

            // Check if the latest enrollment is in the immediate next session and marks are less than 60
            $latestEnrollment = $enrollments->first();
            if ($latestEnrollment &&
                ($latestEnrollment->courseSession->session === $student->session) &&
                (($latestEnrollment->class_assessment_marks + $latestEnrollment->final_term_marks) < 60)) {
                $canImprove = true;
            } else {
                $canImprove = false;
            }
            return $canRetake || $canImprove;
        }
    }

    // This method is used by course teacher/admin to update marks for a single enrollment
    public function update(Request $request, Enrollment $enrollment): JsonResponse
    {
        $this->authorize('update', $enrollment);
        try {
            // Validate the incoming request data
            $validatedData = $request->validate([
                'class_assessment_marks' => 'required|integer|min:0|max:30',
                'final_term_marks' => 'required|integer|min:0|max:70',
            ]);

            // Update only the specified fields
            $enrollment->update($validatedData);

            return response()->json([
                'status' => 'success',
                'message' => 'Enrollment updated successfully.',
                'data' => $enrollment,
            ], Response::HTTP_OK);
        } catch (ValidationException $e) {
            // Handle validation exceptions
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            // Log the exception
            Log::error('Enrollment update failed: ' . $e->getMessage());

            // Handle other exceptions
            return response()->json([
                'status' => 'error',
                'message' => 'An unexpected error occurred.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // This method is used by course teacher to update marks for multiple enrollments
    public function updateMarks(Request $request): JsonResponse
    {
        $user = Auth::user();

        try {
            // Validate the request
            $validatedData = $request->validate([
                'courseSession_id' => 'required|integer|exists:course_sessions,id',
                'enrollments' => 'required|array',
                'enrollments.*.id' => 'required|integer|exists:enrollments,id',
                'enrollments.*.class_assessment_marks' => 'required|integer|min:0|max:30',
                'enrollments.*.final_term_marks' => 'required|integer|min:0|max:70',
            ]);

            foreach ($validatedData['enrollments'] as $data) {
                $enrollment = Enrollment::find($data['id']);

                if (!$enrollment) {
                    throw new EnrollmentNotFoundException('Enrollment not found.', 404);
                }

                // Check if the user is authorized to update this enrollment
                if (!$user->can('update', $enrollment)) {
                    throw new UnauthorizedAccessException('You do not have permission to update some enrollments.',403);
                }

                // Update the enrollment
                $enrollment->update([
                    'class_assessment_marks' => $data['class_assessment_marks'],
                    'final_term_marks' => $data['final_term_marks'],
                ]);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Enrollments updated successfully.',
            ], Response::HTTP_OK);

        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);

        } catch (\Exception $e) {
            Log::error('Enrollment update failed: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()?: 'Failed to update enrollments.',
            ], $e->getCode()?: Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function showForTeacher($courseSessionId): JsonResponse
    {
        // Retrieve the authenticated teacher's ID
        $teacher = Auth::user();
        $teacherId = $teacher->id;

        if(!$teacher->hasRole('teacher')){
            return response()->json([
                'status' => 'error',
                'message' => 'Only teachers can view enrollments.',
            ], Response::HTTP_FORBIDDEN);
        }

        // Fetch enrollments associated with the specified course_session_id
        // and ensure the course session belongs to the authenticated teacher
        $enrollments = Enrollment::with('student')  // Eager load the student data
        ->whereHas('courseSession', function ($query) use ($courseSessionId, $teacherId) {
            $query->where('id', $courseSessionId)
                ->where('teacher_id', $teacherId);
        })->get();

        if ($enrollments->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'There is no enrollment data or you are not authorized to view it.',
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'status' => 'success',
            'data' => $enrollments,
        ]);
    }

    public function showForStudent(): JsonResponse
    {
        $student = Auth::user();
        $studentId = $student->id;

        if (!$student->hasRole('student')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Only students can view enrollments.',
            ], Response::HTTP_FORBIDDEN);
        }
        // Get enrollments with highest (class_assessment_marks + final_term_marks) per course
        $enrollments = Enrollment::with(['courseSession.course'])
            ->selectRaw('*, (class_assessment_marks + final_term_marks) as total_marks')
            ->where('student_id', $studentId)
            ->orderByDesc('total_marks')
            ->get()
            ->makeHidden('final_term_marks');

        if ($enrollments->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'There is no enrollment data or you are not authorized to view it.',
            ], Response::HTTP_NOT_FOUND);
        }
        // Add canReEnroll property by calling canRetake for each enrollment
        $enrollments->transform(function ($enrollment) use ($studentId) {
            $courseId = $enrollment->courseSession->course->id;
            $courseSessionId = CourseSession::where('course_id', $courseId)
                ->max('id');
            $enrollment->canReEnroll = $this->canRetake($studentId, $courseSessionId);
            return $enrollment;
        });

        return response()->json([
            'status' => 'success',
            'data' => $enrollments,
        ]);
    }
}
