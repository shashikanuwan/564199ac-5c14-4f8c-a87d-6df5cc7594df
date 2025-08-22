<?php

namespace App\Services\Reports\Data;

use App\Repositories\Contracts\AssessmentRepositoryInterface;
use App\Repositories\Contracts\QuestionRepositoryInterface;
use App\Repositories\Contracts\ResponseRepositoryInterface;
use App\ValueObjects\AssessmentResult;
use Carbon\Carbon;

class AssessmentDataService
{
    public function __construct(
        protected ResponseRepositoryInterface $responseRepository,
        protected AssessmentRepositoryInterface $assessmentRepository,
        protected QuestionRepositoryInterface $questionRepository
    ) {}

    public function getCompletedAssessments(string $studentId): array
    {
        $responses = $this->responseRepository->getCompletedByStudent($studentId);
        $assessmentResults = [];

        foreach ($responses as $response) {
            $assessment = $this->assessmentRepository->findById($response['assessmentId']);
            if (! $assessment) {
                continue;
            }

            $correctAnswers = $this->calculateCorrectAnswers($response);
            $completedDate = Carbon::createFromFormat('d/m/Y H:i:s', $response['completed']);

            $assessmentResults[] = new AssessmentResult(
                $response['assessmentId'],
                $assessment['name'],
                $completedDate,
                $correctAnswers,
                count($response['responses']),
                $response['responses']
            );
        }

        return $assessmentResults;
    }

    public function getLatestAssessment(string $studentId): ?AssessmentResult
    {
        $assessments = $this->getCompletedAssessments($studentId);
        if (empty($assessments)) {
            return null;
        }

        // Sort by date descending
        usort($assessments, fn ($a, $b) => $b->completedDate->getTimestamp() <=> $a->completedDate->getTimestamp());

        return $assessments[0];
    }

    private function calculateCorrectAnswers(array $response): int
    {
        $correct = 0;
        foreach ($response['responses'] as $studentAnswer) {
            $question = $this->questionRepository->findById($studentAnswer['questionId']);
            if ($question && $studentAnswer['response'] === $question['config']['key']) {
                $correct++;
            }
        }

        return $correct;
    }
}
