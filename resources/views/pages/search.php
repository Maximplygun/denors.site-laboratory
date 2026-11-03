<?php
$query = $data['query'] ?? null;
$result = $data['result'] ?? null;
$action = $data['action'] ?? '';
if (!$query instanceof \Denors\Catalog\SearchQuery || !is_array($result)) throw new \InvalidArgumentException('Search presentation is required.');
\Denors\Catalog\OriginalPath::validate($action);
$values = $data['values'] ?? $query->parameters();
$value = static fn (string $key): string => isset($values[$key]) && is_string($values[$key]) ? $values[$key] : '';
$pageUrl = static fn (int $page): string => $action . '?' . http_build_query($query->parameters() + ['page' => $page], '', '&', PHP_QUERY_RFC3986);
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Поиск оборудования — Денорс</title>
    <?php require dirname(__DIR__) . '/components/site-styles.php'; ?>
    <link rel="stylesheet" href="/assets/css/search.css?v=day11">
</head>
<body class="site-page">
    <a class="skip-link" href="#main">К содержимому</a>
    <?php require dirname(__DIR__) . '/components/site-header.php'; ?>
    <main class="container search" id="main" tabindex="-1">
        <div class="stack search__heading"><p class="eyebrow muted">Оборудование</p><h1>Поиск оборудования</h1><p class="muted">Найдите товар по названию, артикулу или описанию.</p></div>
        <?php if (isset($data['error'])): ?><div class="search__error" id="search-error" role="alert"><?= $escape($data['error']) ?></div><?php endif; ?>
        <form class="search__form" action="<?= $escape($action) ?>" method="get" role="search" aria-label="Поиск и фильтры оборудования" <?= isset($data['error']) ? 'aria-describedby="search-error"' : '' ?>>
            <div class="field search__query"><label class="field__label" for="search-q">Что ищем?</label><input class="field__control" id="search-q" name="q" type="search" maxlength="120" value="<?= $escape($value('q')) ?>" placeholder="Название или артикул"></div>
            <fieldset class="search__prices"><legend>Цена, ₽</legend>
                <div class="field"><label for="search-min">От</label><input class="field__control" id="search-min" name="min" inputmode="decimal" type="text" value="<?= $escape($value('min')) ?>" aria-describedby="price-hint"></div>
                <div class="field"><label for="search-max">До</label><input class="field__control" id="search-max" name="max" inputmode="decimal" type="text" value="<?= $escape($value('max')) ?>" aria-describedby="price-hint"></div>
                <p class="field__hint" id="price-hint">Необязательно. Рубли и копейки, например 1250,50.</p>
            </fieldset>
            <div class="field"><label class="field__label" for="search-sort">Сортировка</label><select class="field__control" name="sort" id="search-sort"><?php foreach (['default' => 'Исходный порядок', 'price_asc' => 'Цена: по возрастанию', 'price_desc' => 'Цена: по убыванию', 'name' => 'По названию'] as $key => $label): ?><option value="<?= $escape($key) ?>" <?= $value('sort') === $key ? 'selected' : '' ?>><?= $escape($label) ?></option><?php endforeach; ?></select></div>
            <div class="search__actions"><button class="button" type="submit">Найти</button><a class="button button--secondary" href="<?= $escape($action) ?>">Сбросить</a></div>
        </form>
        <?php if (!isset($data['error'])): ?>
            <section class="search__results" aria-labelledby="search-results-title">
                <h2 id="search-results-title">Найдено товаров: <?= $result['total'] ?></h2>
                <?php if ($result['total'] === 0): ?><div class="search__empty stack"><h3>Ничего не найдено</h3><p class="muted">Измените запрос или диапазон цены.</p><a href="<?= $escape($action) ?>">Сбросить условия поиска</a></div>
                <?php else: ?><ul class="search__grid" role="list"><?php foreach ($result['items'] as $item): ?><li class="search__item stack"><h3><a href="<?= $escape($item->originalPath) ?>"><?= $escape($item->product->title) ?></a></h3><?php if ($item->product->sku !== null): ?><p class="muted">Артикул: <?= $escape($item->product->sku) ?></p><?php endif; ?><p class="search__price"><?= $escape($item->product->formattedPrice() ?? 'Цена не указана') ?></p></li><?php endforeach; ?></ul><?php endif; ?>
                <?php if ($result['pages'] > 1): ?><nav class="search__pagination" aria-label="Страницы результатов">
                    <?php if ($result['page'] > 1): ?><a class="button button--secondary" href="<?= $escape($pageUrl($result['page'] - 1)) ?>">Предыдущая</a><?php endif; ?>
                    <span>Страница <?= $result['page'] ?> из <?= $result['pages'] ?></span>
                    <?php if ($result['page'] < $result['pages']): ?><a class="button button--secondary" href="<?= $escape($pageUrl($result['page'] + 1)) ?>">Следующая</a><?php endif; ?>
                </nav><?php endif; ?>
            </section>
        <?php endif; ?>
    </main>
    <?php require dirname(__DIR__) . '/components/site-footer.php'; ?>
</body>
</html>
