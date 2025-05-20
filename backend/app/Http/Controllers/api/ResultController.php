<?php

namespace App\Http\Controllers\api;

use App\Models\Enrollment;
use App\Helpers\GradeHelper;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ResultController extends Controller
{
    public function showResult($courseId)
    {
        $user = Auth::user();
        try {
            // Subquery to get only the highest-scoring enrollment per course session
            $enrollment = Enrollment::with(['courseSession.course'])
                ->where('student_id', $user->id)
                ->whereHas('courseSession', function ($query) use ($courseId) {
                    $query->where('course_id', $courseId);
                })
                ->orderByDesc(\DB::raw('(class_assessment_marks + final_term_marks)'))
                ->first(); // ✅ Fetch only the single best enrollment

            $maxMarks = $enrollment->final_term_marks + $enrollment->class_assessment_marks;

            $gradeDetails = GradeHelper::getGrade($maxMarks);

            return response()->json([
                'course_id' => $courseId,
                'max_final_term_marks' => $maxMarks,
                'grade' => $gradeDetails['grade'],
                'gpa' => $gradeDetails['gpa'],
                'remark' => $gradeDetails['remark'],
                'user_id' => $user->id,
                'user_name' => $user->name,
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred while fetching results'], 500);
        }
    }


    public function showFullResult($year, $semester)
    {
        $user = Auth::id();

        try {
            // ✅ Step 1: Get all courses for the given semester
            $allCourses = Course::where('year', $year)
                ->where('semester', $semester)
                ->get();

            // ✅ Step 2: Get all enrollments of the student in this semester
            $enrollments = Enrollment::where('student_id', $user)
                ->whereHas('courseSession.course', function ($query) use ($year, $semester) {
                    $query->where('year', $year)
                        ->where('semester', $semester);
                })
                ->with(['courseSession.course'])
                ->get();

            // ✅ Step 3: Get the best enrollment for each course (if multiple attempts)
            $bestResults = $enrollments
                ->groupBy('courseSession.course.id') // Group by course ID
                ->map(function ($enrollmentGroup) {
                    // Select the enrollment with the highest (CA + Final) marks
                    return $enrollmentGroup->sortByDesc(fn($e) => $e->class_assessment_marks + $e->final_term_marks)
                        ->first();
                });

            $totalWeightedGPA = 0;
            $totalCreditHours = 0;
            $response = [];

            // ✅ Step 4: Process each course in the semester
            foreach ($allCourses as $course) {
                // Check if the student has an enrollment for this course
                $enrollment = $bestResults->get($course->id);

                if ($enrollment) {
                    // ✅ Student is enrolled: Calculate based on best attempt
                    $totalMarks = $enrollment->class_assessment_marks + $enrollment->final_term_marks;
                    $gradeDetails = GradeHelper::getGrade($totalMarks);
                    $gpa = $gradeDetails['gpa'];
                } else {
                    // ❌ Student is NOT enrolled: GPA = 0
                    $totalMarks = null;
                    $gpa = 0;
                    $gradeDetails = [
                        'grade' => 'F',
                        'gpa' => 0,
                        'remark' => 'Not Enrolled'
                    ];
                }

                // ✅ Compute weighted GPA = (GPA × Credit Hours)
                $weightedGPA = $gpa * $course->credit;

                // ✅ Update total weighted GPA and total credit hours
                $totalWeightedGPA += $weightedGPA;
                $totalCreditHours += $course->credit;

                // ✅ Append to response
                $response[] = [
                    'department' => $course->department->name,
                    'session' => User::find($user)->session,
                    'course_id' => $course->id,
                    'course_name' => $course->name,
                    'year' => $year,
                    'semester' => $semester,
                    'total_marks' => $totalMarks,
                    'grade' => $gradeDetails['grade'],
                    'gpa' => $gradeDetails['gpa'],
                    'remark' => $gradeDetails['remark'],
                    'credit_hours' => $course->credit,
                ];
            }

            // ✅ Step 5: Calculate CGPA including all courses
            $cgpa = $totalCreditHours > 0 ? $totalWeightedGPA / $totalCreditHours : 0;

            return response()->json([
                'courses' => $response,
                'total_cgpa' => round($cgpa, 2),
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred while fetching results'], 500);
        }
    }

}
