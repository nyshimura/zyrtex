<?php

namespace Zyrtex\Core;

class Router {
    private array $routes = [];

    public function get(string $path, callable|string|array $handler): void {
        $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, callable|string|array $handler): void {
        $this->addRoute('POST', $path, $handler);
    }

    private function addRoute(string $method, string $path, callable|string|array $handler): void {
        $cleanPath = '/' . trim($path, '/');
        if ($cleanPath === '//') {
            $cleanPath = '/';
        }

        // Converte {param} em expressão regular compatível
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $cleanPath);
        $pattern = "#^" . $pattern . "$#";

        $this->routes[] = [
            'method' => $method,
            'path' => $cleanPath,
            'pattern' => $pattern,
            'handler' => $handler
        ];
    }

    public function dispatch(Request $request): void {
        $method = $request->getMethod();
        if ($method === 'HEAD') {
            $method = 'GET';
        }
        $path = $request->getPath();

        // Verificação automática de CSRF em requisições POST (exceto Webhooks)
        if ($method === 'POST' && !str_contains($path, '/webhook')) {
            $token = $request->input('_csrf') ?? $request->input('csrf_token');
            if (!CSRF::validate($token)) {
                Session::setFlash('error', 'Sessão expirada ou requisição inválida. Por favor, tente novamente.');
                Response::redirect($_SERVER['HTTP_REFERER'] ?? $request->getPath());
                return;
            }
        }

        foreach ($this->routes as $route) {
            if ($route['method'] === $method && preg_match($route['pattern'], $path, $matches)) {
                // Filtra parâmetros nomeados
                $params = array_filter($matches, fn($key) => !is_int($key), ARRAY_FILTER_USE_KEY);

                $handler = $route['handler'];

                if (is_callable($handler)) {
                    call_user_func($handler, $request, ...array_values($params));
                    return;
                }

                if (is_array($handler)) {
                    [$class, $action] = $handler;
                    $controller = new $class();
                    $controller->$action($request, ...array_values($params));
                    return;
                }

                if (is_string($handler) && str_contains($handler, '@')) {
                    [$class, $action] = explode('@', $handler);
                    $fullClass = "Zyrtex\\Controllers\\{$class}";
                    if (!class_exists($fullClass)) {
                        throw new \Exception("Controller não encontrado: {$fullClass}");
                    }
                    $controller = new $fullClass();
                    $controller->$action($request, ...array_values($params));
                    return;
                }
            }
        }

        // Rota não encontrada (404)
        http_response_code(404);
        View::render('errors/404', ['path' => $path], 'public');
    }
}
