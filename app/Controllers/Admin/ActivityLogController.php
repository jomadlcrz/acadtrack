<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Services\ActivityLogService;

/** Read-only view of the staff activity log. */
class ActivityLogController
{
    public function index(Request $request, Response $response, Session $session): void
    {
        $category = (string) $request->get('category', '');
        $filters = [
            'category' => isset(ActivityLogService::CATEGORY_LABELS[$category]) ? $category : '',
            'search' => trim((string) $request->get('search', '')),
            'start_date' => (string) $request->get('start_date', ''),
            'end_date' => (string) $request->get('end_date', ''),
        ];

        $logs = ActivityLogService::paginate($filters, (int) $request->get('page', 1), 20);

        $html = (new View())->render('admin.activity_log.index', [
            'logs' => $logs,
            'filters' => $filters,
        ]);
        $response->html($html);
    }
}
