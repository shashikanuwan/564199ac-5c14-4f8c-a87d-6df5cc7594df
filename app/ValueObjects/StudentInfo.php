<?php

namespace App\ValueObjects;

readonly class StudentInfo
{
    public function __construct(
        public string $id,
        public string $firstName,
        public string $lastName
    ) {}

    public function getFullName(): string
    {
        return $this->firstName.' '.$this->lastName;
    }
}
