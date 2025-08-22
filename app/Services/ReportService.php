<?php

namespace App\Services;

use App\Services\Reports\ReportGeneratorFactoryInterface;
use InvalidArgumentException;

readonly class ReportService
{
    public function __construct(
        private ReportGeneratorFactoryInterface $generatorFactory
    ) {}

    public function generate(string $type, string $studentId): string
    {
        try {
            $generator = $this->generatorFactory->create($type);

            return $generator->generate($studentId);
        } catch (InvalidArgumentException $e) {
            $supported = implode(', ', $this->getSupportedReportTypes());
            throw new InvalidArgumentException(
                "Report type [$type] not supported. Supported types: [$supported]"
            );
        }
    }

    public function getSupportedReportTypes(): array
    {
        return $this->generatorFactory->getSupportedTypes();
    }
}
