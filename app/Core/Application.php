<?php

declare(strict_types=1);

namespace App\Core;

class Application
{
    public Router $router;
    public Database $database;
    public Request $request;
    public Response $response;
    public Session $session;
    public View $view;

    public function __construct(Router $router, Database $database)
    {
        $this->request = new Request();
        $this->response = new Response();
        $this->session = new Session();
        $this->router = $router;
        $this->database = $database;
        $this->view = new View();
    }

    public function run(): void
    {
        // Enforce clean URL: redirect any direct /public access to clean URL
        $requestUri = $_SERVER['REQUEST_URI'] ?? '';
        if (str_contains($requestUri, '/public/')) {
            $cleanUri = preg_replace('#/public/#', '/', $requestUri, 1);
            header("Location: {$cleanUri}", true, 301);
            exit;
        } elseif (str_ends_with($requestUri, '/public')) {
            $cleanUri = substr($requestUri, 0, -7) ?: '/';
            header("Location: {$cleanUri}", true, 301);
            exit;
        }

        try {
            $method = $this->request->method();
            $path = $this->request->path();

            $this->router->resolve($method, $path, $this->request, $this->response, $this->session);
        } catch (\Throwable $e) {
            $this->handleException($e);
        }
    }

    private function handleException(\Throwable $e): void
    {
        error_log("[Application Error] " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());

        $friendlyMessage = 'An unexpected error occurred. Please try again.';
        $isDuplicate = false;

        if ($e instanceof \PDOException || ($e->getPrevious() instanceof \PDOException)) {
            $pdoException = $e instanceof \PDOException ? $e : $e->getPrevious();
            $msg = $pdoException->getMessage();
            $code = (string) $pdoException->getCode();

            if ($code === '23000' || str_contains($msg, 'Duplicate entry') || str_contains($msg, '1062')) {
                $isDuplicate = true;
                if (str_contains($msg, 'unique_subject') || str_contains($msg, 'subjects')) {
                    $friendlyMessage = 'A subject with this code already exists for the active academic term.';
                } elseif (str_contains($msg, 'users.email') || str_contains($msg, 'for key \'email\'') || str_contains($msg, 'idx_email')) {
                    $friendlyMessage = 'An account with this email address already exists.';
                } elseif (str_contains($msg, 'student_number')) {
                    $friendlyMessage = 'A student with this student number already exists.';
                } elseif (str_contains($msg, 'unique_assignment') || str_contains($msg, 'faculty_subjects')) {
                    $friendlyMessage = 'This faculty member is already assigned to this subject for this term.';
                } else {
                    $friendlyMessage = 'A duplicate record with this information already exists in the system.';
                }
            } elseif (str_contains($msg, 'foreign key constraint fails') || str_contains($msg, 'Cannot delete or update a parent row')) {
                $friendlyMessage = 'Cannot complete this action because related records exist in the system.';
            } else {
                $friendlyMessage = 'A database conflict or connection issue occurred while processing your request.';
            }
        } elseif ($e instanceof \RuntimeException) {
            $friendlyMessage = $e->getMessage();
        }

        // If it was a state-mutating request (POST/PUT/DELETE), flash friendly error and redirect back to UI
        if (in_array($this->request->method(), ['POST', 'PUT', 'DELETE'], true)) {
            $this->session->flash('error', $friendlyMessage);
            $referer = $_SERVER['HTTP_REFERER'] ?? '';
            if (!empty($referer)) {
                header("Location: {$referer}");
                exit;
            }
            redirect('/dashboard');
        }

        // For GET requests or unhandled fatal scenarios, render error page
        $debug = (bool) env('APP_DEBUG', false) || env('APP_ENV') === 'development';
        http_response_code(500);
        try {
            $html = $this->view->render('errors.error', [
                'title' => $isDuplicate ? 'Duplicate Entry Conflict' : 'Request Could Not Be Completed',
                'message' => $friendlyMessage,
                'debug' => $debug,
                'exception' => $e,
            ]);
            echo $html;
        } catch (\Throwable) {
            echo "<div style='font-family:sans-serif;padding:30px;max-width:600px;margin:50px auto;border-top:4px solid #dc2626;background:#fff;border-radius:8px;box-shadow:0 4px 6px rgba(0,0,0,0.1);'>";
            echo "<h2 style='color:#1e293b;'>Notice</h2>";
            echo "<p style='color:#475569;'>" . htmlspecialchars($friendlyMessage) . "</p>";
            echo "<p><a href='javascript:history.back()' style='color:#2563eb;'>&larr; Go Back</a></p>";
            echo "</div>";
        }
        exit;
    }
}
