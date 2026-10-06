<?php
declare(strict_types=1);

// PHP dahili sunucusu: statik dosyaları doğrudan sun
if (PHP_SAPI === 'cli-server') {
    $file = __DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if (is_file($file) && !str_ends_with($file, '.php')) {
        return false;
    }
}

require dirname(__DIR__) . '/app/bootstrap.php';

use App\Core\Installer;
use App\Core\Router;
use App\Core\Session;

header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: camera=(), microphone=(), geolocation=()');

set_exception_handler(static function (Throwable $e): void {
    error_log((string) $e);
    if (config('app.debug')) {
        http_response_code(500);
        echo '<pre style="padding:24px;font:13px/1.5 monospace;white-space:pre-wrap">' . e((string) $e) . '</pre>';
        return;
    }
    abort(500);
});

Installer::ensureInstalled();
Session::start();

$router = new Router();
require BASE_PATH . '/app/routes.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$router->dispatch($method, current_path());
