<?php

namespace Core;

class Controller
{
    protected function render(
        string $view,
        array $data = []
    ): void {
        /**
         * Make view data available
         */
        foreach ($data as $key => $value) {
            ${$key} = $value;
        }

        $viewsPath = __DIR__ . '/../views/';

        $header = $viewsPath . 'partials/header.php';
        $footer = $viewsPath . 'partials/footer.php';
        $viewFile = $viewsPath . $view . '.php';

        if (!file_exists($viewFile)) {
            http_response_code(404);

            echo 'View not found';

            return;
        }

        require $header;
        require $viewFile;
        require $footer;
    }
}