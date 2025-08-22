<?php

namespace App\Repositories\Contracts;

interface QuestionRepositoryInterface
{
    public function findById(string $id): ?array;
}
