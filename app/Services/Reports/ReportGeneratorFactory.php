<?php

namespace App\Services\Reports;

readonly class ReportGeneratorFactory implements ReportGeneratorFactoryInterface
{
    public function __construct(
        private array $generators
    ) {}

    public function create(string $type): ReportGeneratorInterface
    {
        if (! isset($this->generators[$type])) {
            throw new \InvalidArgumentException("Unsupported report type: {$type}");
        }

        return $this->generators[$type];
    }

    public function getSupportedTypes(): array
    {
        return array_keys($this->generators);
    }
}
