<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * One step in a grading sheet's life (submitted, review started, approved, returned, adjusted, finalized).
 * Append-only: rows are never edited or deleted, so every return/resubmit cycle keeps its history.
 */
class GradingSheetEvent extends Model
{
    protected $table = 'grading_sheet_events';
    public $timestamps = false;

    public const SUBMITTED = 'SUBMITTED';
    public const REVIEW_STARTED = 'REVIEW_STARTED';
    public const APPROVED = 'APPROVED';
    public const RETURNED = 'RETURNED';
    public const GRADES_ADJUSTED = 'GRADES_ADJUSTED';
    public const FINALIZED = 'FINALIZED';

    /** @return array<int, array<string, mixed>> oldest first */
    public static function forSheet(int $gradingSheetId): array
    {
        $stmt = self::db()->prepare('SELECT * FROM grading_sheet_events WHERE grading_sheet_id = :id ORDER BY id ASC');
        $stmt->execute(['id' => $gradingSheetId]);
        return $stmt->fetchAll();
    }
}
