# День 5 — дизайн-система

Статус: REVIEW. Реализация готова локально; визуальное согласование владельцем
и проверка доставки статики на Timeweb ожидаются. Это основа компонентов,
а не главная магазина, header/footer или товарная карточка.

## Организация

CSS подключается в порядке fonts → tokens → base → components → design-preview.
Файлы находятся в public/assets/css/. Общие файлы можно использовать на будущих
страницах; design-preview.css обслуживает только служебную страницу образцов.
Новых маршрутов нет: образцы показаны на существующей staging-главной `/`.
Версия ресурсов в ссылках `?v=day5` должна меняться при следующих изменениях CSS.
Сборщик, JS и внешние runtime-зависимости не требуются.

Токены: цвет, типографика, отступы 4/8/12/16/24/32/48/64 px при базовых 16 px,
радиусы, тень и контейнер до 1200 px. Размеры текста используют rem и clamp;
контейнер имеет боковые поля, grid переходит в одну колонку при ширине ≤48rem.
Общие компоненты: .button (primary/secondary/disabled), .panel, .badge,
.notice, .field и .check. Использовать существующие классы и переменные,
стили страницы ограничивать её префиксом. Не создавать глобальные переопределения.

## Предлагаемая палитра

В исходном ТЗ точные коды не заданы. Следующие значения предложены для согласования;
реализация Дня 5 не означает утверждение фирменной палитры владельцем.

| Назначение | Цвет |
| --- | --- |
| Фон | #F4F6F5 |
| Поверхность | #FFFFFF |
| Основной текст | #182C29 |
| Второстепенный текст | #526660 |
| Основное действие | #075E51 |
| Hover действия | #03483E |
| Мягкий фон | #E5F1ED |
| Декоративный акцент | #D9EDAA |
| Ошибка | #A92332 |
| Фокус | #1769BA |

Расчётные контрасты: основной текст/фон 13.52:1; muted/white 6.13:1;
brand/white 7.70:1; error/white 7.06:1; focus/white 5.58:1.
Светлая граница panel декоративная; границы полей и вторичной кнопки контрастнее.
Состояние ошибки сопровождается текстом и aria-invalid/aria-describedby.

## Шрифт

Onest variable, 100–900, normal, font-display: swap; системный sans-serif fallback.
Файл обслуживается локально: public/assets/fonts/onest-variable.ttf (193056 байт).
Источник: [Google Fonts / Onest](https://github.com/google/fonts/tree/main/ofl/onest).
Лицензия SIL OFL 1.1 сохранена рядом в OFL-Onest.txt. Получено 03.11.2026.
SHA-256: `966c5c29b4755da84b6854d5c21dd4eaa2420225d0e9874de602de176d4a9f31`.
Нет внешних запросов к Google Fonts из HTML/CSS. Inter отдельно не загружается:
для текущей системы выбрана одна из разрешённых ТЗ гарнитур.

## Доступность и границы

Один h1, секции с h2, lang=ru, viewport без запрета масштабирования.
Есть skip-link, видимый focus-visible, элементы управления высотой от 44–48 px,
связанные label и подсказки, нативный checkbox и disabled.
Учитываются prefers-reduced-motion и forced-colors для фокуса.
Образцы не образуют форму, не отправляют запросы и не сохраняют введённый текст.
Их статическая ошибка показывает стиль, а не бизнес-валидацию будущего магазина.

Noindex и HTTP-контракт сохранены. Canonical, товары, цены и коммерческие тексты
не создавались. 404-шаблон не изменён. Дни 6 и далее не реализованы.

## Проверка на Timeweb владельцем

Из корня проекта в Git Bash, ветка feature/design-system, чистая рабочая папка:

```bash
git status --short
git branch --show-current
denors check
denors
bash tests/http-staging.sh
git rev-parse HEAD
curl -q --fail --show-error https://denors.site-laboratory.ru/denors-release.txt
curl -q --fail --show-error -I https://denors.site-laboratory.ru/assets/css/tokens.css?v=day5
curl -q --fail --show-error -I https://denors.site-laboratory.ru/assets/fonts/onest-variable.ttf
```

denors публикует текущий коммит на staging и отправляет ветку в GitHub.
Требуются PASS Linux self-test, успешный deploy, совпадение release marker,
успех HTTP-проверок, 200 для CSS/font и Content-Type text/css для CSS.
Откройте https://denors.site-laboratory.ru/ с обновлением кэша.

В DevTools Network проверить 200 всех пяти CSS и Onest, отсутствие внешних
запросов шрифта; в Console — отсутствие ошибок. На ширинах 320, 375, 768 и
1440 px проверить отсутствие горизонтальной прокрутки, перенос кнопок и полей.
Проверить Tab/Shift+Tab, skip-link, видимый фокус, checkbox клавишей Space,
масштаб 200% и reduced motion. Образцы не должны отправлять данные.
Выполнить Lighthouse в Mobile и Desktop; сохранить отчёты accessibility,
performance и best practices. SEO-аудит будет видеть намеренный noindex staging:
его нельзя удалять ради оценки. Это не подтверждение полевых Core Web Vitals.

## Проверки реализации

PHP lint шаблона, 15 application и 748 routing checks прошли.
Локальная HTTP-регрессия: 13 проверок + /.env 404; CSS HTTP 200/text/css,
шрифт HTTP 200 с корректной TTF-сигнатурой. Ссылки ресурсов и контраст проверены.
Браузер: просмотр 1280/375 px, контроль переполнения на 320/375/768/1440 px,
поля, checkbox и клавиатурный фокус; ошибок/warnings консоли не обнаружено.
Lighthouse, полноценная проверка screen reader, 200% zoom и forced-colors
не выполнены. Доставка новой версии на Timeweb в этой задаче не выполнялась.
