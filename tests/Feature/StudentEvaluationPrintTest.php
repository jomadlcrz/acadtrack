<?php

declare(strict_types=1);

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Controllers\Student\EvaluationController;
use App\Controllers\Dean\GradeReviewController;
use App\Models\User;
use App\Models\Student;
use App\Models\GradingSheet;

class StudentEvaluationPrintTest extends TestCase
{
    private static int $studentUserId = 0;
    private static int $sheetId = 0;

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

        $student = Student::first();
        if ($student) {
            self::$studentUserId = (int) $student['user_id'];
        }

        $sheet = GradingSheet::first();
        if ($sheet) {
            self::$sheetId = (int) $sheet['id'];
        }
    }

    public function testEvaluationPageStructureWhenNoUserGrades(): void
    {
        $user = User::create([
            'email' => 'empty_eval_' . uniqid() . '@example.com',
            'password_hash' => password_hash('secret', PASSWORD_DEFAULT),
            'role' => 'Student',
        ]);

        $req = new Request();
        $res = new Response();
        $sess = new Session();
        $sess->set('user', ['id' => $user->id, 'role' => 'Student']);

        $controller = new EvaluationController();
        ob_start();
        $controller->show($req, $res, $sess);
        $html = ob_get_clean();

        // Print button should NOT be rendered
        $this->assertStringNotContainsString('Print evaluation', $html);
        $this->assertStringContainsString('No evaluation records available', $html);

        // Accessing print endpoint directly should redirect
        $controller->printEvaluation($req, $res, $sess);
        $this->assertSame('No evaluation records available to print.', $sess->getFlash('error'));

        $user->delete();
    }

    public function testDeanGradeReviewPrintOutputsStandaloneDocument(): void
    {
        if (self::$sheetId === 0) {
            $this->markTestSkipped('No grading sheet available for testing print.');
        }

        $req = new Request();
        $res = new Response();
        $sess = new Session();
        $sess->set('user', ['id' => 2, 'role' => 'Dean']);

        $controller = new GradeReviewController();
        ob_start();
        $controller->printSheet($req, $res, $sess, (string) self::$sheetId);
        $html = ob_get_clean();

        // Must be a standalone HTML document
        $this->assertStringContainsString('<!DOCTYPE html>', $html);
        $this->assertStringContainsString('GOLDEN WEST COLLEGES, INC.', $html);
        $this->assertStringContainsString('assets/images/gwc.png', $html);
        $this->assertStringContainsString('assets/images/cite.png', $html);
        $this->assertStringContainsString('OFFICIAL GRADING SHEET &amp; CLASS ROSTER', $html);
        $this->assertStringContainsString('fl-info', $html);
        $this->assertStringContainsString('fl-table', $html);
        $this->assertStringContainsString('fl-signoff', $html);

        // Must not contain dashboard navbar or sidebar chrome
        $this->assertStringNotContainsString('class="sidebar"', $html);
        $this->assertStringNotContainsString('class="navbar', $html);
    }
}
