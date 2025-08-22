<?php

namespace App\Services\Reports;

use App\Repositories\Contracts\AssessmentRepositoryInterface;
use App\Repositories\Contracts\ResponseRepositoryInterface;
use App\Repositories\Contracts\StudentRepositoryInterface;
use Carbon\Carbon;

readonly class ProgressReportGenerator implements ReportGeneratorInterface
{
    public function __construct(
        private StudentRepositoryInterface $students,
        private AssessmentRepositoryInterface $assessments,
        private ResponseRepositoryInterface $responses
    ) {}

    public function generate(string $studentId): string
    {
        $student = $this->students->findById($studentId);
        if (! $student) {
            return 'Student not found.';
        }

        $completedResponses = $this->responses->getCompletedByStudent($studentId);
        if (count($completedResponses) < 1) {
            return 'No completed assessments found for this student.';
        }

        // sort ascending by date
        usort($completedResponses, fn ($a, $b) => strtotime($a['completed']) <=> strtotime($b['completed']));

        $assessment = $this->assessments->findById($completedResponses[0]['assessmentId']);
        $report = sprintf(
            "%s has completed %s assessment %d times in total. Date and raw score given below:\n\n",
            $student['firstName'].' '.$student['lastName'],
            $assessment['name'],
            count($completedResponses)
        );

        foreach ($completedResponses as $response) {
            $date = Carbon::createFromFormat('d/m/Y H:i:s', $response['completed']);
            $report .= sprintf(
                "Date: %s, Raw Score: %d out of %d\n",
                $date->format('jS F Y'),
                $response['results']['rawScore'],
                count($response['responses'])
            );
        }

        if (count($completedResponses) > 1) {
            $oldest = $completedResponses[0]['results']['rawScore'];
            $latest = $completedResponses[count($completedResponses) - 1]['results']['rawScore'];
            $improvement = $latest - $oldest;

            $report .= sprintf(
                "\n%s got %d more correct in the recent completed assessment than the oldest",
                $student['firstName'].' '.$student['lastName'],
                $improvement
            );
        }

        return $report;
    }
}
