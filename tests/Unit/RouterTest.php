<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Core\Router;
use App\Controllers\AuthController;

class RouterTest extends TestCase
{
    public function testGetRoutesRegistersCorrectly(): void
    {
        $router = new Router();
        $router->get('/test-route', [AuthController::class, 'showLogin']);

        $routes = $router->getRoutes();
        $this->assertCount(1, $routes);
        $this->assertSame('GET', $routes[0]['method']);
        $this->assertSame('/test-route', $routes[0]['path']);
    }

    public function testPostRoutesRegistersCorrectly(): void
    {
        $router = new Router();
        $router->post('/test-submit', [AuthController::class, 'login']);

        $routes = $router->getRoutes();
        $this->assertCount(1, $routes);
        $this->assertSame('POST', $routes[0]['method']);
        $this->assertSame('/test-submit', $routes[0]['path']);
    }

    public function testGroupRoutePrefix(): void
    {
        $router = new Router();
        $router->group('/api', [], function (Router $r) {
            $r->get('/status', [AuthController::class, 'showLogin']);
        });

        $routes = $router->getRoutes();
        $this->assertCount(1, $routes);
        $this->assertSame('/api/status', $routes[0]['path']);
    }
}