<?php

namespace App\Services\Reports\Data;

use App\Repositories\Contracts\StudentRepositoryInterface;
use App\ValueObjects\StudentInfo;

class StudentDataService
{
    public function __construct(
        protected StudentRepositoryInterface $studentRepository
    ) {}

    public function getStudentInfo(string $studentId): ?StudentInfo
    {
        $student = $this->studentRepository->findById($studentId);
        if (! $student) {
            return null;
        }

        return new StudentInfo(
            $studentId,
            $student['firstName'],
            $student['lastName']
        );
    }
}
