<?php
declare(strict_types=1);

namespace App\Core;

final class Router
{
    /** @var array<int, array{method:string, pattern:string, regex:string, handler:array|callable}> */
    private array $routes = [];

    public function get(string $path, array|callable $handler): self
    {
        return $this->add('GET', $path, $handler);
    }

    public function post(string $path, array|callable $handler): self
    {
        return $this->add('POST', $path, $handler);
    }

    public function add(string $method, string $path, array|callable $handler): self
    {
        $path = '/' . trim($path, '/');
        $regex = preg_replace('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', '(?P<$1>[^/]+)', $path);
        $this->routes[] = [
            'method'  => $method,
            'pattern' => $path,
            'regex'   => '#^' . $regex . '$#u',
            'handler' => $handler,
        ];
        return $this;
    }

    public function dispatch(string $method, string $path): void
    {
        $path = '/' . trim($path, '/');
        $methodAllowed = false;

        foreach ($this->routes as $route) {
            if (!preg_match($route['regex'], $path, $matches)) {
                continue;
            }
            if ($route['method'] !== $method && !($method === 'HEAD' && $route['method'] === 'GET')) {
                $methodAllowed = true;
                continue;
            }

            if ($method === 'POST' && !Csrf::verify(Csrf::fromRequest())) {
                abort(419);
            }

            $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
            $handler = $route['handler'];

            if (is_array($handler)) {
                [$class, $action] = $handler;
                $controller = new $class();
                $controller->$action(...array_values($params));
            } else {
                $handler(...array_values($params));
            }
            return;
        }

        abort($methodAllowed ? 405 : 404);
    }
}
