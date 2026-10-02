<?php

declare(strict_types=1);

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use App\Controllers\LegalController;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

class LegalRoutesTest extends TestCase
{
    public function testPrivacyRouteRendersSuccessfully(): void
    {
        $controller = new LegalController();
        $response = new Response();
        $session = new Session();

        ob_start();
        $controller->privacy(new Request(), $response, $session);
        ob_end_clean();

        $html = $response->getBody();
        $this->assertNotEmpty($html);
        $this->assertStringContainsString('Data Privacy Notice', $html);
        $this->assertStringContainsString('Republic Act No. 10173', $html);
        $this->assertStringContainsString('College of Information Technology', $html);
    }

    public function testTermsRouteRendersSuccessfully(): void
    {
        $controller = new LegalController();
        $response = new Response();
        $session = new Session();

        ob_start();
        $controller->terms(new Request(), $response, $session);
        ob_end_clean();

        $html = $response->getBody();
        $this->assertNotEmpty($html);
        $this->assertStringContainsString('Terms of Academic Service', $html);
        $this->assertStringContainsString('Grading Policy', $html);
        $this->assertStringContainsString('Advisory Status of Online Grades', $html);
        $this->assertStringContainsString('College of Information Technology', $html);
    }
}
