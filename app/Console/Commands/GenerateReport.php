<?php

namespace App\Console\Commands;

use App\Services\ReportService;
use Illuminate\Console\Command;

use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

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
        $this->newLine();
        $this->info('==========================================');
        $this->info('         Assessment Report Generator       ');
        $this->info('==========================================');
        $this->newLine();

        $studentId = text(
            label: 'Enter Student ID',
            required: 'Student ID is required.'
        );

        $reportType = select(
            label: 'Select report type',
            options: [
                'diagnostic' => '📊 Diagnostic',
                'progress' => '📈 Progress',
                'feedback' => '💡 Feedback',
            ],
            default: 'diagnostic'
        );

        $this->newLine();
        $this->info('Generating report...');
        $this->newLine();

        try {
            $report = match ($reportType) {
                'diagnostic' => $this->reportService->generateDiagnosticReport($studentId),
                'progress' => $this->reportService->generateProgressReport($studentId),
                'feedback' => $this->reportService->generateFeedbackReport($studentId),
                default => null,
            };

            if (! $report || str_contains($report, 'not found')) {
                $this->error($report ?? 'Unable to generate report.');

                return self::FAILURE;
            }

            $this->line($report);
            $this->newLine();
            $this->info('✔ Report generated successfully.');

        } catch (\Exception $e) {
            $this->error('Error generating report: '.$e->getMessage());

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
