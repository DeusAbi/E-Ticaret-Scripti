<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Mailer;

final class AuthController extends Controller
{
    public function loginForm(): void
    {
        if (Auth::check()) {
            redirect(Auth::isAdmin() ? 'admin' : 'hesabim');
        }
        view('auth/login', ['pageTitle' => 'Giriş Yap', 'redirect' => safe_redirect_path((string) input('redirect', ''), '')]);
    }

    public function login(): void
    {
        $redirectTo = safe_redirect_path((string) input('redirect', ''), '');
        $back = 'giris' . ($redirectTo ? '?redirect=' . rawurlencode($redirectTo) : '');
        $data = $this->validate(['email' => 'required|email', 'password' => 'required'], ['email' => 'E-posta', 'password' => 'Şifre'], $back);

        if (Auth::tooManyAttempts($data['email'])) {
            with_errors([], ['email' => $data['email']]);
            flash('error', 'Çok fazla hatalı giriş denemesi yaptınız. Lütfen 15 dakika sonra tekrar deneyin.');
            redirect($back);
        }

        $user = Auth::attempt($data['email'], (string) $_POST['password']);
        if (!$user) {
            Auth::recordFailedAttempt($data['email']);
            with_errors(['email' => 'E-posta adresi veya şifre hatalı.'], ['email' => $data['email']]);
            redirect($back);
        }
        if (!(int) $user['is_active']) {
            flash('error', 'Hesabınız askıya alınmış. Lütfen müşteri hizmetleri ile iletişime geçin.');
            redirect($back);
        }

        Auth::clearAttempts($data['email']);
        Auth::login($user);
        flash('success', 'Hoş geldiniz, ' . explode(' ', $user['name'])[0] . '!');

        if ($redirectTo) {
            redirect(ltrim($redirectTo, '/'));
        }
        redirect($user['role'] === 'admin' ? 'admin' : 'hesabim');
    }

    public function registerForm(): void
    {
        if (Auth::check()) {
            redirect('hesabim');
        }
        view('auth/register', ['pageTitle' => 'Üye Ol']);
    }

    public function register(): void
    {
        $_POST['email'] = mb_strtolower(trim((string) ($_POST['email'] ?? '')));
        $data = $this->validate([
            'name'                  => 'required|min:3|max:120',
            'email'                 => 'required|email|max:190|unique:users,email',
            'phone'                 => 'phone',
            'password'              => 'required|min:8|max:72|password',
            'password_confirmation' => 'required|same:password',
            'terms'                 => 'required',
        ], [
            'name' => 'Ad soyad', 'email' => 'E-posta adresi', 'phone' => 'Telefon', 'password' => 'Şifre',
            'password_confirmation' => 'Şifre tekrarı', 'terms' => 'Üyelik sözleşmesi onayı',
        ], 'kayit');

        $id = Database::insert('users', [
            'name'       => $data['name'],
            'email'      => $data['email'],
            'phone'      => $data['phone'] ?: null,
            'password'   => password_hash((string) $_POST['password'], PASSWORD_DEFAULT),
            'role'       => 'customer',
            'is_active'  => 1,
            'newsletter' => input('newsletter') === '1' ? 1 : 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $user = Database::one('SELECT * FROM users WHERE id = ?', [$id]);
        Auth::login($user);

        Mailer::sendTemplate(
            $user['email'],
            'Aramıza hoş geldiniz!',
            'Hoş geldiniz, ' . $user['name'],
            '<p>' . e(setting('store_name')) . ' ailesine katıldığınız için teşekkür ederiz.</p><p>İlk siparişinizde <strong>HOSGELDIN10</strong> kodu ile %10 indirim kazanabilirsiniz.</p>'
        );

        flash('success', 'Üyeliğiniz oluşturuldu. Alışverişe başlayabilirsiniz!');
        redirect('hesabim');
    }

    public function logout(): void
    {
        Auth::logout();
        flash('success', 'Güvenli bir şekilde çıkış yaptınız.');
        redirect('/');
    }

    public function forgotForm(): void
    {
        view('auth/forgot', ['pageTitle' => 'Şifremi Unuttum']);
    }

    public function forgot(): void
    {
        $data = $this->validate(['email' => 'required|email'], ['email' => 'E-posta'], 'sifremi-unuttum');
        $email = mb_strtolower($data['email']);
        $user = Database::one('SELECT * FROM users WHERE email = ? AND is_active = 1', [$email]);

        if ($user) {
            $token = bin2hex(random_bytes(32));
            Database::delete('password_resets', 'email = ?', [$email]);
            Database::insert('password_resets', ['email' => $email, 'token_hash' => hash('sha256', $token), 'created_at' => now()]);
            $link = absolute_url('sifre-sifirla/' . $token . '?email=' . rawurlencode($email));
            Mailer::sendTemplate(
                $email,
                'Şifre sıfırlama talebi',
                'Şifrenizi sıfırlayın',
                '<p>Merhaba ' . e($user['name']) . ',</p><p>Hesabınız için şifre sıfırlama talebinde bulunuldu. Aşağıdaki bağlantı 60 dakika boyunca geçerlidir.</p>'
                . '<p><a class="btn" href="' . e($link) . '">Şifremi Sıfırla</a></p><p>Bu talebi siz yapmadıysanız bu e-postayı dikkate almayın.</p>'
            );
        }

        flash('success', 'Bu e-posta adresi kayıtlıysa şifre sıfırlama bağlantısı gönderildi. Lütfen gelen kutunuzu kontrol edin.');
        if (config('mail.driver') === 'log' && config('app.debug')) {
            flash('info', 'Geliştirme modu: e-posta içeriği storage/logs/mail.log dosyasına yazıldı.');
        }
        redirect('giris');
    }

    private function findReset(string $token, string $email): ?array
    {
        $row = Database::one('SELECT * FROM password_resets WHERE email = ? AND token_hash = ?', [mb_strtolower($email), hash('sha256', $token)]);
        if (!$row || strtotime($row['created_at']) < time() - 3600) {
            return null;
        }
        return $row;
    }

    public function resetForm(string $token): void
    {
        $email = (string) input('email', '');
        if (!$this->findReset($token, $email)) {
            flash('error', 'Şifre sıfırlama bağlantısı geçersiz veya süresi dolmuş.');
            redirect('sifremi-unuttum');
        }
        view('auth/reset', ['pageTitle' => 'Yeni Şifre Belirle', 'token' => $token, 'email' => $email]);
    }

    public function reset(string $token): void
    {
        $email = (string) input('email', '');
        if (!$this->findReset($token, $email)) {
            flash('error', 'Şifre sıfırlama bağlantısı geçersiz veya süresi dolmuş.');
            redirect('sifremi-unuttum');
        }
        $this->validate([
            'password'              => 'required|min:8|max:72|password',
            'password_confirmation' => 'required|same:password',
        ], ['password' => 'Şifre', 'password_confirmation' => 'Şifre tekrarı'], 'sifre-sifirla/' . $token . '?email=' . rawurlencode($email));

        Database::update('users', ['password' => password_hash((string) $_POST['password'], PASSWORD_DEFAULT), 'updated_at' => now()], 'email = ?', [mb_strtolower($email)]);
        Database::delete('password_resets', 'email = ?', [mb_strtolower($email)]);
        flash('success', 'Şifreniz güncellendi. Yeni şifrenizle giriş yapabilirsiniz.');
        redirect('giris');
    }
}
