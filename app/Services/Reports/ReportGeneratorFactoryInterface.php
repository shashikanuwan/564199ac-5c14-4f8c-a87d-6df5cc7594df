<?php

namespace App\Services\Reports;

interface ReportGeneratorFactoryInterface
{
    public function create(string $type): ReportGeneratorInterface;

    public function getSupportedTypes(): array;
}
