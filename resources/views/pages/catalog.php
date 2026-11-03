<?php
/** @var list<\Denors\Catalog\CategoryLink> $categories */
$categories = $data['categories'] ?? [];
// An unpopulated directory must not silently become a public placeholder page.
if (!is_array($categories) || !array_is_list($categories) || $categories === []) {
    throw new \InvalidArgumentException('Catalog requires populated category data.');
}
$paths = [];
foreach ($categories as $category) {
    if (!$category instanceof \Denors\Catalog\CategoryLink || isset($paths[$category->originalPath])) {
        throw new \InvalidArgumentException('Catalog requires distinct category links.');
    }
    $paths[$category->originalPath] = true;
}
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Каталог оборудования — Денорс</title>
    <meta name="description" content="Каталог оборудования систем безопасности Денорс. Выберите раздел каталога.">
    <?php require dirname(__DIR__) . '/components/site-styles.php'; ?>
    <link rel="stylesheet" href="/assets/css/catalog.css?v=day9">
</head>
<body class="site-page">
    <a class="skip-link" href="#main">К содержимому</a>
    <?php require dirname(__DIR__) . '/components/site-header.php'; ?>
    <main class="container catalog" id="main" tabindex="-1">
        <nav class="catalog__breadcrumbs" aria-label="Хлебные крошки">
            <ol><li><a href="/">Главная</a></li><li aria-current="page">Каталог оборудования</li></ol>
        </nav>
        <div class="catalog__heading stack">
            <p class="eyebrow muted">Оборудование</p>
            <h1>Каталог оборудования</h1>
            <p class="muted">Выберите раздел, чтобы перейти к оборудованию.</p>
        </div>
        <section aria-labelledby="catalog-sections-title">
            <h2 class="catalog__section-title" id="catalog-sections-title">Разделы каталога</h2>
            <ul class="catalog__grid" role="list">
                <?php foreach ($categories as $category): ?>
                    <li>
                        <a class="catalog__category" href="<?= $escape($category->originalPath) ?>">
                            <span><?= $escape($category->title) ?></span>
                            <svg class="catalog__arrow" width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>
        <aside class="catalog__help" aria-labelledby="catalog-help-title">
            <div class="stack"><h2 id="catalog-help-title">Нужна помощь с выбором?</h2><p class="muted">Обсудите задачу с компанией Денорс.</p></div>
            <a class="button" href="#site-contacts">Связаться с нами</a>
        </aside>
    </main>
    <?php require dirname(__DIR__) . '/components/site-footer.php'; ?>
</body>
</html>
