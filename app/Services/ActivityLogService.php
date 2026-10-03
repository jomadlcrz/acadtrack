<?php

declare(strict_types=1);

namespace App\Services;

use PDO;

/**
 * The staff activity log (`audit_logs`): who did what, to which record, and when.
 * Entries are only ever added, never edited or deleted. Writing one never blocks the action
 * it describes: a failure is only written to the error log.
 */
class ActivityLogService
{
    public const CATEGORY_ACCOUNTS = 'accounts';
    public const CATEGORY_REGISTRATION = 'registration';
    public const CATEGORY_GRADES = 'grades';
    public const CATEGORY_TERMS = 'terms';
    public const CATEGORY_ASSIGNMENTS = 'assignments';

    public const CATEGORY_LABELS = [
        self::CATEGORY_ACCOUNTS => 'Accounts',
        self::CATEGORY_REGISTRATION => 'Registration',
        self::CATEGORY_GRADES => 'Grades',
        self::CATEGORY_TERMS => 'Terms',
        self::CATEGORY_ASSIGNMENTS => 'Assignments',
    ];

    private const ACTION_MAX = 80;
    private const SUMMARY_MAX = 500;
    private const LABEL_MAX = 120;

    /**
     * @param array{category:string, action:string, target_type?:string, target_id?:int|string, target_label?:string, summary:string} $entry
     * @param array{id?:int|null, name?:string, email?:string, role?:string}|null $actor defaults to the signed-in user
     */
    public static function record(array $entry, ?array $actor = null): void
    {
        $actor ??= self::actorFromSession();
        if ($actor === null) {
            return; // CLI, tests, or a request with nobody signed in
        }

        try {
            $stmt = \App\Core\Database::getConnection()->prepare("
                INSERT INTO audit_logs
                    (actor_id, actor_name, actor_email, actor_role, category, action,
                     target_type, target_id, target_label, summary, ip_address)
                VALUES
                    (:actor_id, :actor_name, :actor_email, :actor_role, :category, :action,
                     :target_type, :target_id, :target_label, :summary, :ip)
            ");
            $stmt->execute([
                'actor_id' => $actor['id'] ?? null,
                'actor_name' => self::clip((string) ($actor['name'] ?? '') ?: (string) ($actor['email'] ?? '') ?: 'Staff', self::LABEL_MAX),
                'actor_email' => self::clip((string) ($actor['email'] ?? ''), 255),
                'actor_role' => self::clip((string) ($actor['role'] ?? ''), 30),
                'category' => self::clip($entry['category'], 30),
                'action' => self::clip(trim($entry['action']), self::ACTION_MAX),
                'target_type' => self::clip((string) ($entry['target_type'] ?? ''), 40),
                'target_id' => self::clip((string) ($entry['target_id'] ?? ''), 64),
                'target_label' => self::clip(trim((string) ($entry['target_label'] ?? '')), self::LABEL_MAX),
                'summary' => self::clip(trim($entry['summary']), self::SUMMARY_MAX),
                'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
            ]);
        } catch (\Throwable $e) {
            error_log('[ActivityLog] Could not record activity: ' . $e->getMessage());
        }
    }

    /**
     * @param array{category?:string, search?:string, start_date?:string, end_date?:string} $filters
     * @return array{data:array<int,array<string,mixed>>, total:int, page:int, perPage:int, per_page:int, lastPage:int, last_page:int}
     */
    public static function paginate(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $page = max(1, $page);
        $clauses = [];
        $params = [];

        if (!empty($filters['category']) && isset(self::CATEGORY_LABELS[$filters['category']])) {
            $clauses[] = 'category = :category';
            $params['category'] = $filters['category'];
        }
        if (!empty($filters['search'])) {
            $clauses[] = "CONCAT(actor_name, ' ', actor_email, ' ', action, ' ', target_label, ' ', summary, ' ', target_id) LIKE :search";
            $params['search'] = '%' . $filters['search'] . '%';
        }
        if (!empty($filters['start_date']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $filters['start_date'])) {
            $clauses[] = 'created_at >= :start_date';
            $params['start_date'] = $filters['start_date'] . ' 00:00:00';
        }
        if (!empty($filters['end_date']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $filters['end_date'])) {
            $clauses[] = 'created_at <= :end_date';
            $params['end_date'] = $filters['end_date'] . ' 23:59:59';
        }
        $where = $clauses ? 'WHERE ' . implode(' AND ', $clauses) : '';

        $pdo = \App\Core\Database::getConnection();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM audit_logs {$where}");
        $stmt->execute($params);
        $total = (int) $stmt->fetchColumn();

        $stmt = $pdo->prepare("SELECT * FROM audit_logs {$where} ORDER BY created_at DESC, id DESC LIMIT :limit OFFSET :offset");
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', ($page - 1) * $perPage, PDO::PARAM_INT);
        $stmt->execute();

        $lastPage = max(1, (int) ceil($total / $perPage));
        return [
            'data' => $stmt->fetchAll(PDO::FETCH_ASSOC),
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'per_page' => $perPage,
            'lastPage' => $lastPage,
            'last_page' => $lastPage,
        ];
    }

    /** Where to open the record an entry is about, or null when there is no page for it. */
    public static function targetUrl(string $targetType, string $targetId): ?string
    {
        if ($targetId === '') {
            return null;
        }
        return match ($targetType) {
            'account' => '/admin/users/' . $targetId . '/edit',
            'grading_sheet' => '/dean/grade-review/' . $targetId,
            default => null,
        };
    }

    private static function actorFromSession(): ?array
    {
        $user = $_SESSION['user'] ?? null;
        if (empty($user['id'])) {
            return null;
        }
        return [
            'id' => (int) $user['id'],
            'name' => trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')),
            'email' => (string) ($user['email'] ?? ''),
            'role' => (string) ($user['role'] ?? ''),
        ];
    }

    private static function clip(string $value, int $max): string
    {
        return mb_strlen($value) > $max ? mb_substr($value, 0, $max - 1) . '…' : $value;
    }
}
