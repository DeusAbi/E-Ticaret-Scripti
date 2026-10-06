<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Database;

final class Category
{
    private static ?array $all = null;

    public static function all(bool $onlyActive = true): array
    {
        if ($onlyActive && self::$all !== null) {
            return self::$all;
        }
        $rows = Database::all(
            "SELECT c.*, (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id AND p.status = 'active') AS product_count
             FROM categories c " . ($onlyActive ? 'WHERE c.is_active = 1 ' : '') . 'ORDER BY c.sort_order, c.name'
        );
        if ($onlyActive) {
            self::$all = $rows;
        }
        return $rows;
    }

    /** Üst kategoriler ve alt kategorileri ile ağaç yapısı */
    public static function tree(bool $onlyActive = true): array
    {
        $all = self::all($onlyActive);
        $children = [];
        foreach ($all as $c) {
            $children[(int) $c['parent_id']][] = $c;
        }
        $roots = [];
        foreach ($children[0] ?? [] as $root) {
            $root['children'] = $children[(int) $root['id']] ?? [];
            $roots[] = $root;
        }
        return $roots;
    }

    public static function findBySlug(string $slug): ?array
    {
        return Database::one('SELECT * FROM categories WHERE slug = ? AND is_active = 1', [$slug]);
    }

    /** Kategori ve tüm alt kategorilerinin id listesi */
    public static function descendantIds(int $id): array
    {
        $all = Database::all('SELECT id, parent_id FROM categories');
        $ids = [$id];
        $queue = [$id];
        while ($queue) {
            $current = array_shift($queue);
            foreach ($all as $c) {
                if ((int) $c['parent_id'] === $current && !in_array((int) $c['id'], $ids, true)) {
                    $ids[] = (int) $c['id'];
                    $queue[] = (int) $c['id'];
                }
            }
        }
        return $ids;
    }

    public static function breadcrumbs(?int $categoryId): array
    {
        $trail = [];
        $guard = 0;
        while ($categoryId && $guard++ < 10) {
            $c = Database::one('SELECT id, parent_id, name, slug FROM categories WHERE id = ?', [$categoryId]);
            if (!$c) {
                break;
            }
            array_unshift($trail, $c);
            $categoryId = $c['parent_id'] ? (int) $c['parent_id'] : null;
        }
        return $trail;
    }
}
