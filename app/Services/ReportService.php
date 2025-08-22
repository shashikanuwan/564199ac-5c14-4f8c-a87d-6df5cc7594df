<?php

namespace App\Services;

use AllowDynamicProperties;

#[AllowDynamicProperties]
class ReportService
{
    public function __construct(array $generators = [])
    {
        $this->generators = $generators;
    }

    public function generate(string $type, string $studentId): string
    {
        if (! isset($this->generators[$type])) {
            throw new \InvalidArgumentException("Report type [$type] not supported");
        }

        return $this->generators[$type]->generate($studentId);
    }
}
