<header class="site-header">
    <div class="site-header__notice">
        <div class="container">Тестовый сайт · оформление заказов пока недоступно</div>
    </div>
    <div class="container site-header__inner">
        <?php require __DIR__ . '/site-brand.php'; ?>
        <p class="site-header__description">Продажа, монтаж и техническое обслуживание систем безопасности объектов!</p>
        <nav class="site-header__nav" aria-label="Основная навигация">
            <?php require __DIR__ . '/site-nav-links.php'; ?>
        </nav>
        <details class="mobile-navigation" data-mobile-navigation>
            <summary class="mobile-navigation__toggle">Меню сайта<span class="mobile-navigation__icon" aria-hidden="true"></span></summary>
            <nav class="mobile-navigation__links" aria-label="Мобильная навигация">
                <?php require __DIR__ . '/site-nav-links.php'; ?>
            </nav>
        </details>
    </div>
</header>
<script type="module" src="/assets/js/mobile-navigation.js?v=day8"></script>
