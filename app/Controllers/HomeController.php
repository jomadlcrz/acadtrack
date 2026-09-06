<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;

class HomeController
{
    public function index(Request $request, Response $response, Session $session): void
    {
        $view = new View();
        $html = $view->render('home.index', [
            'pageTitle' => 'Acadtrack',
        ]);
        $response->html($html);
    }
}
