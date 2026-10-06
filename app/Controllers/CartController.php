<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Services\Cart;

final class CartController extends Controller
{
    public function index(): void
    {
        $summary = Cart::summary();
        foreach ($summary['warnings'] as $w) {
            \App\Core\Session::flashNow('warning', $w);
        }
        view('cart/index', ['pageTitle' => 'Sepetim', 'summary' => $summary]);
    }

    public function add(): void
    {
        $id = (int) input('product_id');
        $qty = max(1, (int) input('qty', 1));
        $error = Cart::add($id, $qty);

        if (is_ajax()) {
            $summary = Cart::summary();
            json_response([
                'ok'      => $error === null || Cart::quantityOf($id) > 0,
                'message' => $error ?? 'Ürün sepetinize eklendi.',
                'count'   => Cart::count(),
                'total'   => money($summary['total']),
                'mini'    => partial('partials/mini-cart', ['summary' => $summary]),
            ], $error !== null && Cart::quantityOf($id) === 0 ? 422 : 200);
        }

        $error ? flash('warning', $error) : flash('success', 'Ürün sepetinize eklendi.');
        if (input('buy_now') === '1' && Cart::quantityOf($id) > 0) {
            redirect('odeme');
        }
        back('sepet');
    }

    public function update(): void
    {
        $error = Cart::update((int) input('product_id'), (int) input('qty'));
        if (is_ajax()) {
            $summary = Cart::summary();
            json_response([
                'ok'      => true,
                'message' => $error,
                'count'   => Cart::count(),
                'html'    => partial('cart/content', ['summary' => $summary]),
                'mini'    => partial('partials/mini-cart', ['summary' => $summary]),
            ]);
        }
        if ($error) {
            flash('warning', $error);
        }
        redirect('sepet');
    }

    public function remove(): void
    {
        Cart::remove((int) input('product_id'));
        if (is_ajax()) {
            $summary = Cart::summary();
            json_response([
                'ok'    => true,
                'count' => Cart::count(),
                'html'  => partial('cart/content', ['summary' => $summary]),
                'mini'  => partial('partials/mini-cart', ['summary' => $summary]),
            ]);
        }
        flash('success', 'Ürün sepetinizden çıkarıldı.');
        redirect('sepet');
    }

    public function applyCoupon(): void
    {
        $error = Cart::applyCoupon((string) input('code', ''));
        $error ? flash('error', $error) : flash('success', 'Kupon kodu uygulandı.');
        back('sepet');
    }

    public function removeCoupon(): void
    {
        Cart::removeCoupon();
        flash('success', 'Kupon kaldırıldı.');
        back('sepet');
    }
}
