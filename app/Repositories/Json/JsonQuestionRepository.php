<?php

namespace App\Repositories\Json;

use App\Repositories\Contracts\QuestionRepositoryInterface;

class JsonQuestionRepository implements QuestionRepositoryInterface
{
    private array $questions;

    public function __construct()
    {
        $this->questions = json_decode(file_get_contents(base_path('data/questions.json')), true);
    }

    public function findById(string $id): ?array
    {
        return collect($this->questions)->firstWhere('id', $id);
    }
}
