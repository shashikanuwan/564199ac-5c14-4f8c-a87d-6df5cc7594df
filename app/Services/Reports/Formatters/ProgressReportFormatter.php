<?php

namespace App\Services\Reports\Formatters;

use App\ValueObjects\AssessmentResult;
use App\ValueObjects\StudentInfo;

class ProgressReportFormatter implements ReportFormatterInterface
{
    public function formatHeader(StudentInfo $student, AssessmentResult $assessment): string
    {
        return ''; // Progress reports don't use standard headers
    }

    public function formatSummary(AssessmentResult $assessment): string
    {
        return ''; // Progress reports don't use standard summaries
    }

    public function formatProgressHeader(StudentInfo $student, string $assessmentName, int $completionCount): string
    {
        return sprintf(
            "%s has completed %s assessment %d times in total. Date and raw score given below:\n\n",
            $student->getFullName(),
            $assessmentName,
            $completionCount
        );
    }

    public function formatAssessmentHistory(array $assessments): string
    {
        $output = '';
        foreach ($assessments as $assessment) {
            $output .= sprintf(
                "Date: %s, Raw Score: %s\n",
                $assessment->completedDate->format('jS F Y'),
                $assessment->getScore()
            );
        }

        return $output;
    }

    public function formatImprovement(StudentInfo $student, int $improvement): string
    {
        return sprintf(
            "\n%s got %d more correct in the recent completed assessment than the oldest",
            $student->getFullName(),
            $improvement
        );
    }
}
