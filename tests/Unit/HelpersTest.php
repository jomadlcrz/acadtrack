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
}