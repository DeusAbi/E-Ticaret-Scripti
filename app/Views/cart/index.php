<div class="container">
    <div class="page-head">
        <?= partial('partials/breadcrumbs', ['items' => [['Sepetim', null]]]) ?>
        <div class="page-title"><h1>Alışveriş Sepetim</h1></div>
    </div>
    <div data-cart-content>
        <?= partial('cart/content', ['summary' => $summary]) ?>
    </div>
</div>
