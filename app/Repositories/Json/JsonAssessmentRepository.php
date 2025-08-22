<?php

namespace App\Repositories\Json;

use App\Repositories\Contracts\AssessmentRepositoryInterface;

class JsonAssessmentRepository implements AssessmentRepositoryInterface
{
    private array $assessments;

    public function __construct()
    {
        $this->assessments = json_decode(file_get_contents(base_path('data/assessments.json')), true);
    }

    public function findById(string $id): ?array
    {
        return collect($this->assessments)->firstWhere('id', $id);
    }
}
