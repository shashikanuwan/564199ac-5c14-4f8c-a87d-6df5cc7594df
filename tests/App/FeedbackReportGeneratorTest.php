<?php

use App\Services\Reports\Analysis\QuestionAnalysisService;
use App\Services\Reports\Data\AssessmentDataService;
use App\Services\Reports\Data\StudentDataService;
use App\Services\Reports\FeedbackReportGenerator;
use App\Services\Reports\Formatters\FeedbackReportFormatter;
use App\ValueObjects\StudentInfo;

beforeEach(function () {
    $this->studentService = Mockery::mock(StudentDataService::class);
    $this->assessmentService = Mockery::mock(AssessmentDataService::class);
    $this->analysisService = Mockery::mock(QuestionAnalysisService::class);
    $this->formatter = Mockery::mock(FeedbackReportFormatter::class);

    $this->generator = new FeedbackReportGenerator(
        $this->studentService,
        $this->assessmentService,
        $this->analysisService,
        $this->formatter
    );
});

afterEach(function () {
    Mockery::close();
});

it('returns student not found message when student does not exist', function () {
    $studentId = 'non-existent-student';

    $this->studentService
        ->shouldReceive('getStudentInfo')
        ->once()
        ->with($studentId)
        ->andReturn(null);

    $result = $this->generator->generate($studentId);

    expect($result)->toBe('Student not found.');
});

it('returns no assessments message when student has no completed assessments', function () {
    $studentId = 'student-123';
    $student = new StudentInfo(
        $studentId,
        'John Doe',
        'Doe'
    );

    $this->studentService
        ->shouldReceive('getStudentInfo')
        ->once()
        ->with($studentId)
        ->andReturn($student);

    $this->assessmentService
        ->shouldReceive('getLatestAssessment')
        ->once()
        ->with($studentId)
        ->andReturn(null);

    $result = $this->generator->generate($studentId);

    expect($result)->toBe('No completed assessments found for this student.');
});
