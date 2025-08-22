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
use App\Services\Reports\ReportGeneratorFactory;
use App\Services\Reports\ReportGeneratorFactoryInterface;
use Illuminate\Support\ServiceProvider;

class ReportServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(StudentRepositoryInterface::class, JsonStudentRepository::class);
        $this->app->bind(AssessmentRepositoryInterface::class, JsonAssessmentRepository::class);
        $this->app->bind(QuestionRepositoryInterface::class, JsonQuestionRepository::class);
        $this->app->bind(ResponseRepositoryInterface::class, JsonResponseRepository::class);

        // Report factory
        $this->app->bind(ReportGeneratorFactoryInterface::class, function ($app) {
            return new ReportGeneratorFactory([
                'diagnostic' => $app->make(DiagnosticReportGenerator::class),
                'progress' => $app->make(ProgressReportGenerator::class),
                'feedback' => $app->make(FeedbackReportGenerator::class),
            ]);
        });
    }

    public function boot(): void {}
}
