# Art Settings — UI-кит дашборда и настройки

Единый стандарт классов для плагинов на базе `art-settings`. Всё, что нужно подключать, брать из либы, а не копировать в
плагин.

---

## 1. Стандарт имён классов

BEM с префиксом `ast`:

| Часть       | Форма                    | Пример                  |
|-------------|--------------------------|-------------------------|
| Блок        | `ast__<block>`           | `.ast__controls`        |
| Элемент     | `ast__<block>-<element>` | `.ast__stat-card-label` |
| Модификатор | `--<modifier>`           | `.ast__alert--warning`  |

Исключения — системные классы HTMX (`.htmx-indicator`, `.htmx-request`), их имя задаёт сам htmx, переименовывать нельзя.

---

## 2. Подключение

SCSS-исходники лежат в `vendor/art/settings/src/scss`. В sass-loader плагина добавьте `includePaths` на эту папку и
подключайте нужные модули через `@use`.

```scss
// webpack.config.js
sassOptions: {
  includePaths: [ path.resolve( __dirname, 'vendor/art/settings/src/scss' ) ],
}
```

```scss
// src/scss/admin-style.scss
@use "dashboard/variables" as *;
@use "dashboard/base";
@use "dashboard/blocks/controls";
@use "dashboard/blocks/card";
@use "dashboard/blocks/stats";
@use "dashboard/blocks/badge";
@use "dashboard/blocks/pagination";
@use "dashboard/blocks/table";

// Плагин-специфичные блоки.
@use "blocks/my-plugin";
```

Если плагин не собирает свои стили — можно подключить пребилт
`assets/css/ast-dashboard-style.min.css` (entry `ast-dashboard-style`).

---

## 3. Общие правила применения

- **Не переопределяйте классы либы в плагине.** Если нужен другой внешний вид — добавьте плагинный модификатор рядом,
  например
  `class="ast__stat-card ast__stat-card--my-variant"`.
- **Отступы**: блоки и таблицы идут с фиксированным нижним отступом, внешние отступы сверху плагин не задаёт (кроме
  `.ast__journal` — у него
  `margin-top`).
- **Keyframes** (`htmx-btn-spin`, `loading-pulse`, `shake`) уже определены в либе — повторно не объявлять.
- **`.htmx-indicator` / `.htmx-request`** — служебные классы htmx, стилизуются только в `dashboard/base`; в плагине их
  не переопределять.
- **Пустые состояния** — всегда `ast__empty` (заголовок `ast__empty-title`, описание `ast__empty-desc`), а не отдельные
  плагинные классы.
- **Тулбар над таблицей** — `ast__table-bar` (кнопки, селекты, пагинация в одну строку с переносом).

---

## 4. Каталог компонентов

### Базовое

| Класс                                     | Назначение                         |
|-------------------------------------------|------------------------------------|
| `.ast__loading` / `.ast__loading-spinner` | Блок загрузки (спиннер WP + текст) |
| `.ast__is-hidden`                         | Скрыть элемент (`display: none`)   |
| `.htmx-indicator`                         | Индикатор запроса htmx (служебный) |

### Тулбар со статусом

| Класс                                              | Назначение                                      |
|----------------------------------------------------|-------------------------------------------------|
| `.ast__controls`                                   | Панель «Диспетчер»: инфо слева, действия справа |
| `.ast__controls-info`                              | Обёртка статус-точки + текста                   |
| `.ast__controls-status` / `--running`, `--stopped` | Статус-точка (пульс при работе)                 |
| `.ast__controls-title` / `-desc`                   | Заголовок и описание                            |
| `.ast__controls-actions`                           | Контейнер кнопок                                |
| `.ast__btn-start` / `.ast__btn-stop`               | Зелёная / красная кнопка запуска-остановки      |

### Карточки, пустые состояния, легенда

| Класс                                                              | Назначение                                                     |
|--------------------------------------------------------------------|----------------------------------------------------------------|
| `.ast__card`                                                       | Белая карточка с заголовком и контентом                        |
| `.ast__card-header` / `-info` / `-heading` / `-title` / `-scan-id` | Шапка карточки                                                 |
| `.ast__card-meta` / `-block` / `-label` / `-value`                 | Мета-пары «метка: значение»                                    |
| `.ast__empty`                                                      | Пустое состояние (title/desc)                                  |
| `.ast__legend`                                                     | Легенда цветов статусов (dot `--pending/--processing/--error`) |

### Метрики

| Класс                                                  | Назначение                                   |
|--------------------------------------------------------|----------------------------------------------|
| `.ast__stat-grid`                                      | Сетка 4 колонки (2 на планшете)              |
| `.ast__stat-card`                                      | Карточка метрики: `-label`, `-value`, `-sub` |
| `.ast__stat-card--success/--skip/--total/--link/--cta` | Варианты подсветки                           |

### Пайплайн

| Класс                                                  | Назначение                                         |
|--------------------------------------------------------|----------------------------------------------------|
| `.ast__pipeline`                                       | Сетка стадий (3 колонки)                           |
| `.ast__stage`                                          | Стадия: `-number`, `-name`, `-progress`, `-metric` |
| `.ast__stage--processing` / `--has-data`               | Состояния стадии                                   |
| `.ast__stage-metric--pulse/--has-errors/--has-skipped` | Подсветка метрик                                   |
| `.ast__stage-metric--bold`                             | Выделение всей строки метрики (жирный текст)      |

> Примечание: внутри `.ast__stage-metric` label и value — голые `span`'ы, flex `space-between`
> раскладывает их сам. Элементные классы (`-label`/`-value`) в либу **не вводятся**; жирное
> начертание значения — через `--bold` на всей строке метрики, а не на value отдельно.

### Бейджи и статусы

| Класс                                           | Назначение                               |
|-------------------------------------------------|------------------------------------------|
| `.ast__badge` / `--batch`, `--sync`             | Режим работы (uppercase-пилюля)          |
| `.ast__badge--bootstrap`                        | Импорт товара (WP-палитра orange)        |

### Пагинация

| Класс                   | Назначение                 |
|-------------------------|----------------------------|
| `.ast__pagination`      | Контейнер кнопок пагинации |
| `.ast__pagination-gap`  | Разделитель «…»            |
| `.ast__pagination-meta` | Счётчик «Стр. N из M (K)»  |

### Таблицы

| Класс                     | Назначение                                    |
|---------------------------|-----------------------------------------------|
| `.ast__table-bar`         | Тулбар над таблицей (фильтры/кнопки)          |
| `.ast__table-wrap`        | Скролл-обёртка + нижний отступ                |
| `.ast__table-data`        | Широкая таблица, ячейки `vertical-align: top` |
| `.ast__table-data--fixed` | То же с `table-layout: fixed`                 |
| `.ast__journal`           | Блок журнала: `-title` + таблица              |

### Фильтры

| Класс                           | Назначение                                                                         |
|---------------------------------|------------------------------------------------------------------------------------|
| `.ast__filter-panel` / `-title` | Панель «Фильтры»                                                                   |
| `.ast__filter-grid`             | Сетка полей (2 колонки), `-search`/`-full` на всю ширину                           |
| `.ast__filter-field`            | Поле: label + контрол колонкой                                                     |
| `.ast__range`                   | Двойной слайдер min/max: `-caption`, `-min-input`, `-max-input`, `-track`, `-fill` |

### Инфо-блоки и прогресс

| Класс                                   | Назначение                                                       |
|-----------------------------------------|------------------------------------------------------------------|
| `.ast__alert--info/--warning/--error`   | Цветные уведомления                                              |
| `.ast__progress` / `.ast__progress-bar` | Прогресс-бар                                                     |
| `.ast__info`                            | Информационная страница                                          |

### Скелетоны

| Класс                                              | Назначение                                                          |
|----------------------------------------------------|---------------------------------------------------------------------|
| `.ast__skeleton`                                   | Shimmer-база заглушки (градиент + анимация + скругление)            |
| `.ast__skeleton--title` / `--icon` / `--button`    | Заглушки шапки/футера: 200×24 / 20×20 / 100×35                      |
| `.ast__skeleton-row`                               | Строка тела (`flex`, `gap: 20px`)                                   |
| `.ast__skeleton-cell--text` / `--input`            | Широкие ячейки строки (`flex: 1`, высота 30px)                      |
| `.ast__skeleton-cell--number`                      | Узкая ячейка номера (`flex: 0 0 30px`)                              |
| `.ast__skeleton-modal` / `-header` / `-footer`     | Структура партиала `templates/parts/skeleton.php`                   |

Партиал `templates/parts/skeleton.php` (параметры `rows` default 5, `with_number` default false, `id`,
`class`) рендерит только «строки с ячейками» — оверлей HTMX (`.htmx-request`) и модальная обёртка остаются зоной
плагина. Keyframe `shimmer-animation` определён в либе (`src/scss/keyframes.scss`) — повторно не объявлять.

Пример использования с `hx-indicator` (паттерн feed):

```php
// В кастомном шаблоне плагина, через PageRenderer::render_template()
$renderer->render_template( 'parts/skeleton.php', [
    'rows'        => 15,
    'with_number' => true,
    'id'          => 'sklpf-mapping-skeleton',
    'class'       => 'htmx-indicator',
] );
```

### Уведомления

Единый рендер нотисов — `Art\Settings\Helpers\Notice::html()`. Заменяет собственные копии `View::notice()` в
плагинах: контракт поверх `templates/notice.php` (классы `ast__notice`, `notice-*`, `is-dismissible`).

```php
use Art\Settings\Helpers\Notice;

echo Notice::html( 'Товары синхронизированы.' );                                  // success (по умолчанию)
echo Notice::html( 'Не удалось сохранить.', 'error', [ 'dismissible' => false ] );
echo Notice::html( "Строка 1\nСтрока 2", 'warning', [ 'allow_html' => true ] );    // многострочные, nl2br
echo Notice::html( 'Загружено', 'info', [ 'oob_target' => '#notices' ] );          // HTMX OOB
```

| Параметр | Тип | Default | Назначение |
|----------|-----|---------|------------|
| `$message` | `string` | — | Текст сообщения |
| `$type` | `string` | `success` | `success\|error\|warning\|info` → класс-модификатор |
| `$args['strong']` | `bool` | `true` | Оборачивать сообщение в `<strong>` |
| `$args['dismissible']` | `bool` | `true` | Кнопка закрытия `.notice-dismiss` + класс `is-dismissible` |
| `$args['oob_target']` | `string` | `''` | Атрибут `hx-swap-oob="afterbegin:<target>"` для HTMX OOB |
| `$args['allow_html']` | `bool` | `false` | Пропустить сообщение через `wp_kses_post` (многострочные `nl2br`) |

По умолчанию сообщение экранируется (`esc_html`). Кастомный шаблон подхватывается через фильтр
`art_settings_template_path` с фолбэком на шаблон либы.

### Отладка

| Класс                                  | Назначение                                                    |
|----------------------------------------|---------------------------------------------------------------|
| `.ast__debug`                          | Корень вкладки «Отладка»                                      |
| `.ast__debug-warning`                  | Жёлтое предупреждение вверху                                  |
| `.ast__debug-grid`                     | Сетка стадий (3 колонки)                                      |
| `.ast__debug-card` / `-card-step`      | Карточка стадии + номер шага                                  |
| `.ast__debug-divider` / `-action-divider` | Разделитель блоков / внутри ряда кнопок                    |
| `.ast__debug-tools`                    | Сетка 1fr/3fr: ссылки слева, сброс справа                     |
| `.ast__debug-links` / `-links-list`    | Блок «Быстрый переход»                                        |
| `.ast__debug-reset` / `-reset-desc`    | Колонка «Сервисное обслуживание и сброс»                      |
| `.ast__debug-actions`                  | Ряд кнопок (wrap)                                             |

Кнопки отладки:

| Класс                                | Назначение                                              |
|--------------------------------------|---------------------------------------------------------|
| `.ast__debug-button--run`            | Запуск стадии (на карточке в `-grid`)                   |
| `.ast__debug-button--danger`         | Мягкая деструктивная кнопка (сброс одной области)       |
| `.ast__debug-button--reset`          | Полный сброс (красная)                                  |

Правила:
- Каждый деструктивный action — только с `hx-confirm`.
- Стадии рендерятся карточками в `ast__debug-grid`, не списком.
- Кнопки сброса группируются в `ast__debug-actions`; между логическими
  группами — `ast__debug-action-divider`.
- Блоки разделяются `<hr class="ast__debug-divider">`.

---

## 5. Пример разметки (журнал с фильтрами и пагинацией)

```html

<div class="ast__journal">
	<h3 class="ast__journal-title">Журнал изменений</h3>
	
	<div class="ast__table-bar">
		<select name="status">
			<option>Все записи</option>
			<option>Удалено</option>
		</select>
		<nav class="ast__pagination">
			<button type="button"
			        class="button">1
			</button>
			<button type="button"
			        class="button">2
			</button>
			<span class="ast__pagination-meta">Стр. 1 из 5 (120)</span>
		</nav>
	</div>
	
	<div class="ast__table-wrap">
		<table class="widefat striped ast__table-data">
			<thead>
				<tr>
					<th>Дата</th>
					<th>Товар</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>2026-10-01</td>
					<td>#420665</td>
				</tr>
			</tbody>
		</table>
	</div>
</div>
```

---

## 6. Пример вкладки «Отладка»

```html
<div class="ast__debug">
	<div class="ast__debug-warning">
		Кнопки ниже ставят стадию в Action Scheduler. Если скан уже завершён,
		он будет снова переведён в running.
	</div>

	<div class="ast__debug-grid">
		<div class="ast__debug-card">
			<span class="ast__debug-card-step">Шаг 1</span>
			<button type="button"
				class="button ast__debug-button--run"
				hx-post="https://site/wp-admin/admin-ajax.php"
				hx-vals='{"action":"debug_run_stage","stage":"normalize","nonce":"…"}'
				hx-target="#notices"
				hx-swap="none">
				Запустить<br> «Нормализация»
			</button>
		</div>
	</div>

	<hr class="ast__debug-divider">

	<div class="ast__debug-tools">
		<div class="ast__debug-links">
			<h4>Быстрый переход</h4>
			<ul class="ast__debug-links-list">
				<li>
					<a href="https://site/wp-admin/admin.php?page=wc-status&tab=action-scheduler"
						target="_blank"
						class="dashicons-before dashicons-list-view">
						Просмотр крон-задач
					</a>
				</li>
			</ul>
		</div>

		<div class="ast__debug-reset">
			<h4>Сервисное обслуживание и сброс</h4>
			<p class="ast__debug-reset-desc">
				Выберите область очистки. Действия необратимы.
			</p>
			<div class="ast__debug-actions">
				<button type="button"
					class="button button-secondary ast__debug-button--danger"
					hx-post="https://site/wp-admin/admin-ajax.php"
					hx-confirm="Очистить данные сканов?">
					Очистить метрики
				</button>
				<hr class="ast__debug-divider ast__debug-action-divider">
				<button type="button"
					class="button-link-delete ast__debug-button--reset"
					hx-post="https://site/wp-admin/admin-ajax.php"
					hx-confirm="ВНИМАНИЕ! Очистить всё. Продолжить?">
					ПОЛНЫЙ СБРОС
				</button>
			</div>
		</div>
	</div>
</div>
```

---

## 7. Что НЕ трогать

- Классы плагинов с собственным префиксом (`skl-*`, `dedup-*`, `sklpf-*`)
  — они остаются в плагине, пока плагин не переведён на кит.
- `.htmx-indicator` / `.htmx-request` — служебные для htmx.
- WP-классы (`widefat`, `striped`, `notice`, `button`) — стилизует ядро.