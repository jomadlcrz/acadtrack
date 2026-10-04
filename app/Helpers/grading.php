<?php

declare(strict_types=1);

if (!function_exists('grading_sheet_status')) {
    /**
     * How a grading sheet status is shown everywhere: plain-language label, short label, badge class and icon.
     *
     * @return array{label: string, short: string, class: string, icon: string}
     */
    function grading_sheet_status(string $status): array
    {
        return match ($status) {
            'DRAFT' => ['label' => 'Draft', 'short' => 'Draft', 'class' => 'badge-draft', 'icon' => 'bi-pencil'],
            'SUBMITTED' => ['label' => 'Awaiting review', 'short' => 'Awaiting', 'class' => 'badge-submitted', 'icon' => 'bi-hourglass-split'],
            'UNDER_REVIEW' => ['label' => 'Under review', 'short' => 'In review', 'class' => 'badge-under-review', 'icon' => 'bi-eye'],
            'APPROVED' => ['label' => 'Approved', 'short' => 'Approved', 'class' => 'badge-approved', 'icon' => 'bi-check-circle-fill'],
            'FINALIZED' => ['label' => 'Finalized', 'short' => 'Finalized', 'class' => 'badge-finalized', 'icon' => 'bi-lock-fill'],
            'RETURNED' => ['label' => 'Returned', 'short' => 'Returned', 'class' => 'badge-returned', 'icon' => 'bi-arrow-return-left'],
            default => ['label' => ucfirst(strtolower($status)), 'short' => ucfirst(strtolower($status)), 'class' => 'badge-draft', 'icon' => 'bi-circle'],
        };
    }
}
