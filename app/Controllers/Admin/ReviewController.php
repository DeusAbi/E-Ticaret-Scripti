<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Database;

final class ReviewController extends AdminController
{
    public function index(): void
    {
        $filter = (string) input('durum', '');
        $where = match ($filter) {
            'bekleyen' => 'r.is_approved = 0',
            'onayli'   => 'r.is_approved = 1',
            default    => '1 = 1',
        };
        $this->render('reviews/index', [
            'pageTitle' => 'Ürün Yorumları',
            'filter'    => $filter,
            'pending'   => (int) Database::value('SELECT COUNT(*) FROM reviews WHERE is_approved = 0'),
            'result'    => $this->paginate(
                "SELECT COUNT(*) FROM reviews r WHERE $where",
                "SELECT r.*, p.name AS product_name, p.slug AS product_slug FROM reviews r
                 LEFT JOIN products p ON p.id = r.product_id WHERE $where ORDER BY r.is_approved ASC, r.id DESC",
                []
            ),
        ]);
    }

    public function approve(string $id): void
    {
        $review = Database::one('SELECT * FROM reviews WHERE id = ?', [(int) $id]);
        if ($review) {
            Database::update('reviews', ['is_approved' => (int) $review['is_approved'] ? 0 : 1], 'id = ?', [$review['id']]);
            flash('success', (int) $review['is_approved'] ? 'Yorum yayından kaldırıldı.' : 'Yorum onaylandı ve yayınlandı.');
        }
        back('admin/yorumlar');
    }

    public function destroy(string $id): void
    {
        Database::delete('reviews', 'id = ?', [(int) $id]);
        flash('success', 'Yorum silindi.');
        back('admin/yorumlar');
    }
}
