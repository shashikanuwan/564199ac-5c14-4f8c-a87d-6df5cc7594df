<?php

namespace App\Services\Reports\Formatters;

use App\ValueObjects\AssessmentResult;
use App\ValueObjects\StudentInfo;

class DiagnosticReportFormatter implements ReportFormatterInterface
{
    public function formatHeader(StudentInfo $student, AssessmentResult $assessment): string
    {
        return sprintf(
            "%s recently completed %s assessment on %s\n",
            $student->getFullName(),
            $assessment->assessmentName,
            $assessment->completedDate->format('jS F Y g:i A')
        );
    }

    public function formatSummary(AssessmentResult $assessment): string
    {
        return sprintf(
            "He got %s. Details by strand given below:\n\n",
            $assessment->getScore()
        );
    }

    public function formatStrandResults(array $strandResults): string
    {
        $output = '';
        foreach ($strandResults as $result) {
            $output .= sprintf(
                "%s: %d out of %d correct\n",
                $result->strandName,
                $result->correctAnswers,
                $result->totalQuestions
            );
        }

        return $output;
    }
}
