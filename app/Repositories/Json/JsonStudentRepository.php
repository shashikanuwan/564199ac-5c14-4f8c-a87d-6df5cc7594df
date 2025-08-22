<?php

namespace App\Repositories\Json;

use App\Repositories\Contracts\StudentRepositoryInterface;

class JsonStudentRepository implements StudentRepositoryInterface
{
    private array $students;

    public function __construct()
    {
        $this->students = json_decode(file_get_contents(base_path('data/students.json')), true);
    }

    public function findById(string $id): ?array
    {
        return collect($this->students)->firstWhere('id', $id);
    }
}
