<?php

namespace App\Repositories\Json;

use App\Repositories\Contracts\ResponseRepositoryInterface;

class JsonResponseRepository implements ResponseRepositoryInterface
{
    private array $responses;

    public function __construct()
    {
        $this->responses = json_decode(file_get_contents(base_path('data/student-responses.json')), true);
    }

    public function getCompletedByStudent(string $studentId): array
    {
        return collect($this->responses)
            ->where('student.id', $studentId)
            ->whereNotNull('completed')
            ->values()
            ->toArray();
    }
}
