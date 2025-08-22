<?php

namespace App\Services\Reports;

use App\Services\Reports\Analysis\QuestionAnalysisService;
use App\Services\Reports\Data\AssessmentDataService;
use App\Services\Reports\Data\StudentDataService;
use App\Services\Reports\Formatters\DiagnosticReportFormatter;

readonly class DiagnosticReportGenerator implements ReportGeneratorInterface
{
    public function __construct(
        private StudentDataService $studentService,
        private AssessmentDataService $assessmentService,
        private QuestionAnalysisService $analysisService,
        private DiagnosticReportFormatter $formatter
    ) {}

    public function generate(string $studentId): string
    {
        $student = $this->studentService->getStudentInfo($studentId);
        if (! $student) {
            return 'Student not found.';
        }

        $latestAssessment = $this->assessmentService->getLatestAssessment($studentId);
        if (! $latestAssessment) {
            return 'No completed assessments found for this student.';
        }

        $strandResults = $this->analysisService->analyzeByStrands($latestAssessment->responses);

        return $this->formatter->formatHeader($student, $latestAssessment).
            $this->formatter->formatSummary($latestAssessment).
            $this->formatter->formatStrandResults($strandResults);
    }
}
