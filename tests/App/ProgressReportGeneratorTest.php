<?php

use App\Services\Reports\Data\AssessmentDataService;
use App\Services\Reports\Data\StudentDataService;
use App\Services\Reports\Formatters\ProgressReportFormatter;
use App\Services\Reports\ProgressReportGenerator;
use App\ValueObjects\StudentInfo;
use Carbon\Carbon;

beforeEach(function () {
    $this->studentService = Mockery::mock(StudentDataService::class);
    $this->assessmentService = Mockery::mock(AssessmentDataService::class);
    $this->formatter = Mockery::mock(ProgressReportFormatter::class);

    $this->generator = new ProgressReportGenerator(
        $this->studentService,
        $this->assessmentService,
        $this->formatter
    );
});

it('returns student not found if student does not exist', function () {
    $this->studentService->shouldReceive('getStudentInfo')
        ->once()
        ->with('123')
        ->andReturn(null);

    $result = $this->generator->generate('123');

    expect($result)->toBe('Student not found.');
});

it('returns no assessments message if none found', function () {
    $this->studentService->shouldReceive('getStudentInfo')
        ->once()
        ->with('123')
        ->andReturn(new StudentInfo(
            '123',
            'Tony',
            'Stark'
        ));

    $this->assessmentService->shouldReceive('getCompletedAssessments')
        ->once()
        ->with('123')
        ->andReturn([]);

    $result = $this->generator->generate('123');

    expect($result)->toBe('No completed assessments found for this student.');
});

it('generates report for single assessment', function () {
    $student = new StudentInfo(
        '123',
        'Tony',
        'Stark'
    );
    $assessment = (object) [
        'assessmentName' => 'Math Test',
        'completedDate' => Carbon::parse('2023-01-01'),
        'correctAnswers' => 5,
    ];

    $this->studentService->shouldReceive('getStudentInfo')->andReturn($student);
    $this->assessmentService->shouldReceive('getCompletedAssessments')->andReturn([$assessment]);

    $this->formatter->shouldReceive('formatProgressHeader')
        ->once()
        ->with($student, 'Math Test', 1)
        ->andReturn("Header\n");

    $this->formatter->shouldReceive('formatAssessmentHistory')
        ->once()
        ->with([$assessment])
        ->andReturn("History\n");

    $result = $this->generator->generate('123');

    expect($result)->toBe("Header\nHistory\n");
});

it('generates report with improvement for multiple assessments', function () {
    $student = new StudentInfo(
        '123',
        'Tony',
        'Stark'
    );
    $assessments = [
        (object) [
            'assessmentName' => 'Math Test 1',
            'completedDate' => Carbon::parse('2023-01-01'),
            'correctAnswers' => 5,
        ],
        (object) [
            'assessmentName' => 'Math Test 2',
            'completedDate' => Carbon::parse('2023-02-01'),
            'correctAnswers' => 8,
        ],
    ];

    $this->studentService->shouldReceive('getStudentInfo')->andReturn($student);
    $this->assessmentService->shouldReceive('getCompletedAssessments')->andReturn($assessments);

    $this->formatter->shouldReceive('formatProgressHeader')
        ->once()
        ->with($student, 'Math Test 1', 2)
        ->andReturn("Header\n");

    $this->formatter->shouldReceive('formatAssessmentHistory')
        ->once()
        ->with($assessments)
        ->andReturn("History\n");

    $this->formatter->shouldReceive('formatImprovement')
        ->once()
        ->with($student, 3) // 8 - 5
        ->andReturn("Improved by 3\n");

    $result = $this->generator->generate('123');

    expect($result)->toBe("Header\nHistory\nImproved by 3\n");
});
