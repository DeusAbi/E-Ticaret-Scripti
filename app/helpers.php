<?php
declare(strict_types=1);

use App\Core\Auth;
use App\Core\Config;
use App\Core\Csrf;
use App\Core\Icons;
use App\Core\Session;
use App\Core\Settings;
use App\Core\View;

function config(string $key, mixed $default = null): mixed
{
    return Config::get($key, $default);
}

function e(mixed $value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function now(): string
{
    return date('Y-m-d H:i:s');
}

/* ----------------------------------------------------------------------
 | URL & İstek yardımcıları
 * -------------------------------------------------------------------- */

/** Uygulamanın kök dizine göre yolu ("" veya "/alt-klasor") */
function base_url_path(): string
{
    static $base = null;
    if ($base !== null) {
        return $base;
    }
    if (PHP_SAPI === 'cli') {
        return $base = rtrim((string) parse_url((string) config('app.url', ''), PHP_URL_PATH), '/');
    }
    $script = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
    $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    // Kök .htaccess ile public/ klasörüne yönlendirilmişse "/public" kısmını gizle
    if ($script !== '' && str_ends_with($script, '/public') && !str_starts_with($uri, $script . '/') && $uri !== $script) {
        $script = substr($script, 0, -7);
    }
    return $base = $script;
}

function url(string $path = ''): string
{
    if (preg_match('#^(https?:)?//#', $path)) {
        return $path;
    }
    return base_url_path() . '/' . ltrim($path, '/');
}

function absolute_url(string $path = ''): string
{
    if (PHP_SAPI !== 'cli' && !empty($_SERVER['HTTP_HOST'])) {
        $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
        return ($https ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . url($path);
    }
    return rtrim((string) config('app.url', ''), '/') . '/' . ltrim($path, '/');
}

function asset(string $path): string
{
    $file = PUBLIC_PATH . '/assets/' . ltrim($path, '/');
    $version = is_file($file) ? (string) filemtime($file) : APP_VERSION;
    return url('assets/' . ltrim($path, '/')) . '?v=' . $version;
}

/** Yüklenen dosya / harici görsel adresi */
function media(?string $path, string $fallback = 'assets/img/placeholder.svg'): string
{
    if (!$path) {
        return url($fallback);
    }
    return preg_match('#^https?://#', $path) ? $path : url($path);
}

function current_path(): string
{
    $uri = rawurldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
    $base = base_url_path();
    if ($base !== '' && str_starts_with($uri, $base)) {
        $uri = substr($uri, strlen($base));
    }
    if (str_starts_with($uri, '/index.php')) {
        $uri = substr($uri, 10);
    }
    return '/' . trim($uri, '/');
}

/** Aktif menü öğesi kontrolü: is_active('admin/urunler*') */
function is_active(string $pattern): bool
{
    $path = trim(current_path(), '/');
    $pattern = trim($pattern, '/');
    if (str_ends_with($pattern, '*')) {
        return str_starts_with($path, rtrim($pattern, '*'));
    }
    return $path === $pattern;
}

/** Mevcut sorgu dizesini değiştirerek URL üretir */
function query_url(array $changes, ?string $path = null): string
{
    $query = array_merge($_GET, $changes);
    $query = array_filter($query, static fn ($v) => $v !== null && $v !== '' && $v !== []);
    $qs = http_build_query($query);
    return url(ltrim($path ?? current_path(), '/')) . ($qs ? '?' . $qs : '');
}

function redirect(string $path): never
{
    $target = preg_match('#^https?://#', $path) ? $path : url($path);
    header('Location: ' . $target, true, 302);
    exit;
}

function back(string $fallback = '/'): never
{
    $ref = $_SERVER['HTTP_REFERER'] ?? '';
    $host = $_SERVER['HTTP_HOST'] ?? '';
    if ($ref !== '' && parse_url($ref, PHP_URL_HOST) === parse_url('http://' . $host, PHP_URL_HOST)) {
        header('Location: ' . $ref, true, 302);
        exit;
    }
    redirect($fallback);
}

/** Yalnızca site içi yönlendirmelere izin verir (open redirect koruması) */
function safe_redirect_path(?string $path, string $fallback = '/'): string
{
    $path = (string) $path;
    if ($path === '' || !str_starts_with($path, '/') || str_starts_with($path, '//') || str_contains($path, '\\')) {
        return $fallback;
    }
    return $path;
}

function input(string $key, mixed $default = null): mixed
{
    $value = $_POST[$key] ?? $_GET[$key] ?? $default;
    return is_string($value) ? trim($value) : $value;
}

function is_ajax(): bool
{
    return strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest'
        || str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');
}

function json_response(array $data, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function client_ip(): string
{
    return substr((string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'), 0, 64);
}

function abort(int $code, string $message = ''): never
{
    http_response_code($code);
    if (is_ajax()) {
        json_response(['ok' => false, 'message' => $message ?: 'İstek işlenemedi.'], $code);
    }
    $titles = [
        403 => 'Erişim Engellendi',
        404 => 'Sayfa Bulunamadı',
        405 => 'Geçersiz İstek',
        419 => 'Oturum Süresi Doldu',
        500 => 'Bir Hata Oluştu',
    ];
    $messages = [
        403 => 'Bu sayfayı görüntüleme yetkiniz bulunmuyor.',
        404 => 'Aradığınız sayfa taşınmış, silinmiş veya hiç var olmamış olabilir.',
        405 => 'Bu adres bu istek türünü desteklemiyor.',
        419 => 'Güvenlik nedeniyle sayfanın süresi doldu. Lütfen sayfayı yenileyip tekrar deneyin.',
        500 => 'Beklenmeyen bir hata oluştu. Lütfen daha sonra tekrar deneyin.',
    ];
    try {
        echo View::render('errors/error', [
            'code'      => $code,
            'pageTitle' => $titles[$code] ?? 'Hata',
            'title'     => $titles[$code] ?? 'Hata',
            'message' => $message ?: ($messages[$code] ?? ''),
        ]);
    } catch (Throwable) {
        echo '<h1>' . $code . '</h1><p>' . e($message ?: ($messages[$code] ?? '')) . '</p>';
    }
    exit;
}

/* ----------------------------------------------------------------------
 | Görünüm yardımcıları
 * -------------------------------------------------------------------- */

function view(string $template, array $data = [], ?string $layout = 'layouts/main'): void
{
    echo View::render($template, $data, $layout);
}

function partial(string $template, array $data = []): string
{
    return View::partial($template, $data);
}

function icon(string $name, string $class = '', int $size = 20, float $stroke = 2): string
{
    return Icons::svg($name, $class, $size, $stroke);
}

function csrf_token(): string
{
    return Csrf::token();
}

function csrf_field(): string
{
    return '<input type="hidden" name="_token" value="' . e(Csrf::token()) . '">';
}

function method_field(string $method): string
{
    return '<input type="hidden" name="_method" value="' . e($method) . '">';
}

function old(string $key, mixed $default = ''): mixed
{
    $old = Session::getFlash('_old', []);
    return $old[$key] ?? $default;
}

function error(string $key): ?string
{
    return Session::getFlash('_errors', [])[$key] ?? null;
}

function has_error(string $key): string
{
    return error($key) ? ' is-invalid' : '';
}

function field_error(string $key): string
{
    $msg = error($key);
    return $msg ? '<div class="field-error">' . e($msg) . '</div>' : '';
}

function with_errors(array $errors, ?array $input = null): void
{
    Session::flash('_errors', $errors);
    $input ??= $_POST;
    unset($input['_token'], $input['password'], $input['password_confirmation'], $input['current_password'], $input['card']);
    Session::flash('_old', $input);
}

function flash(string $type, string $message): void
{
    Session::flash($type, $message);
}

function auth(): ?array
{
    return Auth::user();
}

function setting(string $key, mixed $default = ''): mixed
{
    try {
        return Settings::get($key, $default);
    } catch (Throwable) {
        return $default;
    }
}

function money(mixed $amount, bool $symbol = true): string
{
    $formatted = number_format((float) $amount, 2, ',', '.');
    return $symbol ? $formatted . ' ' . setting('currency_symbol', '₺') : $formatted;
}

function to_decimal(mixed $value): float
{
    $value = trim((string) $value);
    // "1.299,90" => 1299.90 ; "1299.90" => 1299.90
    if (str_contains($value, ',')) {
        $value = str_replace(['.', ','], ['', '.'], $value);
    }
    return round((float) $value, 2);
}

function slugify(string $text): string
{
    $map = ['ç' => 'c', 'Ç' => 'c', 'ğ' => 'g', 'Ğ' => 'g', 'ı' => 'i', 'I' => 'i', 'İ' => 'i', 'ö' => 'o', 'Ö' => 'o', 'ş' => 's', 'Ş' => 's', 'ü' => 'u', 'Ü' => 'u'];
    $text = strtr($text, $map);
    $text = mb_strtolower($text);
    if (function_exists('iconv')) {
        $converted = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
        if ($converted !== false) {
            $text = $converted;
        }
    }
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim((string) $text, '-');
}

function str_limit(?string $text, int $limit = 100): string
{
    $text = trim(strip_tags((string) $text));
    return mb_strlen($text) > $limit ? rtrim(mb_substr($text, 0, $limit)) . '…' : $text;
}

function tr_upper(string $text): string
{
    return mb_strtoupper(strtr($text, ['i' => 'İ', 'ı' => 'I']));
}

function date_tr(?string $date, bool $withTime = true): string
{
    if (!$date) {
        return '-';
    }
    $months = ['', 'Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran', 'Temmuz', 'Ağustos', 'Eylül', 'Ekim', 'Kasım', 'Aralık'];
    $ts = strtotime($date);
    $out = date('j', $ts) . ' ' . $months[(int) date('n', $ts)] . ' ' . date('Y', $ts);
    return $withTime ? $out . ', ' . date('H:i', $ts) : $out;
}

function time_ago(?string $date): string
{
    if (!$date) {
        return '';
    }
    $diff = time() - strtotime($date);
    return match (true) {
        $diff < 60 => 'az önce',
        $diff < 3600 => floor($diff / 60) . ' dk önce',
        $diff < 86400 => floor($diff / 3600) . ' saat önce',
        $diff < 604800 => floor($diff / 86400) . ' gün önce',
        default => date_tr($date, false),
    };
}

function badge(string $label, string $color = 'muted'): string
{
    return '<span class="badge badge-' . e($color) . '">' . e($label) . '</span>';
}

function order_status_badge(string $status): string
{
    $s = App\Services\Order::STATUSES[$status] ?? ['label' => $status, 'color' => 'muted'];
    return badge($s['label'], $s['color']);
}

function payment_status_badge(string $status): string
{
    $s = App\Services\Order::PAYMENT_STATUSES[$status] ?? ['label' => $status, 'color' => 'muted'];
    return badge($s['label'], $s['color']);
}

function stars(float $rating, int $size = 14): string
{
    $html = '<span class="stars" aria-label="5 üzerinden ' . number_format($rating, 1, ',', '') . ' puan">';
    for ($i = 1; $i <= 5; $i++) {
        $class = $rating >= $i ? 'is-full' : ($rating >= $i - 0.5 ? 'is-half' : '');
        $html .= '<span class="star ' . $class . '">' . icon('star', '', $size) . '</span>';
    }
    return $html . '</span>';
}

function mask_email(string $email): string
{
    [$user, $domain] = array_pad(explode('@', $email, 2), 2, '');
    return mb_substr($user, 0, 2) . str_repeat('*', max(1, mb_strlen($user) - 2)) . '@' . $domain;
}

function initials(string $name): string
{
    $parts = preg_split('/\s+/', trim($name)) ?: [];
    $first = mb_substr($parts[0] ?? '', 0, 1);
    $last = count($parts) > 1 ? mb_substr((string) end($parts), 0, 1) : '';
    return tr_upper($first . $last);
}

/** Türkiye'nin 81 ili */
function tr_cities(): array
{
    return ['Adana', 'Adıyaman', 'Afyonkarahisar', 'Ağrı', 'Aksaray', 'Amasya', 'Ankara', 'Antalya', 'Ardahan', 'Artvin', 'Aydın', 'Balıkesir', 'Bartın', 'Batman', 'Bayburt', 'Bilecik', 'Bingöl', 'Bitlis', 'Bolu', 'Burdur', 'Bursa', 'Çanakkale', 'Çankırı', 'Çorum', 'Denizli', 'Diyarbakır', 'Düzce', 'Edirne', 'Elazığ', 'Erzincan', 'Erzurum', 'Eskişehir', 'Gaziantep', 'Giresun', 'Gümüşhane', 'Hakkari', 'Hatay', 'Iğdır', 'Isparta', 'İstanbul', 'İzmir', 'Kahramanmaraş', 'Karabük', 'Karaman', 'Kars', 'Kastamonu', 'Kayseri', 'Kilis', 'Kırıkkale', 'Kırklareli', 'Kırşehir', 'Kocaeli', 'Konya', 'Kütahya', 'Malatya', 'Manisa', 'Mardin', 'Mersin', 'Muğla', 'Muş', 'Nevşehir', 'Niğde', 'Ordu', 'Osmaniye', 'Rize', 'Sakarya', 'Samsun', 'Şanlıurfa', 'Siirt', 'Sinop', 'Şırnak', 'Sivas', 'Tekirdağ', 'Tokat', 'Trabzon', 'Tunceli', 'Uşak', 'Van', 'Yalova', 'Yozgat', 'Zonguldak'];
}

/** Basit sayfalama verisi */
function paginate_links(int $page, int $pages, int $window = 2): array
{
    $links = [];
    for ($i = 1; $i <= $pages; $i++) {
        if ($i === 1 || $i === $pages || abs($i - $page) <= $window) {
            $links[] = $i;
        } elseif (end($links) !== '…') {
            $links[] = '…';
        }
    }
    return $links;
}

/** Ayarlardaki banka hesaplarını [banka, alıcı, iban] dizisine dönüştürür */
function bank_accounts(): array
{
    $rows = [];
    foreach (preg_split('/\r\n|\r|\n/', (string) setting('bank_accounts', '')) as $line) {
        $parts = array_map('trim', explode('|', $line));
        if (count($parts) >= 3 && $parts[2] !== '') {
            $rows[] = ['bank' => $parts[0], 'holder' => $parts[1], 'iban' => $parts[2]];
        }
    }
    return $rows;
}

/**
 * Güvenli zengin metin: "## başlık", "- madde", "**kalın**", "*italik*" ve paragraflar.
 * HTML her zaman kaçışlanır.
 */
function rich_text(?string $text): string
{
    $lines = preg_split('/\r\n|\r|\n/', (string) $text);
    $html = '';
    $inList = false;
    $para = [];
    $inline = static function (string $s): string {
        $s = e($s);
        $s = preg_replace('/\*\*(.+?)\*\*/u', '<strong>$1</strong>', $s);
        return (string) preg_replace('/(?<![*\w])\*(?!\s)(.+?)(?<!\s)\*(?![*\w])/u', '<em>$1</em>', (string) $s);
    };
    $flushPara = static function () use (&$para, &$html, $inline): void {
        if ($para) {
            $html .= '<p>' . implode('<br>', array_map($inline, $para)) . '</p>';
            $para = [];
        }
    };
    foreach ($lines as $line) {
        $t = trim($line);
        if (preg_match('/^#{2,3}\s+(.+)$/u', $t, $m)) {
            $flushPara();
            if ($inList) { $html .= '</ul>'; $inList = false; }
            $html .= '<h3>' . $inline($m[1]) . '</h3>';
        } elseif (preg_match('/^[-•]\s+(.+)$/u', $t, $m)) {
            $flushPara();
            if (!$inList) { $html .= '<ul>'; $inList = true; }
            $html .= '<li>' . $inline($m[1]) . '</li>';
        } elseif ($t === '') {
            $flushPara();
            if ($inList) { $html .= '</ul>'; $inList = false; }
        } else {
            if ($inList) { $html .= '</ul>'; $inList = false; }
            $para[] = $t;
        }
    }
    $flushPara();
    if ($inList) {
        $html .= '</ul>';
    }
    return $html;
}

/** Giriş yapmış kullanıcının favori ürün id'leri (istek başına önbellekli) */
function wishlist_ids(): array
{
    static $ids = null;
    if ($ids === null) {
        $ids = auth()
            ? array_map('intval', array_column(App\Core\Database::all('SELECT product_id FROM wishlists WHERE user_id = ?', [auth()['id']]), 'product_id'))
            : [];
    }
    return $ids;
}

/**
 * Form girdisindeki "1.299,90" / "1299.90" biçimindeki sayıyı "1299.9" yapar.
 * Geçersiz değerleri dokunmadan döndürür, böylece doğrulama hatası verilebilir.
 */
function normalize_decimal_input(mixed $value): string
{
    $value = trim((string) $value);
    if (preg_match('/^\d{1,3}(\.\d{3})+$/', $value)) {
        return str_replace('.', '', $value); // "1.299" => binlik ayırıcı (TR)
    }
    if (preg_match('/^\d{1,3}(\.\d{3})+(,\d+)?$|^\d+([.,]\d+)?$/', $value)) {
        return (string) to_decimal($value);
    }
    return $value;
}
