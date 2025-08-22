<?php

namespace App\Services\Reports;

use App\Services\Reports\Data\AssessmentDataService;
use App\Services\Reports\Data\StudentDataService;
use App\Services\Reports\Formatters\ProgressReportFormatter;

readonly class ProgressReportGenerator implements ReportGeneratorInterface
{
    public function __construct(
        private StudentDataService $studentService,
        private AssessmentDataService $assessmentService,
        private ProgressReportFormatter $formatter
    ) {}

    public function generate(string $studentId): string
    {
        $student = $this->studentService->getStudentInfo($studentId);
        if (! $student) {
            return 'Student not found.';
        }

        $assessments = $this->assessmentService->getCompletedAssessments($studentId);
        if (empty($assessments)) {
            return 'No completed assessments found for this student.';
        }

        // Sort by date ascending for progress view
        usort($assessments, fn ($a, $b) => $a->completedDate->getTimestamp() <=> $b->completedDate->getTimestamp());

        $report = $this->formatter->formatProgressHeader($student, $assessments[0]->assessmentName, count($assessments));
        $report .= $this->formatter->formatAssessmentHistory($assessments);

        if (count($assessments) > 1) {
            $improvement = $assessments[count($assessments) - 1]->correctAnswers - $assessments[0]->correctAnswers;
            $report .= $this->formatter->formatImprovement($student, $improvement);
        }

        return $report;
    }
}
