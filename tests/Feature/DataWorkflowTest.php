<?php

declare(strict_types=1);

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use App\Models\AcademicTerm;
use App\Models\Subject;
use App\Models\Faculty;
use App\Models\Student;
use App\Models\Grade;
use App\Models\GradingSheet;
use App\Services\GradeService;
use App\Services\EvaluationService;

class DataWorkflowTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        $dotenv = \Dotenv\Dotenv::createImmutable(dirname(__DIR__, 2));
        $dotenv->load();
        new \App\Core\Database(
            env('DB_HOST'),
            env('DB_DATABASE'),
            env('DB_USERNAME'),
            (string) env('DB_PASSWORD', '')
        );
    }

    public function testActiveAcademicTermExists(): void
    {
        $term = AcademicTerm::getActive();
        $this->assertNotNull($term);
        $this->assertSame(1, (int) $term['is_active']);
    }

    public function testSubjectsAreConfigured(): void
    {
        $subjects = Subject::all();
        $this->assertGreaterThanOrEqual(3, count($subjects));
    }

    public function testStudentEvaluationCalculations(): void
    {
        $evalService = new EvaluationService();

        // Test Excellent
        $res = $evalService->calculateEvaluation(['Prelim' => 95, 'Midterm' => 92]);
        $this->assertSame('Excellent', $res['status']);
        $this->assertSame(93.5, $res['average']);

        // Test Very Good
        $res = $evalService->calculateEvaluation(['Prelim' => 84, 'Midterm' => 82]);
        $this->assertSame('Very Good', $res['status']);

        // Test Failing
        $res = $evalService->calculateEvaluation(['Prelim' => 45, 'Midterm' => 40]);
        $this->assertSame('Failing', $res['status']);
        $this->assertSame('Unsatisfactory performance', $res['remarks']);
    }

    public function testGradingSheetsLifecycleStates(): void
    {
        $term = AcademicTerm::getActive();
        $sheets = (new \App\Repositories\GradingSheetRepository())->getPendingReview((int) $term['id']);
        $this->assertIsArray($sheets);
    }
}