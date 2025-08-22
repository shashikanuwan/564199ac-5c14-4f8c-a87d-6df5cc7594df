<?php

namespace App\Services\Reports\Formatters;

use App\ValueObjects\AssessmentResult;
use App\ValueObjects\StudentInfo;

class FeedbackReportFormatter implements ReportFormatterInterface
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
            "He got %s. Feedback for wrong answers given below\n\n",
            $assessment->getScore()
        );
    }

    public function formatWrongAnswers(array $wrongAnswers): string
    {
        $output = '';
        foreach ($wrongAnswers as $analysis) {
            $output .= sprintf("Question: %s\n", $analysis->stem);
            $output .= sprintf("Your answer: %s\n", $analysis->studentAnswer);
            $output .= sprintf("Right answer: %s\n", $analysis->correctAnswer);
            $output .= sprintf("Hint: %s\n\n", $analysis->hint);
        }

        return $output;
    }
}
