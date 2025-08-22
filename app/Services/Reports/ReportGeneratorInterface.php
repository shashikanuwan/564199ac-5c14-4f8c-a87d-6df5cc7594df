<?php

namespace App\Services\Reports;

interface ReportGeneratorInterface
{
    public function generate(string $studentId): string;
}
