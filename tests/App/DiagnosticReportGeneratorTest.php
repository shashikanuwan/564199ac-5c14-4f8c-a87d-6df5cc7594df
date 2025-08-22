<?php

use App\Services\Reports\Analysis\QuestionAnalysisService;
use App\Services\Reports\Data\AssessmentDataService;
use App\Services\Reports\Data\StudentDataService;
use App\Services\Reports\DiagnosticReportGenerator;
use App\Services\Reports\Formatters\DiagnosticReportFormatter;

uses()->group('reports');

beforeEach(function () {
    $this->studentService = Mockery::mock(StudentDataService::class);
    $this->assessmentService = Mockery::mock(AssessmentDataService::class);
    $this->analysisService = Mockery::mock(QuestionAnalysisService::class);
    $this->formatter = Mockery::mock(DiagnosticReportFormatter::class);

    $this->reportGenerator = resolve(DiagnosticReportGenerator::class);
});

it('returns "Student not found." if student does not exist', function () {
    $this->studentService->shouldReceive('getStudentInfo')
        ->with('123')
        ->andReturnNull();

    $result = $this->reportGenerator->generate('123');

    expect($result)->toBe('Student not found.');
});
