<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;

class LegalController
{
    public function privacy(Request $request, Response $response, Session $session): void
    {
        $view = new View();
        $html = $view->render('legal.privacy', [
            'pageTitle' => 'Data Privacy Notice',
            'departmentName' => 'College of Information Technology',
            'institutionName' => 'Golden West Colleges',
        ]);
        $response->html($html);
    }

    public function terms(Request $request, Response $response, Session $session): void
    {
        $view = new View();
        $html = $view->render('legal.terms', [
            'pageTitle' => 'Terms of Academic Service & Grading Policy',
            'departmentName' => 'College of Information Technology',
            'institutionName' => 'Golden West Colleges',
        ]);
        $response->html($html);
    }
}
