<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Core\Mailer;
use App\Services\Order;

final class PageController extends Controller
{
    public function show(string $slug): void
    {
        $page = Database::one('SELECT * FROM pages WHERE slug = ? AND is_active = 1', [$slug]);
        if (!$page) {
            abort(404);
        }
        view('pages/show', ['pageTitle' => $page['title'], 'page' => $page]);
    }

    public function contact(): void
    {
        view('pages/contact', ['pageTitle' => 'İletişim']);
    }

    public function sendContact(): void
    {
        // Basit bot koruması (gizli alan)
        if (trim((string) input('website', '')) !== '') {
            redirect('iletisim');
        }
        $data = $this->validate([
            'name'    => 'required|max:120',
            'email'   => 'required|email|max:190',
            'phone'   => 'phone',
            'subject' => 'required|max:190',
            'message' => 'required|min:10|max:3000',
        ], ['name' => 'Ad soyad', 'email' => 'E-posta', 'phone' => 'Telefon', 'subject' => 'Konu', 'message' => 'Mesaj'], 'iletisim');

        Database::insert('messages', $data + ['is_read' => 0, 'created_at' => now()]);
        if ($to = (string) setting('store_email')) {
            Mailer::sendTemplate($to, 'Yeni iletişim mesajı: ' . $data['subject'], 'Yeni iletişim mesajı',
                '<p><strong>' . e($data['name']) . '</strong> (' . e($data['email']) . ')</p><p>' . nl2br(e($data['message'])) . '</p>');
        }
        flash('success', 'Mesajınız bize ulaştı. En kısa sürede size dönüş yapacağız.');
        redirect('iletisim');
    }

    public function track(): void
    {
        $order = null;
        $searched = false;
        $no = trim((string) input('no', ''));
        $email = mb_strtolower(trim((string) input('email', '')));
        if ($no !== '' && $email !== '') {
            $searched = true;
            $order = Database::one('SELECT * FROM orders WHERE order_no = ? AND customer_email = ?', [strtoupper($no), $email]);
        }
        view('pages/track', [
            'pageTitle' => 'Sipariş Takibi',
            'order'     => $order,
            'searched'  => $searched,
            'history'   => $order ? Order::history((int) $order['id']) : [],
            'no'        => $no,
            'email'     => $email,
        ]);
    }

    public function newsletter(): void
    {
        $email = mb_strtolower(trim((string) input('email', '')));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $msg = 'Lütfen geçerli bir e-posta adresi girin.';
            is_ajax() ? json_response(['ok' => false, 'message' => $msg], 422) : flash('error', $msg);
            back();
        }
        if (!(int) Database::value('SELECT COUNT(*) FROM newsletter_subscribers WHERE email = ?', [$email])) {
            Database::insert('newsletter_subscribers', ['email' => $email, 'created_at' => now()]);
        }
        $msg = 'Bültenimize abone oldunuz. Kampanyalardan ilk siz haberdar olacaksınız!';
        if (is_ajax()) {
            json_response(['ok' => true, 'message' => $msg]);
        }
        flash('success', $msg);
        back();
    }
}
