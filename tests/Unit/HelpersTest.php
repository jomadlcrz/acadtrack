<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class HelpersTest extends TestCase
{
    public function testUrlGeneratesCleanPath(): void
    {
        $url = url('/login');
        $this->assertStringEndsWith('/login', $url);
    }

    public function testAssetGeneratesAssetPath(): void
    {
        $asset = asset('css/app.css');
        $this->assertStringContainsString('assets/css/app.css', $asset);
    }

    public function testCurrentRoutePathDefaultsToRoot(): void
    {
        $_SERVER['REQUEST_URI'] = '/';
        $path = current_route_path();
        $this->assertSame('/', $path);
    }

    public function testBasePathStripsPublicWhenAccessedCleanly(): void
    {
        $_SERVER['SCRIPT_NAME'] = '/grading-system/public/index.php';
        $_SERVER['REQUEST_URI'] = '/grading-system/login';

        $base = base_path_url();
        $this->assertSame('/grading-system', $base);

        $loginUrl = url('/login');
        $this->assertSame('/grading-system/login', $loginUrl);
        $this->assertStringNotContainsString('/public', $loginUrl);

        $assetUrl = asset('css/app.css');
        $this->assertSame('/grading-system/assets/css/app.css', $assetUrl);
        $this->assertStringNotContainsString('/public', $assetUrl);
    }
}