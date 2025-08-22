<?php

namespace App\Services\Reports;

use App\Repositories\Contracts\AssessmentRepositoryInterface;
use App\Repositories\Contracts\QuestionRepositoryInterface;
use App\Repositories\Contracts\ResponseRepositoryInterface;
use App\Repositories\Contracts\StudentRepositoryInterface;
use Carbon\Carbon;

readonly class DiagnosticReportGenerator implements ReportGeneratorInterface
{
    public function __construct(
        private StudentRepositoryInterface $students,
        private AssessmentRepositoryInterface $assessments,
        private QuestionRepositoryInterface $questions,
        private ResponseRepositoryInterface $responses
    ) {}

    public function generate(string $studentId): string
    {
        $student = $this->students->findById($studentId);
        if (! $student) {
            return 'Student not found.';
        }

        $completedResponses = $this->responses->getCompletedByStudent($studentId);
        if (empty($completedResponses)) {
            return 'No completed assessments found for this student.';
        }

        usort($completedResponses, fn ($a, $b) => strtotime($b['completed']) <=> strtotime($a['completed']));
        $latestResponse = $completedResponses[0];

        $assessment = $this->assessments->findById($latestResponse['assessmentId']);
        $completedDate = Carbon::createFromFormat('d/m/Y H:i:s', $latestResponse['completed']);

        $report = sprintf(
            "%s recently completed %s assessment on %s\n",
            $student['firstName'].' '.$student['lastName'],
            $assessment['name'],
            $completedDate->format('jS F Y g:i A')
        );

        $totalQuestions = count($latestResponse['responses']);
        $correctAnswers = $this->calculateCorrectAnswers($latestResponse);

        $report .= sprintf("He got %d questions right out of %d.\n", $correctAnswers, $totalQuestions);

        return $report;
    }

    private function calculateCorrectAnswers(array $response): int
    {
        $correct = 0;
        foreach ($response['responses'] as $studentAnswer) {
            $question = $this->questions->findById($studentAnswer['questionId']);
            if ($question && $studentAnswer['response'] === $question['config']['key']) {
                $correct++;
            }
        }

        return $correct;
    }
}
