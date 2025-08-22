<?php

namespace App\ValueObjects;

readonly class StrandResult
{
    public function __construct(
        public string $strandName,
        public int $correctAnswers,
        public int $totalQuestions
    ) {}

    public function getPercentage(): float
    {
        return $this->totalQuestions > 0 ? ($this->correctAnswers / $this->totalQuestions) * 100 : 0;
    }
}
