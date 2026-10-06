<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Services\Category;
use App\Services\Product;

final class HomeController extends Controller
{
    public function index(): void
    {
        view('home/index', [
            'sliders'     => Database::all('SELECT * FROM sliders WHERE is_active = 1 ORDER BY sort_order, id'),
            'categories'  => Category::tree(),
            'featured'    => Product::list('p.is_featured = 1', [], '(p.stock > 0) DESC, p.sales_count DESC', 8),
            'deals'       => Product::list('p.compare_price > p.price AND p.stock > 0', [], '(p.compare_price - p.price) / p.compare_price DESC', 4),
            'newArrivals' => Product::list('1 = 1', [], 'p.created_at DESC, p.id DESC', 8),
            'bestsellers' => Product::list('p.stock > 0', [], 'p.sales_count DESC', 4),
        ]);
    }
}
