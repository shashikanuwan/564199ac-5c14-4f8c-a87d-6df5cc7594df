<?php

namespace App\Repositories\Contracts;

interface ResponseRepositoryInterface
{
    public function getCompletedByStudent(string $studentId): array;
}
