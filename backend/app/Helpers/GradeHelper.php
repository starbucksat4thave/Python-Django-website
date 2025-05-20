<?php

namespace App\Helpers;

class GradeHelper
{
    public static function getGrade($marks): array
    {
        $gradeMappings = [
            80 => ['grade' => 'A+', 'gpa' => 4.00, 'remark' => 'Outstanding'],
            75 => ['grade' => 'A',  'gpa' => 3.75, 'remark' => 'Excellent'],
            70 => ['grade' => 'A-', 'gpa' => 3.50, 'remark' => 'Very Good'],
            65 => ['grade' => 'B+', 'gpa' => 3.25, 'remark' => 'Good'],
            60 => ['grade' => 'B',  'gpa' => 3.00, 'remark' => 'Satisfactory'],
            55 => ['grade' => 'B-', 'gpa' => 2.75, 'remark' => 'Below Satisfactory'],
            50 => ['grade' => 'C+', 'gpa' => 2.50, 'remark' => 'Average'],
            45 => ['grade' => 'C',  'gpa' => 2.25, 'remark' => 'Pass'],
            40 => ['grade' => 'D',  'gpa' => 2.00, 'remark' => 'Poor'],
            0  => ['grade' => 'F',  'gpa' => 0.00, 'remark' => 'Fail'],
        ];

        foreach ($gradeMappings as $minMark => $gradeInfo) {
            if ($marks >= $minMark) {
                return $gradeInfo;
            }
        }

        // Should never reach here unless marks are negative
        return ['grade' => 'Invalid', 'gpa' => 0.00, 'remark' => 'Invalid Marks'];
    }
}
