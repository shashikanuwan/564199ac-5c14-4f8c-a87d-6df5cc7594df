<?php

namespace App\Repositories\Contracts;

interface AssessmentRepositoryInterface
{
    public function findById(string $id): ?array;
}
