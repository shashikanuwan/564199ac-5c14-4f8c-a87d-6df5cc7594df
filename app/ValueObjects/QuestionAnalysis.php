<?php

namespace App\ValueObjects;

readonly class QuestionAnalysis
{
    public function __construct(
        public string $questionId,
        public string $stem,
        public string $studentAnswer,
        public string $correctAnswer,
        public string $hint,
        public bool $isCorrect
    ) {}
}
