<?php
declare(strict_types=1);

namespace App\Core;

final class Auth
{
    private static ?array $user = null;
    private static bool $loaded = false;

    public static function user(): ?array
    {
        if (!self::$loaded) {
            self::$loaded = true;
            $id = Session::get('user_id');
            if ($id) {
                $user = Database::one('SELECT * FROM users WHERE id = ? AND is_active = 1', [$id]);
                if ($user) {
                    self::$user = $user;
                } else {
                    Session::forget('user_id');
                }
            }
        }
        return self::$user;
    }

    public static function id(): ?int
    {
        $user = self::user();
        return $user ? (int) $user['id'] : null;
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function isAdmin(): bool
    {
        return (self::user()['role'] ?? null) === 'admin';
    }

    public static function attempt(string $email, string $password): ?array
    {
        $user = Database::one('SELECT * FROM users WHERE email = ?', [mb_strtolower(trim($email))]);
        if (!$user || !password_verify($password, $user['password'])) {
            return null;
        }
        if (password_needs_rehash($user['password'], PASSWORD_DEFAULT)) {
            Database::update('users', ['password' => password_hash($password, PASSWORD_DEFAULT)], 'id = ?', [$user['id']]);
        }
        return $user;
    }

    public static function login(array $user): void
    {
        Session::regenerate();
        Session::set('user_id', (int) $user['id']);
        Database::update('users', ['last_login_at' => now()], 'id = ?', [$user['id']]);
        self::$user = $user;
        self::$loaded = true;
    }

    public static function logout(): void
    {
        Session::forget('user_id');
        Session::regenerate();
        self::$user = null;
    }

    public static function requireLogin(): void
    {
        if (!self::check()) {
            Session::flash('warning', 'Devam etmek için lütfen giriş yapın.');
            redirect('giris?redirect=' . rawurlencode(current_path() . (empty($_GET) ? '' : '?' . http_build_query($_GET))));
        }
    }

    public static function requireAdmin(): void
    {
        if (!self::check()) {
            redirect('giris?redirect=' . rawurlencode(current_path()));
        }
        if (!self::isAdmin()) {
            abort(403);
        }
    }

    /** Basit kaba kuvvet koruması: 15 dakikada en fazla 5 hatalı deneme */
    public static function tooManyAttempts(string $email): bool
    {
        $since = date('Y-m-d H:i:s', time() - 900);
        $count = (int) Database::value(
            'SELECT COUNT(*) FROM login_attempts WHERE (ip = ? OR email = ?) AND created_at >= ?',
            [client_ip(), mb_strtolower($email), $since]
        );
        return $count >= 5;
    }

    public static function recordFailedAttempt(string $email): void
    {
        Database::insert('login_attempts', ['ip' => client_ip(), 'email' => mb_strtolower($email), 'created_at' => now()]);
    }

    public static function clearAttempts(string $email): void
    {
        Database::delete('login_attempts', 'ip = ? OR email = ?', [client_ip(), mb_strtolower($email)]);
    }
}
