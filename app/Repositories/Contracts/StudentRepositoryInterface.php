<?php

namespace App\Repositories\Contracts;

interface StudentRepositoryInterface
{
    public function findById(string $id): ?array;
}
