<?php

declare(strict_types=1);

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use App\Models\AcademicTerm;
use App\Models\Subject;
use App\Services\RankingService;

class RankingAndAnalyticsTest extends TestCase
{
    private static int $termId;
    private static int $subjectId;

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

        $term = AcademicTerm::getActive();
        self::$termId = (int) ($term['id'] ?? 1);

        $subject = Subject::first();
        self::$subjectId = (int) ($subject['id'] ?? 1);
    }

    public function testSubjectRankingsStructure(): void
    {
        $service = new RankingService();
        $rankings = $service->getSubjectRankings(self::$subjectId, self::$termId);

        $this->assertIsArray($rankings);
        $this->assertArrayHasKey('top_performers', $rankings);
        $this->assertArrayHasKey('period_leaders', $rankings);
        $this->assertArrayHasKey('distribution', $rankings);
        $this->assertArrayHasKey('stats', $rankings);

        // Verify distribution keys
        $dist = $rankings['distribution'];
        $expectedBuckets = ['90-100', '80-89', '70-79', '60-69', '50-59', '0-49'];
        foreach ($expectedBuckets as $b) {
            $this->assertArrayHasKey($b, $dist, "Distribution bucket $b must exist");
        }

        // Verify stats structure
        $stats = $rankings['stats'];
        $this->assertArrayHasKey('count', $stats);
        $this->assertArrayHasKey('average', $stats);
        $this->assertArrayHasKey('passed', $stats);
        $this->assertArrayHasKey('failed', $stats);
        $this->assertArrayHasKey('incomplete', $stats);
        $this->assertArrayHasKey('passing_rate', $stats);

        // Verify period leaders structure
        $leaders = $rankings['period_leaders'];
        $this->assertArrayHasKey('prelim', $leaders);
        $this->assertArrayHasKey('midterm', $leaders);
        $this->assertArrayHasKey('semi_final', $leaders);
        $this->assertArrayHasKey('final', $leaders);
    }

    public function testTopPerformersSortOrder(): void
    {
        $service = new RankingService();
        $rankings = $service->getSubjectRankings(self::$subjectId, self::$termId);

        $top = $rankings['top_performers'];
        if (count($top) > 1) {
            for ($i = 0; $i < count($top) - 1; $i++) {
                $this->assertGreaterThanOrEqual(
                    $top[$i + 1]['final_grade'],
                    $top[$i]['final_grade'],
                    'Ranked list must be in descending order of final grade'
                );
                $this->assertLessThanOrEqual(
                    $top[$i + 1]['rank'],
                    $top[$i]['rank'],
                    'Ranks must be non-decreasing'
                );
            }
        } else {
            $this->assertTrue(true);
        }
    }

    public function testTermTopPerformersDeansList(): void
    {
        $service = new RankingService();
        $topTerm = $service->getTermTopPerformers(self::$termId, null, 5);

        $this->assertIsArray($topTerm);
        $this->assertLessThanOrEqual(5, count($topTerm));

        if (!empty($topTerm)) {
            $first = $topTerm[0];
            $this->assertArrayHasKey('rank', $first);
            $this->assertArrayHasKey('student_id', $first);
            $this->assertArrayHasKey('full_name', $first);
            $this->assertArrayHasKey('gwa', $first);
            $this->assertArrayHasKey('subject_count', $first);

            if (count($topTerm) > 1) {
                for ($i = 0; $i < count($topTerm) - 1; $i++) {
                    $this->assertGreaterThanOrEqual(
                        $topTerm[$i + 1]['gwa'],
                        $topTerm[$i]['gwa'],
                        "Dean's list must be sorted descending by GWA"
                    );
                }
            }
        }
    }
}
