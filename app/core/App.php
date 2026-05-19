<?php

namespace Core;

class App
{
    public function run(): void
    {
        $uri = $this->getUri();

        $controllerName = 'HomeController';
        $method = 'index';

        if ($uri !== '') {
            $method = $this->formatMethodName($uri);
        }

        $controllerClass = 'Controllers\\' . $controllerName;
        $controllerFile = __DIR__ . '/../controllers/' . $controllerName . '.php';

        if (!file_exists($controllerFile)) {
            $this->error404();
            return;
        }

        require_once $controllerFile;

        if (!class_exists($controllerClass)) {
            $this->error404();
            return;
        }

        $controller = new $controllerClass();

        /**
         * Prevent invalid or internal method access
         */
        if (
            !method_exists($controller, $method) ||
            str_starts_with($method, '_') ||
            str_starts_with($method, '__')
        ) {
            $this->error404();
            return;
        }

        $controller->$method();
    }

    private function getUri(): string
    {
        $uri = parse_url(
            $_SERVER['REQUEST_URI'] ?? '/',
            PHP_URL_PATH
        );

        $scriptName = str_replace(
            '\\',
            '/',
            $_SERVER['SCRIPT_NAME'] ?? ''
        );

        $basePath = str_replace('/index.php', '', $scriptName);

        if (
            $basePath !== '' &&
            strpos($uri, $basePath) === 0
        ) {
            $uri = substr($uri, strlen($basePath));
        }

        return trim($uri, '/');
    }

    private function formatMethodName(string $uri): string
    {
        $parts = explode('-', $uri);

        $method = array_shift($parts);

        foreach ($parts as $part) {
            $method .= ucfirst($part);
        }

        return preg_replace('/[^a-zA-Z0-9]/', '', $method);
    }

    private function error404(): void
    {
        http_response_code(404);

        echo '404 - Page not found';
    }
}