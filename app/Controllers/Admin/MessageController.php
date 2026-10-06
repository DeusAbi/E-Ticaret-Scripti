<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Database;

final class MessageController extends AdminController
{
    public function index(): void
    {
        $this->render('messages/index', [
            'pageTitle' => 'İletişim Mesajları',
            'result'    => $this->paginate('SELECT COUNT(*) FROM messages', 'SELECT * FROM messages ORDER BY is_read ASC, id DESC', []),
            'subscribers' => (int) Database::value('SELECT COUNT(*) FROM newsletter_subscribers'),
        ]);
    }

    public function show(string $id): void
    {
        $message = Database::one('SELECT * FROM messages WHERE id = ?', [(int) $id]);
        if (!$message) {
            abort(404);
        }
        if (!(int) $message['is_read']) {
            Database::update('messages', ['is_read' => 1], 'id = ?', [$message['id']]);
        }
        $this->render('messages/show', ['pageTitle' => 'Mesaj: ' . $message['subject'], 'message' => $message]);
    }

    public function destroy(string $id): void
    {
        Database::delete('messages', 'id = ?', [(int) $id]);
        flash('success', 'Mesaj silindi.');
        redirect('admin/mesajlar');
    }
}
