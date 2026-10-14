<?php

namespace Zyrtex\Core;

class View {
    public static function render(string $viewPath, array $data = [], ?string $layout = 'public'): void {
        $appConfig = require __DIR__ . '/../../config/app.php';
        $baseUrl = rtrim($appConfig['url'], '/');

        // Variáveis padrão injetadas em todas as views
        $defaultData = [
            'app' => $appConfig,
            'baseUrl' => $baseUrl,
            'tenant' => Tenant::current(),
            'flashes' => Session::getFlashes(),
            'csrf' => CSRF::getToken(),
            'csrfToken' => CSRF::getToken(),
            'csrfField' => CSRF::field(),
            'currentUser' => Session::get('user'),
        ];

        // Mescla variáveis
        $extractedData = array_merge($defaultData, $data);
        extract($extractedData, EXTR_SKIP);

        $viewFile = __DIR__ . "/../../views/{$viewPath}.php";
        if (!file_exists($viewFile)) {
            throw new \Exception("View não encontrada: views/{$viewPath}.php");
        }

        // Renderiza o conteúdo da view principal em buffer
        ob_start();
        include $viewFile;
        $content = ob_get_clean();

        // Se layout foi informado, inclui dentro do layout
        if ($layout !== null) {
            $layoutFile = __DIR__ . "/../../views/layouts/{$layout}.php";
            if (!file_exists($layoutFile)) {
                throw new \Exception("Layout não encontrado: views/layouts/{$layout}.php");
            }
            include $layoutFile;
        } else {
            echo $content;
        }
    }

    /**
     * Helper para incluir partials
     */
    public static function partial(string $partialPath, array $data = []): void {
        $cleanPath = ltrim(preg_replace('#^partials/#', '', $partialPath), '/');
        $partialFile = __DIR__ . "/../../views/partials/{$cleanPath}.php";
        if (file_exists($partialFile)) {
            extract($data, EXTR_SKIP);
            include $partialFile;
        }
    }

    /**
     * Alias de compatibilidade para View::partial
     */
    public static function renderPartial(string $partialPath, array $data = []): void {
        self::partial($partialPath, $data);
    }
}
