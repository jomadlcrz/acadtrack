<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AcademicTerm;
use App\Models\GradingPeriod;
use App\Models\GradingSheet;
use App\Models\Subject;
use App\Models\User;
use App\Services\GradingService;
use App\Services\TermClosureService;
use PHPUnit\Framework\TestCase;

class TermClosureWorkflowTest extends TestCase
{
    private TermClosureService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TermClosureService();
        $this->cleanupTestData();
    }

    protected function tearDown(): void
    {
        $this->cleanupTestData();
        parent::tearDown();
    }

    public static function tearDownAfterClass(): void
    {
        try {
            AcademicTerm::where('school_year', 'like', '209%')->delete();
            AcademicTerm::whereIn('id', [1, 2])->update([
                'is_closed' => 0,
                'closed_at' => null,
                'closed_by' => null,
                'closure_reason' => null
            ]);
            AcademicTerm::where('id', 1)->update(['is_active' => 1]);
            AcademicTerm::where('id', 2)->update(['is_active' => 0]);
            GradingPeriod::whereIn('id', [1, 2, 3, 4])->update([
                'is_closed' => 0,
                'closed_at' => null
            ]);
        } catch (\Throwable) {
        }
    }

    private function cleanupTestData(): void
    {
        try {
            AcademicTerm::where('school_year', 'like', '209%')->delete();
            AcademicTerm::whereIn('id', [1, 2])->update([
                'is_closed' => 0,
                'closed_at' => null,
                'closed_by' => null,
                'closure_reason' => null
            ]);
            AcademicTerm::where('id', 1)->update(['is_active' => 1]);
            AcademicTerm::where('id', 2)->update(['is_active' => 0]);
            GradingPeriod::whereIn('id', [1, 2, 3, 4])->update([
                'is_closed' => 0,
                'closed_at' => null
            ]);
        } catch (\Throwable) {
        }
    }

    public function testTermClosureColumnsAndDefaultState(): void
    {
        $term = AcademicTerm::first();
        $this->assertNotNull($term);
        $this->assertArrayHasKey('is_closed', $term->toArray());
        
        $period = GradingPeriod::first();
        $this->assertNotNull($period);
        $this->assertArrayHasKey('is_closed', $period->toArray());
    }

    public function testTermClosureGetClosuresList(): void
    {
        $closures = $this->service->getClosures();
        $this->assertIsArray($closures);
        $this->assertNotEmpty($closures);
        $first = $closures[0];
        $this->assertArrayHasKey('id', $first);
        $this->assertArrayHasKey('school_year', $first);
        $this->assertArrayHasKey('semester_name', $first);
        $this->assertArrayHasKey('is_closed', $first);
        $this->assertArrayHasKey('periods', $first);
        $this->assertArrayHasKey('stats', $first);
        $this->assertArrayHasKey('sets_count', $first['stats']);
        $this->assertArrayHasKey('subjects_count', $first['stats']);
    }

    public function testTermClosurePreviewGeneration(): void
    {
        $term = AcademicTerm::first();
        $this->assertNotNull($term);

        $preview = $this->service->getTermPreview((int) $term->id);
        $this->assertNotNull($preview);
        $this->assertArrayHasKey('term', $preview);
        $this->assertArrayHasKey('counts', $preview);
        $this->assertArrayHasKey('effects', $preview);
        $this->assertArrayHasKey('default_reason', $preview);
        $this->assertGreaterThanOrEqual(0, $preview['counts']['subjects']);
        $this->assertGreaterThanOrEqual(0, $preview['counts']['total_sheets']);
    }

    public function testCloseTermLocksTermAndGradingPeriods(): void
    {
        // Create an isolated academic term for testing closure
        $testTerm = AcademicTerm::create([
            'academic_year_id' => 1,
            'school_year' => '2098-2099',
            'semester' => 1,
            'is_active' => 0,
            'is_closed' => 0,
        ]);

        $gp = GradingPeriod::create([
            'academic_term_id' => $testTerm->id,
            'name' => 'Prelim',
            'order_num' => 1,
            'is_current' => 1,
            'is_closed' => 0,
        ]);

        $admin = User::first();
        $adminId = $admin ? (int) $admin->id : 1;

        // Execute closure
        $result = $this->service->closeTerm((int) $testTerm->id, $adminId, 'Final semester verification completed');
        $this->assertTrue($result['success']);

        // Verify term is closed
        $updatedTerm = AcademicTerm::find($testTerm->id);
        $this->assertEquals(1, (int) $updatedTerm->is_closed);
        $this->assertNotNull($updatedTerm->closed_at);
        $this->assertEquals($adminId, (int) $updatedTerm->closed_by);
        $this->assertEquals('Final semester verification completed', $updatedTerm->closure_reason);

        // Verify period is closed
        $updatedGp = GradingPeriod::find($gp->id);
        $this->assertEquals(1, (int) $updatedGp->is_closed);
        $this->assertNotNull($updatedGp->closed_at);

        // Clean up
        $gp->delete();
        $testTerm->delete();
    }

    public function testReopenTermWithAuditReason(): void
    {
        $testTerm = AcademicTerm::create([
            'academic_year_id' => 1,
            'school_year' => '2097-2098',
            'semester' => 1,
            'is_active' => 0,
            'is_closed' => 1,
            'closed_at' => date('Y-m-d H:i:s'),
            'closure_reason' => 'Initial test closure',
        ]);

        // Reopen without reason should throw
        $this->expectException(\InvalidArgumentException::class);
        $this->service->reopenTerm((int) $testTerm->id, 1, '');

        // Reopen with reason
        $result = $this->service->reopenTerm((int) $testTerm->id, 1, 'Authorized grade corrections for student 2026-0001');
        $this->assertTrue($result['success']);

        $updated = AcademicTerm::find($testTerm->id);
        $this->assertEquals(0, (int) $updated->is_closed);
        $this->assertNull($updated->closed_at);

        $testTerm->delete();
    }

    public function testGradingServiceRejectsGradeEncodingOnClosedTerm(): void
    {
        $closedTerm = AcademicTerm::create([
            'academic_year_id' => 1,
            'school_year' => '2096-2097',
            'semester' => 1,
            'is_active' => 0,
            'is_closed' => 1,
            'closed_at' => date('Y-m-d H:i:s'),
            'closure_reason' => 'Locked by Dean',
        ]);

        $gp = GradingPeriod::create([
            'academic_term_id' => $closedTerm->id,
            'name' => 'Final',
            'order_num' => 4,
            'is_current' => 1,
            'is_closed' => 1,
        ]);

        $gradingService = new GradingService();

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Cannot modify grades: Academic term has been officially closed and locked.');

        $gradingService->saveGrades(1, 1, (int) $gp->id, (int) $closedTerm->id, [1 => 88.5]);

        $gp->delete();
        $closedTerm->delete();
    }

    public function testGradingServiceRejectsGradeEncodingOnClosedGradingPeriod(): void
    {
        $openTerm = AcademicTerm::create([
            'academic_year_id' => 1,
            'school_year' => '2095-2096',
            'semester' => 1,
            'is_active' => 1,
            'is_closed' => 0,
        ]);

        $closedGp = GradingPeriod::create([
            'academic_term_id' => $openTerm->id,
            'name' => 'Prelim',
            'order_num' => 1,
            'is_current' => 0,
            'is_closed' => 1,
            'closure_reason' => 'Prelim deadline passed',
        ]);

        $gradingService = new GradingService();

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Cannot modify grades: This grading period has been officially closed and locked.');

        $gradingService->saveGrades(1, 1, (int) $closedGp->id, (int) $openTerm->id, [1 => 92.0]);

        $closedGp->delete();
        $openTerm->delete();
    }

    public function testToggleIndividualGradingPeriodState(): void
    {
        $term = AcademicTerm::create([
            'academic_year_id' => 1,
            'school_year' => '2094-2095',
            'semester' => 1,
            'is_active' => 1,
            'is_closed' => 0,
        ]);

        $gp = GradingPeriod::create([
            'academic_term_id' => $term->id,
            'name' => 'Midterm',
            'order_num' => 2,
            'is_current' => 1,
            'is_closed' => 0,
        ]);

        // Lock period
        $resClose = $this->service->togglePeriodState((int) $gp->id, 'closed', 'Exam week ended');
        $this->assertTrue($resClose['success']);

        $updatedGp = GradingPeriod::find($gp->id);
        $this->assertEquals(1, (int) $updatedGp->is_closed);
        $this->assertEquals('Exam week ended', $updatedGp->closure_reason);

        // Unlock period
        $resOpen = $this->service->togglePeriodState((int) $gp->id, 'open');
        $this->assertTrue($resOpen['success']);

        $reopenedGp = GradingPeriod::find($gp->id);
        $this->assertEquals(0, (int) $reopenedGp->is_closed);
        $this->assertNull($reopenedGp->closed_at);

        $gp->delete();
        $term->delete();
    }

    public function testControllerIndexRendersSuccessfully(): void
    {
        $_SESSION['user'] = ['id' => 1, 'email' => 'admin@gwc.edu', 'role' => 'Admin'];
        $_SESSION['role'] = 'Admin';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/admin/academic-terms/closure';

        $controller = new \App\Controllers\Admin\TermClosureController();
        $req = new \App\Core\Request();
        $res = new \App\Core\Response();
        $session = new \App\Core\Session();

        ob_start();
        $controller->index($req, $res, $session);
        $content = ob_get_clean();

        $this->assertNotEmpty($content);
        $this->assertStringContainsString('Term Closure', $content);
        $this->assertStringContainsString('Operational Sequence', $content);
    }

    public function testSetModificationsBlockedOnClosedTerm(): void
    {
        $term = AcademicTerm::create([
            'academic_year_id' => 1,
            'school_year' => '2098-2099',
            'semester' => 1,
            'is_active' => 0,
            'is_closed' => 1,
            'closed_at' => date('Y-m-d H:i:s'),
            'closed_by' => 1,
            'closure_reason' => 'Term sealed',
        ]);

        $set = \App\Models\Set::create([
            'academic_term_id' => (int) $term->id,
            'program_id' => 1,
            'department_id' => 1,
            'name' => 'BSCS-4A-TEST',
            'set_name' => 'BSCS-4A-TEST',
            'year_level' => 4,
            'set_code' => 'TEST',
            'status' => 'active',
        ]);

        $_SESSION['user'] = ['id' => 1, 'email' => 'admin@gwc.edu', 'role' => 'Admin'];
        $_SESSION['role'] = 'Admin';
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $controller = new \App\Controllers\Admin\SetController();
        $req = new \App\Core\Request([], ['set_code' => 'MOD', 'status' => 'active']);
        $res = new \App\Core\Response();
        $session = new \App\Core\Session();

        $controller->update($req, $res, $session, (string) $set->id);
        $this->assertStringContainsString('closed and sealed', $session->getFlash('error') ?? '');

        $set->delete();
        $term->delete();
    }

    public function testTermPreviewReturnsClosedByName(): void
    {
        $preview = $this->service->getTermPreview(1);
        $this->assertNotNull($preview);
        $this->assertArrayHasKey('closed_by_name', $preview['term']);
    }
}

