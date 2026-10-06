<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Database;

final class Product
{
    public const STATUSES = [
        'active'  => 'Yayında',
        'draft'   => 'Taslak',
        'passive' => 'Pasif',
    ];

    /** Kartlarda kullanılan ortak SELECT (kapak görseli ve puan ortalaması ile) */
    public const CARD_SELECT = "p.*, c.name AS category_name, c.slug AS category_slug,
        (SELECT path FROM product_images i WHERE i.product_id = p.id ORDER BY i.sort_order, i.id LIMIT 1) AS image,
        (SELECT path FROM product_images i WHERE i.product_id = p.id ORDER BY i.sort_order, i.id LIMIT 1 OFFSET 1) AS image_alt,
        (SELECT AVG(rating) FROM reviews r WHERE r.product_id = p.id AND r.is_approved = 1) AS rating_avg,
        (SELECT COUNT(*) FROM reviews r WHERE r.product_id = p.id AND r.is_approved = 1) AS rating_count";

    public static function findActive(int $id): ?array
    {
        return Database::one("SELECT * FROM products WHERE id = ? AND status = 'active'", [$id]);
    }

    public static function findBySlug(string $slug): ?array
    {
        return Database::one(
            'SELECT ' . self::CARD_SELECT . " FROM products p LEFT JOIN categories c ON c.id = p.category_id
             WHERE p.slug = ? AND p.status = 'active'",
            [$slug]
        );
    }

    public static function images(int $productId): array
    {
        return Database::all('SELECT * FROM product_images WHERE product_id = ? ORDER BY sort_order, id', [$productId]);
    }

    /**
     * Mağaza listeleme sorgusu (filtre, sıralama, sayfalama)
     * @return array{items: array, total: int, page: int, pages: int, per_page: int}
     */
    public static function search(array $f, int $page = 1, int $perPage = 12): array
    {
        $where = ["p.status = 'active'"];
        $params = [];

        if (!empty($f['category_ids'])) {
            $where[] = 'p.category_id IN (' . Database::placeholders(count($f['category_ids'])) . ')';
            array_push($params, ...$f['category_ids']);
        }
        if (!empty($f['q'])) {
            $where[] = '(p.name LIKE ? OR p.brand LIKE ? OR p.sku LIKE ? OR p.short_description LIKE ?)';
            $like = '%' . $f['q'] . '%';
            array_push($params, $like, $like, $like, $like);
        }
        if (!empty($f['brands'])) {
            $where[] = 'p.brand IN (' . Database::placeholders(count($f['brands'])) . ')';
            array_push($params, ...$f['brands']);
        }
        if (isset($f['min']) && $f['min'] !== '') {
            $where[] = 'p.price >= ?';
            $params[] = (float) $f['min'];
        }
        if (isset($f['max']) && $f['max'] !== '') {
            $where[] = 'p.price <= ?';
            $params[] = (float) $f['max'];
        }
        if (!empty($f['in_stock'])) {
            $where[] = 'p.stock > 0';
        }
        if (!empty($f['on_sale'])) {
            $where[] = 'p.compare_price > p.price';
        }
        if (!empty($f['featured'])) {
            $where[] = 'p.is_featured = 1';
        }

        $order = match ($f['sort'] ?? '') {
            'price_asc'  => 'p.price ASC',
            'price_desc' => 'p.price DESC',
            'newest'     => 'p.created_at DESC, p.id DESC',
            'bestseller' => 'p.sales_count DESC, p.id DESC',
            'rating'     => 'rating_avg DESC, rating_count DESC',
            'name'       => 'p.name ASC',
            default      => 'p.is_featured DESC, p.sales_count DESC, p.id DESC',
        };

        $whereSql = implode(' AND ', $where);
        $total = (int) Database::value("SELECT COUNT(*) FROM products p WHERE $whereSql", $params);
        $pages = max(1, (int) ceil($total / $perPage));
        $page = min(max(1, $page), $pages);
        $offset = ($page - 1) * $perPage;

        $items = Database::all(
            'SELECT ' . self::CARD_SELECT . " FROM products p LEFT JOIN categories c ON c.id = p.category_id
             WHERE $whereSql ORDER BY (p.stock > 0) DESC, $order LIMIT " . (int) $perPage . ' OFFSET ' . (int) $offset,
            $params
        );

        return ['items' => $items, 'total' => $total, 'page' => $page, 'pages' => $pages, 'per_page' => $perPage];
    }

    public static function list(string $condition, array $params = [], string $order = 'p.id DESC', int $limit = 8): array
    {
        return Database::all(
            'SELECT ' . self::CARD_SELECT . " FROM products p LEFT JOIN categories c ON c.id = p.category_id
             WHERE p.status = 'active' AND ($condition) ORDER BY $order LIMIT " . (int) $limit,
            $params
        );
    }

    public static function related(array $product, int $limit = 4): array
    {
        return self::list(
            'p.category_id = ? AND p.id <> ?',
            [$product['category_id'], $product['id']],
            '(p.stock > 0) DESC, p.sales_count DESC',
            $limit
        );
    }

    public static function brands(array $categoryIds = []): array
    {
        $sql = "SELECT brand, COUNT(*) AS cnt FROM products WHERE status = 'active' AND brand IS NOT NULL AND brand <> ''";
        $params = [];
        if ($categoryIds) {
            $sql .= ' AND category_id IN (' . Database::placeholders(count($categoryIds)) . ')';
            $params = $categoryIds;
        }
        return Database::all($sql . ' GROUP BY brand ORDER BY brand', $params);
    }

    public static function stockState(array $p): array
    {
        $stock = (int) $p['stock'];
        if ($stock <= 0) {
            return ['key' => 'out', 'label' => 'Tükendi'];
        }
        if ($stock <= (int) ($p['low_stock_threshold'] ?? 5)) {
            return ['key' => 'low', 'label' => "Son $stock ürün"];
        }
        return ['key' => 'in', 'label' => 'Stokta'];
    }

    /** "Renk: Siyah" satırlarını [anahtar => değer] dizisine dönüştürür */
    public static function parseSpecs(?string $specs): array
    {
        $result = [];
        foreach (preg_split('/\r\n|\r|\n/', (string) $specs) as $line) {
            if (!str_contains($line, ':')) {
                continue;
            }
            [$k, $v] = array_map('trim', explode(':', $line, 2));
            if ($k !== '' && $v !== '') {
                $result[$k] = $v;
            }
        }
        return $result;
    }

    public static function uniqueSlug(string $name, ?int $exceptId = null, string $table = 'products'): string
    {
        $base = slugify($name) ?: 'urun';
        $slug = $base;
        $i = 2;
        while (true) {
            $sql = "SELECT COUNT(*) FROM $table WHERE slug = ?";
            $params = [$slug];
            if ($exceptId) {
                $sql .= ' AND id <> ?';
                $params[] = $exceptId;
            }
            if ((int) Database::value($sql, $params) === 0) {
                return $slug;
            }
            $slug = $base . '-' . $i++;
        }
    }
}
