<?php

namespace App\Services;

use Carbon\Carbon;

class ReportService1
{
    private array $students;

    private array $assessments;

    private array $questions;

    private array $responses;

    public function __construct()
    {
        $this->loadData();
    }

    private function loadData(): void
    {
        $this->students = json_decode(file_get_contents(base_path('data/students.json')), true);
        $this->assessments = json_decode(file_get_contents(base_path('data/assessments.json')), true);
        $this->questions = json_decode(file_get_contents(base_path('data/questions.json')), true);
        $this->responses = json_decode(file_get_contents(base_path('data/student-responses.json')), true);
    }

    public function generateDiagnosticReport(string $studentId): string
    {
        $student = $this->findStudent($studentId);
        if (! $student) {
            return 'Student not found.';
        }

        $latestResponse = $this->getLatestCompletedResponse($studentId);
        if (! $latestResponse) {
            return 'No completed assessments found for this student.';
        }

        $assessment = $this->findAssessment($latestResponse['assessmentId']);
        $completedDate = Carbon::createFromFormat('d/m/Y H:i:s', $latestResponse['completed']);

        $report = sprintf(
            "%s recently completed %s assessment on %s\n",
            $student['firstName'].' '.$student['lastName'],
            $assessment['name'],
            $completedDate->format('jS F Y g:i A')
        );

        $totalQuestions = count($latestResponse['responses']);
        $correctAnswers = $this->calculateCorrectAnswers($latestResponse);

        $report .= sprintf("He got %d questions right out of %d. Details by strand given below:\n\n",
            $correctAnswers, $totalQuestions);

        $strandResults = $this->calculateStrandResults($latestResponse);
        foreach ($strandResults as $strand => $result) {
            $report .= sprintf("%s: %d out of %d correct\n", $strand, $result['correct'], $result['total']);
        }

        return $report;
    }

    public function generateProgressReport(string $studentId): string
    {
        $student = $this->findStudent($studentId);
        if (! $student) {
            return 'Student not found.';
        }

        $completedResponses = $this->getAllCompletedResponses($studentId);
        if (count($completedResponses) < 1) {
            return 'No completed assessments found for this student.';
        }

        // Sort by completion date
        usort($completedResponses, function ($a, $b) {
            return strtotime($a['completed']) - strtotime($b['completed']);
        });

        $assessment = $this->findAssessment($completedResponses[0]['assessmentId']);
        $report = sprintf(
            "%s has completed %s assessment %d times in total. Date and raw score given below:\n\n",
            $student['firstName'].' '.$student['lastName'],
            $assessment['name'],
            count($completedResponses)
        );

        foreach ($completedResponses as $response) {
            $date = Carbon::createFromFormat('d/m/Y H:i:s', $response['completed']);
            $report .= sprintf("Date: %s, Raw Score: %d out of %d\n",
                $date->format('jS F Y'),
                $response['results']['rawScore'],
                count($response['responses'])
            );
        }

        if (count($completedResponses) > 1) {
            $improvement = $completedResponses[count($completedResponses) - 1]['results']['rawScore'] -
                $completedResponses[0]['results']['rawScore'];

            $report .= sprintf("\n%s got %d more correct in the recent completed assessment than the oldest",
                $student['firstName'].' '.$student['lastName'],
                $improvement
            );
        }

        return $report;
    }

    public function generateFeedbackReport(string $studentId): string
    {
        $student = $this->findStudent($studentId);
        if (! $student) {
            return 'Student not found.';
        }

        $latestResponse = $this->getLatestCompletedResponse($studentId);
        if (! $latestResponse) {
            return 'No completed assessments found for this student.';
        }

        $assessment = $this->findAssessment($latestResponse['assessmentId']);
        $completedDate = Carbon::createFromFormat('d/m/Y H:i:s', $latestResponse['completed']);

        $report = sprintf(
            "%s recently completed %s assessment on %s\n",
            $student['firstName'].' '.$student['lastName'],
            $assessment['name'],
            $completedDate->format('jS F Y g:i A')
        );

        $totalQuestions = count($latestResponse['responses']);
        $correctAnswers = $this->calculateCorrectAnswers($latestResponse);

        $report .= sprintf("He got %d questions right out of %d. Feedback for wrong answers given below\n\n",
            $correctAnswers, $totalQuestions);

        $wrongAnswers = $this->getWrongAnswers($latestResponse);
        foreach ($wrongAnswers as $wrongAnswer) {
            $question = $this->findQuestion($wrongAnswer['questionId']);
            $studentOption = $this->findOptionById($question, $wrongAnswer['response']);
            $correctOption = $this->findOptionById($question, $question['config']['key']);

            $report .= sprintf("Question: %s\n", $question['stem']);
            $report .= sprintf("Your answer: %s with value %s\n",
                $studentOption['label'], $studentOption['value']);
            $report .= sprintf("Right answer: %s with value %s\n",
                $correctOption['label'], $correctOption['value']);
            $report .= sprintf("Hint: %s\n\n", $question['config']['hint']);
        }

        return $report;
    }

    private function findStudent(string $studentId): ?array
    {
        foreach ($this->students as $student) {
            if ($student['id'] === $studentId) {
                return $student;
            }
        }

        return null;
    }

    private function findAssessment(string $assessmentId): ?array
    {
        foreach ($this->assessments as $assessment) {
            if ($assessment['id'] === $assessmentId) {
                return $assessment;
            }
        }

        return null;
    }

    private function findQuestion(string $questionId): ?array
    {
        foreach ($this->questions as $question) {
            if ($question['id'] === $questionId) {
                return $question;
            }
        }

        return null;
    }

    private function getLatestCompletedResponse(string $studentId): ?array
    {
        $completedResponses = $this->getAllCompletedResponses($studentId);
        if (empty($completedResponses)) {
            return null;
        }

        // Sort by completion date descending
        usort($completedResponses, function ($a, $b) {
            return strtotime($b['completed']) - strtotime($a['completed']);
        });

        return $completedResponses[0];
    }

    private function getAllCompletedResponses(string $studentId): array
    {
        $completedResponses = [];
        foreach ($this->responses as $response) {
            if ($response['student']['id'] === $studentId && ! empty($response['completed'])) {
                $completedResponses[] = $response;
            }
        }

        return $completedResponses;
    }

    private function calculateCorrectAnswers(array $response): int
    {
        $correct = 0;
        foreach ($response['responses'] as $studentAnswer) {
            $question = $this->findQuestion($studentAnswer['questionId']);
            if ($question && $studentAnswer['response'] === $question['config']['key']) {
                $correct++;
            }
        }

        return $correct;
    }

    private function calculateStrandResults(array $response): array
    {
        $strandResults = [];

        foreach ($response['responses'] as $studentAnswer) {
            $question = $this->findQuestion($studentAnswer['questionId']);
            if (! $question) {
                continue;
            }

            $strand = $question['strand'];
            if (! isset($strandResults[$strand])) {
                $strandResults[$strand] = ['correct' => 0, 'total' => 0];
            }

            $strandResults[$strand]['total']++;
            if ($studentAnswer['response'] === $question['config']['key']) {
                $strandResults[$strand]['correct']++;
            }
        }

        return $strandResults;
    }

    private function getWrongAnswers(array $response): array
    {
        $wrongAnswers = [];
        foreach ($response['responses'] as $studentAnswer) {
            $question = $this->findQuestion($studentAnswer['questionId']);
            if ($question && $studentAnswer['response'] !== $question['config']['key']) {
                $wrongAnswers[] = $studentAnswer;
            }
        }

        return $wrongAnswers;
    }

    private function findOptionById(array $question, string $optionId): ?array
    {
        foreach ($question['config']['options'] as $option) {
            if ($option['id'] === $optionId) {
                return $option;
            }
        }

        return null;
    }
}
