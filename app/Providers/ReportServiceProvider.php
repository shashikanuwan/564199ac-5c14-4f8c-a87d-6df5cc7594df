<?php

namespace App\Providers;

use App\Repositories\Contracts\AssessmentRepositoryInterface;
use App\Repositories\Contracts\QuestionRepositoryInterface;
use App\Repositories\Contracts\ResponseRepositoryInterface;
use App\Repositories\Contracts\StudentRepositoryInterface;
use App\Repositories\Json\JsonAssessmentRepository;
use App\Repositories\Json\JsonQuestionRepository;
use App\Repositories\Json\JsonResponseRepository;
use App\Repositories\Json\JsonStudentRepository;
use App\Services\Reports\DiagnosticReportGenerator;
use App\Services\Reports\FeedbackReportGenerator;
use App\Services\Reports\ProgressReportGenerator;
use App\Services\ReportService;
use Illuminate\Support\ServiceProvider;

class ReportServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(StudentRepositoryInterface::class, JsonStudentRepository::class);
        $this->app->bind(AssessmentRepositoryInterface::class, JsonAssessmentRepository::class);
        $this->app->bind(QuestionRepositoryInterface::class, JsonQuestionRepository::class);
        $this->app->bind(ResponseRepositoryInterface::class, JsonResponseRepository::class);

        // Bind ReportService with generators
        $this->app->singleton(ReportService::class, function ($app) {
            return new ReportService([
                'diagnostic' => new DiagnosticReportGenerator(
                    $app->make(StudentRepositoryInterface::class),
                    $app->make(AssessmentRepositoryInterface::class),
                    $app->make(QuestionRepositoryInterface::class),
                    $app->make(ResponseRepositoryInterface::class),
                ),
                'progress' => new ProgressReportGenerator(
                    $app->make(StudentRepositoryInterface::class),
                    $app->make(AssessmentRepositoryInterface::class),
                    $app->make(ResponseRepositoryInterface::class),
                ),
                'feedback' => new FeedbackReportGenerator(
                    $app->make(StudentRepositoryInterface::class),
                    $app->make(AssessmentRepositoryInterface::class),
                    $app->make(QuestionRepositoryInterface::class),
                    $app->make(ResponseRepositoryInterface::class),
                ),
            ]);
        });
    }

    public function boot(): void {}
}
