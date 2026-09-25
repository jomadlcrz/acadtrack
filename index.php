<?php

declare(strict_types=1);

/**
 * Acadtrack - Root Entry Point for Shared Hosting & Local Servers
 *
 * This file satisfies hosting health checks (e.g. InfinityFree, cPanel)
 * that require an index.php directly inside the htdocs root, and delegates
 * execution to the public application kernel.
 */

require_once __DIR__ . '/public/index.php';
