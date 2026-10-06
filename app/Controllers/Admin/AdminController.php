<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Core\Auth;

abstract class AdminController extends Controller
{
    public function __construct()
    {
        Auth::requireAdmin();
    }

    protected function render(string $template, array $data = []): void
    {
        view('admin/' . $template, $data, 'layouts/admin');
    }

    /** Admin listeleri için sayfalama yardımcı fonksiyonu */
    protected function paginate(string $countSql, string $selectSql, array $params, int $perPage = 20): array
    {
        $total = (int) \App\Core\Database::value($countSql, $params);
        $pages = max(1, (int) ceil($total / $perPage));
        $page = min(max(1, (int) input('sayfa', 1)), $pages);
        $items = \App\Core\Database::all($selectSql . ' LIMIT ' . $perPage . ' OFFSET ' . (($page - 1) * $perPage), $params);
        return ['items' => $items, 'total' => $total, 'page' => $page, 'pages' => $pages, 'per_page' => $perPage];
    }
}
