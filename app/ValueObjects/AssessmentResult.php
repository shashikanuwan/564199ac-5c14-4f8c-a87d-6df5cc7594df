<?php

namespace App\ValueObjects;

use Carbon\Carbon;

readonly class AssessmentResult
{
    public function __construct(
        public string $assessmentId,
        public string $assessmentName,
        public Carbon $completedDate,
        public int $correctAnswers,
        public int $totalQuestions,
        public array $responses
    ) {}

    public function getScore(): string
    {
        return sprintf('%d out of %d', $this->correctAnswers, $this->totalQuestions);
    }

    public function getScorePercentage(): float
    {
        return $this->totalQuestions > 0 ? ($this->correctAnswers / $this->totalQuestions) * 100 : 0;
    }
}
