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
        $origEnv = $_ENV['APP_URL'] ?? null;
        $origServer = $_SERVER['APP_URL'] ?? null;
        unset($_ENV['APP_URL'], $_SERVER['APP_URL']);
        putenv('APP_URL');

        $_SERVER['SCRIPT_NAME'] = '/acadtrack/public/index.php';
        $_SERVER['REQUEST_URI'] = '/acadtrack/login';

        $base = base_path_url();
        $this->assertSame('/acadtrack', $base);

        $loginUrl = url('/login');
        $this->assertSame('/acadtrack/login', $loginUrl);
        $this->assertStringNotContainsString('/public', $loginUrl);

        $assetUrl = asset('css/app.css');
        $this->assertSame('/acadtrack/assets/css/app.css', $assetUrl);
        $this->assertStringNotContainsString('/public', $assetUrl);

        if ($origEnv !== null) {
            $_ENV['APP_URL'] = $origEnv;
            putenv("APP_URL={$origEnv}");
        }
        if ($origServer !== null) {
            $_SERVER['APP_URL'] = $origServer;
        }
    }

    public function testBasePathSupportsAcadtrack(): void
    {
        $_SERVER['SCRIPT_NAME'] = '/acadtrack/public/index.php';
        $_SERVER['REQUEST_URI'] = '/acadtrack/login';

        $base = base_path_url();
        $this->assertSame('/acadtrack', $base);

        $loginUrl = url('/login');
        $this->assertSame('/acadtrack/login', $loginUrl);

        $assetUrl = asset('css/app.css');
        $this->assertSame('/acadtrack/assets/css/app.css', $assetUrl);
    }
}