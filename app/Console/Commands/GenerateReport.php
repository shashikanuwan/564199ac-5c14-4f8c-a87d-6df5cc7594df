<?php

namespace App\Console\Commands;

use App\Services\ReportService;
use Illuminate\Console\Command;

class GenerateReport extends Command
{
    protected $signature = 'generate-report';

    protected $description = 'Generate assessment reports for students';

    private ReportService $reportService;

    public function __construct(ReportService $reportService)
    {
        parent::__construct();
        $this->reportService = $reportService;
    }

    public function handle(): int
    {
        $this->info('Assessment Report Generator');
        $this->info('=============================');

        $this->line('Please enter the following:');

        $studentId = $this->ask('Student ID');

        $reportType = $this->choice(
            'Report to generate',
            [
                'Diagnostic',
                'Progress',
                'Feedback',
            ],
            0
        );

        $this->line('');
        $this->info('Generating report...');
        $this->line('');

        try {
            $report = match ($reportType) {
                'Diagnostic' => $this->reportService->generateDiagnosticReport($studentId),
                'Progress' => $this->reportService->generateProgressReport($studentId),
                'Feedback' => $this->reportService->generateFeedbackReport($studentId),
                default => 'Invalid report type selected.'
            };

            $this->line($report);

        } catch (\Exception $e) {
            $this->error('Error generating report: '.$e->getMessage());

            return 1;
        }

        return 0;
    }
}
