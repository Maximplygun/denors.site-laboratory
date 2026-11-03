<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Денорс — тестовое окружение</title>
    <link rel="stylesheet" href="/assets/css/fonts.css?v=day5">
    <link rel="stylesheet" href="/assets/css/tokens.css?v=day5">
    <link rel="stylesheet" href="/assets/css/base.css?v=day5">
    <link rel="stylesheet" href="/assets/css/components.css?v=day5">
    <link rel="stylesheet" href="/assets/css/design-preview.css?v=day5">
</head>
<body>
    <a class="skip-link" href="#main">К содержимому</a>
    <main class="container design-preview" id="main" tabindex="-1">
        <div class="design-preview__intro">
            <div class="design-preview__meta">
                <span class="eyebrow">Денорс / дизайн-система</span>
                <span class="badge">День 5 · на согласовании</span>
            </div>
            <h1>Ясность в каждой детали.</h1>
            <p class="design-preview__lead">Тестовое окружение Денорс. Сайт находится в разработке.
                Здесь собраны базовые элементы будущего интерфейса.</p>
        </div>
        <div class="design-preview__grid">
            <section class="panel stack design-preview__wide" aria-labelledby="palette-title">
                <p class="eyebrow muted">01 / Цвет</p>
                <h2 id="palette-title">Спокойная основа. Точный акцент.</h2>
                <p class="muted">Предлагаемая палитра для согласования: светлые поверхности, тёмный текст и бирюзовые действия.</p>
                <ul class="design-preview__swatches">
                    <li><div class="design-preview__swatch design-preview__swatch--brand"></div><strong>Основной</strong><br><code>#075E51</code></li>
                    <li><div class="design-preview__swatch design-preview__swatch--text"></div><strong>Текст</strong><br><code>#182C29</code></li>
                    <li><div class="design-preview__swatch design-preview__swatch--canvas"></div><strong>Фон</strong><br><code>#F4F6F5</code></li>
                    <li><div class="design-preview__swatch design-preview__swatch--accent"></div><strong>Акцент</strong><br><code>#D9EDAA</code></li>
                </ul>
            </section>
            <section class="panel stack" aria-labelledby="type-title">
                <p class="eyebrow muted">02 / Типографика</p>
                <h2 id="type-title">Читается с первого взгляда</h2>
                <p>Onest — единая гарнитура для заголовков и основного текста. Размер текста учитывает настройки браузера.</p>
                <p class="muted">Второстепенный текст сохраняет контраст. Длина строки и интервалы помогают читать на небольшом экране.</p>
                <a href="#controls-title">Перейти к элементам управления</a>
                <div class="notice">Цвет дополняет смысл: состояние всегда сопровождается текстом.</div>
            </section>
            <section class="panel stack" aria-labelledby="controls-title">
                <p class="eyebrow muted">03 / Действия</p>
                <h2 id="controls-title">Понятные приоритеты</h2>
                <p class="muted">Ссылки ведут к образцам полей. Отключённая кнопка показывает недоступное действие.</p>
                <div class="design-preview__actions">
                    <a class="button" href="#fields-title">Основное действие</a>
                    <a class="button button--secondary" href="#fields-title">Второстепенное</a>
                    <button class="button" type="button" disabled>Недоступно</button>
                </div>
            </section>
            <section class="panel stack design-preview__wide" aria-labelledby="fields-title">
                <p class="eyebrow muted">04 / Поля и состояния</p>
                <h2 id="fields-title">Удобный ввод, заметные подсказки</h2>
                <p class="muted">Образцы интерфейса: данные никуда не отправляются и не сохраняются.</p>
                <div class="design-preview__grid">
                    <div class="field">
                        <label class="field__label" for="sample-normal">Обычное поле</label>
                        <input class="field__control" id="sample-normal" type="text" placeholder="Введите текст" aria-describedby="sample-hint" autocomplete="off">
                        <p class="field__hint" id="sample-hint">Подсказка связана с полем.</p>
                    </div>
                    <div class="field">
                        <label class="field__label" for="sample-error">Поле с ошибкой — образец</label>
                        <input class="field__control" id="sample-error" type="text" aria-invalid="true" aria-describedby="sample-error-text" autocomplete="off">
                        <p class="field__error" id="sample-error-text">Пример ошибки: заполните поле.</p>
                    </div>
                </div>
                <label class="check"><input type="checkbox"><span>Образец выбора с доступной подписью</span></label>
            </section>
        </div>
    </main>
</body>
</html>
