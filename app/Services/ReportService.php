<?php

namespace App\Services;

use InvalidArgumentException;

class ReportService
{
    public function __construct(protected array $generators = []) {}

    public function generate(string $type, string $studentId): string
    {
        if (! isset($this->generators[$type])) {
            $supported = implode(', ', array_keys($this->generators));
            throw new InvalidArgumentException(
                "Report type [$type] not supported. Supported types: [$supported]"
            );
        }

        return $this->generators[$type]->generate($studentId);
    }
}
