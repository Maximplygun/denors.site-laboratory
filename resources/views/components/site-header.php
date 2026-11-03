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
        <button class="mobile-navigation__toggle" type="button" aria-label="Открыть меню" aria-expanded="false" aria-controls="mobile-menu" hidden>
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
        </button>
        <dialog class="mobile-navigation" id="mobile-menu" aria-labelledby="mobile-menu-title">
            <div class="mobile-navigation__panel">
                <div class="mobile-navigation__heading">
                    <h2 id="mobile-menu-title">Меню</h2>
                    <button class="mobile-navigation__close" type="button" aria-label="Закрыть меню" autofocus>
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    </button>
                </div>
                <div class="mobile-navigation__links"></div>
                <div class="mobile-navigation__footer"><a class="button" href="#site-contacts">Связаться с нами</a></div>
            </div>
        </dialog>
    </div>
</header>
<script type="module" src="/assets/js/mobile-navigation.js?v=offcanvas1"></script>
