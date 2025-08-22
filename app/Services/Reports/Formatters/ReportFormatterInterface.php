<?php

namespace App\Services\Reports\Formatters;

use App\ValueObjects\AssessmentResult;
use App\ValueObjects\StudentInfo;

interface ReportFormatterInterface
{
    public function formatHeader(StudentInfo $student, AssessmentResult $assessment): string;

    public function formatSummary(AssessmentResult $assessment): string;
}
