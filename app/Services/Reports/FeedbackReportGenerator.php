<?php

namespace App\Services\Reports;

use App\Repositories\Contracts\AssessmentRepositoryInterface;
use App\Repositories\Contracts\QuestionRepositoryInterface;
use App\Repositories\Contracts\ResponseRepositoryInterface;
use App\Repositories\Contracts\StudentRepositoryInterface;
use Carbon\Carbon;

readonly class FeedbackReportGenerator implements ReportGeneratorInterface
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

        usort($completedResponses, function ($a, $b) {
            $dateA = Carbon::createFromFormat('d/m/Y H:i:s', $a['completed']);
            $dateB = Carbon::createFromFormat('d/m/Y H:i:s', $b['completed']);

            return $dateB->getTimestamp() <=> $dateA->getTimestamp();
        });

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

        $report .= sprintf(
            "He got %d questions right out of %d. Feedback for wrong answers given below\n\n",
            $correctAnswers,
            $totalQuestions
        );

        foreach ($this->getWrongAnswers($latestResponse) as $wrongAnswer) {
            $question = $this->questions->findById($wrongAnswer['questionId']);
            if (! $question) {
                continue;
            }

            $studentOption = $this->findOptionById($question, $wrongAnswer['response']);
            $correctOption = $this->findOptionById($question, $question['config']['key']);

            $report .= sprintf("Question: %s\n", $question['stem']);
            $report .= sprintf("Your answer: %s with value %s\n",
                $studentOption['label'] ?? 'N/A',
                $studentOption['value'] ?? 'N/A'
            );
            $report .= sprintf("Right answer: %s with value %s\n",
                $correctOption['label'],
                $correctOption['value']
            );
            $report .= sprintf("Hint: %s\n\n", $question['config']['hint']);
        }

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

    private function getWrongAnswers(array $response): array
    {
        return array_filter($response['responses'], function ($studentAnswer) {
            $question = $this->questions->findById($studentAnswer['questionId']);

            return $question && $studentAnswer['response'] !== $question['config']['key'];
        });
    }

    private function findOptionById(array $question, string $optionId): ?array
    {
        foreach ($question['config']['options'] as $option) {
            if ($option['id'] === $optionId) {
                return $option;
            }
        }

        return null;
    }
}
