<?php
$product = $data['product'] ?? null;
if (!$product instanceof \Denors\Catalog\ProductDetails) {
    throw new \InvalidArgumentException('Product presentation is required.');
}
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title><?= $escape($product->title) ?> — Денорс</title>
    <meta name="description" content="<?= $escape($product->description !== '' ? $product->description : $product->title) ?>">
    <?php require dirname(__DIR__) . '/components/site-styles.php'; ?>
    <link rel="stylesheet" href="/assets/css/product.css?v=day10">
</head>
<body class="site-page">
    <a class="skip-link" href="#main">К содержимому</a>
    <?php require dirname(__DIR__) . '/components/site-header.php'; ?>
    <main class="container product" id="main" tabindex="-1">
        <nav class="product__breadcrumbs" aria-label="Хлебные крошки"><a href="/">Главная</a><span aria-hidden="true">/</span><span aria-current="page"><?= $escape($product->title) ?></span></nav>
        <div class="product__overview">
            <div>
                <?php if ($product->images !== []): ?>
                    <div class="product__gallery" tabindex="0" role="region" aria-label="Изображения товара. Прокрутка по горизонтали">
                        <?php foreach ($product->images as $index => $image): ?>
                            <figure class="product__photo"><img src="<?= $escape($image) ?>" alt="<?= $escape($product->title . ' — изображение ' . ($index + 1)) ?>" width="640" height="640" <?= $index > 0 ? 'loading="lazy"' : 'fetchpriority="high"' ?>></figure>
                        <?php endforeach; ?>
                    </div>
                    <?php if (count($product->images) > 1): ?><p class="product__hint">Изображений: <?= count($product->images) ?>. Прокрутите галерею вправо или используйте клавиши ← →.</p><?php endif; ?>
                <?php else: ?>
                    <div class="product__image-empty">Изображение не добавлено</div>
                <?php endif; ?>
            </div>
            <div class="product__summary stack">
                <p class="eyebrow muted">Оборудование</p>
                <h1><?= $escape($product->title) ?></h1>
                <?php if ($product->sku !== null && $product->sku !== ''): ?><p class="muted">Артикул: <?= $escape($product->sku) ?></p><?php endif; ?>
                <div class="product__purchase stack">
                    <p class="product__price"><?= $escape($product->formattedPrice() ?? 'Цена не указана') ?></p>
                    <p class="muted">Уточните наличие и условия приобретения у компании Денорс.</p>
                    <a class="button" href="#site-contacts">Связаться по товару</a>
                </div>
                <div class="product__sections"><a href="#product-description">Описание</a><?php if ($product->specifications !== []): ?><a href="#product-specifications">Характеристики</a><?php endif; ?></div>
            </div>
        </div>
        <section class="product__section stack" id="product-description" aria-labelledby="product-description-title" tabindex="-1">
            <h2 id="product-description-title">Описание</h2>
            <p class="product__description"><?= $escape(trim($product->description) !== '' ? $product->description : 'Описание пока не добавлено.') ?></p>
        </section>
        <?php if ($product->specifications !== []): ?>
            <section class="product__section stack" id="product-specifications" aria-labelledby="product-specifications-title" tabindex="-1">
                <h2 id="product-specifications-title">Характеристики</h2>
                <dl class="product__specifications"><?php foreach ($product->specifications as $name => $value): ?><div><dt><?= $escape($name) ?></dt><dd><?= $escape($value) ?></dd></div><?php endforeach; ?></dl>
            </section>
        <?php endif; ?>
    </main>
    <?php require dirname(__DIR__) . '/components/site-footer.php'; ?>
</body>
</html>
