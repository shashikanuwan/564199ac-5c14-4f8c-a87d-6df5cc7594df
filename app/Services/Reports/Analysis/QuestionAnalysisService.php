<?php

namespace App\Services\Reports\Analysis;

use App\Repositories\Contracts\QuestionRepositoryInterface;
use App\ValueObjects\QuestionAnalysis;
use App\ValueObjects\StrandResult;

readonly class QuestionAnalysisService
{
    public function __construct(
        private QuestionRepositoryInterface $questionRepository
    ) {}

    public function analyzeWrongAnswers(array $responses): array
    {
        $wrongAnswers = [];

        foreach ($responses as $response) {
            $question = $this->questionRepository->findById($response['questionId']);
            if (! $question || $response['response'] === $question['config']['key']) {
                continue;
            }

            $studentOption = $this->findOptionById($question, $response['response']);
            $correctOption = $this->findOptionById($question, $question['config']['key']);

            $wrongAnswers[] = new QuestionAnalysis(
                $response['questionId'],
                $question['stem'],
                ($studentOption['label'] ?? 'N/A').' with value '.($studentOption['value'] ?? 'N/A'),
                $correctOption['label'].' with value '.$correctOption['value'],
                $question['config']['hint'],
                false
            );
        }

        return $wrongAnswers;
    }

    public function analyzeByStrands(array $responses): array
    {
        $strandData = [];

        foreach ($responses as $response) {
            $question = $this->questionRepository->findById($response['questionId']);
            if (! $question) {
                continue;
            }

            $strand = $question['strand'];
            if (! isset($strandData[$strand])) {
                $strandData[$strand] = ['correct' => 0, 'total' => 0];
            }

            $strandData[$strand]['total']++;
            if ($response['response'] === $question['config']['key']) {
                $strandData[$strand]['correct']++;
            }
        }

        $results = [];
        foreach ($strandData as $strand => $data) {
            $results[] = new StrandResult($strand, $data['correct'], $data['total']);
        }

        return $results;
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
