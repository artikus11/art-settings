# Art Settings Component

Легковесная OOP-библиотека для быстрого создания страниц настроек в WordPress с поддержкой табов, секций, встроенной
валидацией и кастомизацией шаблонов.

## Возможности

* Поддержка двух форматов инициализации: **массивы (Array-driven)** и **объекты (DTO/OOP)**.
* Автоматическая обработка nonces, сохранения данных (`SettingsRepository`) и вывода уведомлений.
* Гибкий рендеринг: переопределение шаблонов через конфиг или WP-фильтры.
* Простая масштабируемость: создание собственных типов полей и секций.

---

## Установка (Установка через Composer из GitHub)

Добавьте репозиторий библиотеки в ваш `composer.json` и укажите зависимость:

```json
{
  "repositories": [
    {
      "type": "vcs",
      "url": "https://github.com/artikus11/art-settings.git"
    }
  ],
  "require": {
    "art/settings": "^1.0"
  }
}

```

Версия пакета берётся из git-тега, не из поля `"version"` в `composer.json`. Constraint `^1.0` пускает 1.x, мажор режет.

Затем выполните установку:

```bash
composer update art/settings

```

---

## Инициализация

### 1. Инициализация через массивы (Array Config)

Быстрый способ описать всю структуру настроек в виде одного конфигурационного массива.

```php
use Art\Settings\SettingsManager;
use Art\Settings\Fields\Text;
use Art\Settings\Fields\Checkbox;

add_action( 'plugins_loaded', function() {
    $config = [
        'option_key' => 'my_plugin_options',
        'menu'       => [
            'page_title'  => 'Настройки плагина',
            'menu_title'  => 'Мой Плагин',
            'menu_slug'   => 'my-plugin-settings',
            'capability'  => 'manage_options',
            'parent_slug' => 'options-general.php', // подменю в «Настройки»; без ключа — пункт верхнего уровня
            'position'    => 80, // опционально; для подменю — 7-й аргумент add_submenu_page (WP 5.3+)
            // 'position' => 'first' — пункт в начало списка подменю после регистрации;
            // 'position' => 'last'  — пункт в конец списка подменю (переживает поздние подменю);
            // 'position' => 80      — конкретный индекс вставки в конец хука admin_menu.
        ],
        'tabs' => [
            'general' => [
                'label'       => 'Основные',
                'save_button' => true,
                'sections'    => [
                    'main_section' => [
                        'title'       => 'Главные параметры',
                        'description' => 'Управление базовым функционалом.',
                        'fields'      => [
                            'api_key' => new Text( [
                                'label'       => 'API Ключ',
                                'description' => 'Введите ключ доступа.',
                                'default'     => '',
                            ] ),
                            'enable_cache' => new Checkbox( [
                                'label'   => 'Включить кеширование',
                                'default' => true,
                            ] ),
                        ],
                    ],
                ],
            ],
        ],
    ];

    $settings = new SettingsManager( $config );
    $settings->init();
} );

```

---

### 2. Инициализация через объекты (Object-Oriented)

Способ с четкой типизацией, автокомплитом в IDE и изолированным объявлением полей.

```php
use Art\Settings\SettingsManager;
use Art\Settings\Fields\Text;
use Art\Settings\Fields\Select;
use Art\Settings\Fields\Checkbox;

add_action( 'plugins_loaded', function() {
    $config = [
        'option_name' => 'my_plugin_options',
        'menu'        => [
            'page_title' => 'Настройки магазина',
            'menu_slug'  => 'shop-settings',
        ],
        'tabs' => [
            'general' => [
                'title'    => 'Общие',
                'sections' => [
                    'checkout' => [
                        'title'  => 'Оформление заказа',
                        'fields' => [
                            'currency' => new Select( [
                                'name'        => 'currency',
                                'label'       => 'Валюта по умолчанию',
                                'options'     => [
                                    'USD' => 'USD ($)',
                                    'EUR' => 'EUR (€)',
                                    'RUB' => 'RUB (₽)',
                                ],
                                'default'     => 'USD',
                            ] ),

                            'max_items' => new Text( [
                                'name'        => 'max_items',
                                'label'       => 'Лимит товаров в корзине',
                                'placeholder' => '10',
                            ] ),

                            'debug_mode' => new Checkbox( [
                                'name'  => 'debug_mode',
                                'label' => 'Включить режим отладки',
                            ] ),
                        ],
                    ],
                ],
            ],
        ],
    ];

    $manager = new SettingsManager( $config );
    $manager->init();
} );

```

### 3. Инициализация с объектами Табов

Если класс вклада реализует интерфейсы/методы get_sections(), get_label() и has_save_button(), SettingsManager
нормализует его автоматически.

```php
use Art\Settings\SettingsManager;
use Art\Settings\Fields\Select;

// Пример кастомного класса вкладки
class ShopTab {
    public function get_label(): string {
        return 'Магазин';
    }

    public function has_save_button(): bool {
        return true;
    }

    public function get_sections(): array {
        return [
            'checkout' => [
                'title'  => 'Оформление заказа',
                'fields' => [
                    'currency' => new Select( [
                        'label'   => 'Валюта по умолчанию',
                        'options' => [
                            'USD' => 'USD ($)',
                            'EUR' => 'EUR (€)',
                        ],
                        'default' => 'USD',
                    ] ),
                ],
            ],
        ];
    }
}

// Передача объекта вкладки в SettingsManager
add_action( 'plugins_loaded', function() {
    $config = [
        'option_key' => 'shop_plugin_options',
        'menu'       => [
            'page_title' => 'Настройки магазина',
            'menu_slug'  => 'shop-settings',
        ],
        'tabs' => [
            'shop_tab' => new ShopTab(),
        ],
    ];

    $manager = new SettingsManager( $config );
    $manager->init();
} );
```

## Поля библиотеки (Field Types)

Все поля наследуют `Art\Settings\Fields\Field` и принимают базовые аргументы `label`, `description`, `default`,
`attributes` (произвольные HTML-атрибуты: `placeholder`, `min`, `max`, `step`, `class` и т.д.).

### Text

```php
use Art\Settings\Fields\Text;

'api_key' => new Text( [
    'label'       => 'API Ключ',
    'description' => 'Введите ключ доступа.',
    'default'     => '',
    'attributes'  => [
        'placeholder' => 'sk-...',
        'class'       => 'regular-text',
    ],
] ),
```

### Textarea

```php
use Art\Settings\Fields\Textarea;

'about' => new Textarea( [
    'label'      => 'Описание',
    'default'    => '',
    'attributes' => [
        'rows' => 4,
        'cols' => 50,
    ],
] ),
```

### Number

```php
use Art\Settings\Fields\Number;

'batch_size' => new Number( [
    'label'      => 'Товаров за батч',
    'default'    => 500,
    'attributes' => [
        'min'  => 1,
        'max'  => 5000,
        'step' => 1,
    ],
] ),
```

### Select

```php
use Art\Settings\Fields\Select;

'currency' => new Select( [
    'label'   => 'Валюта',
    'options' => [
        'USD' => 'USD ($)',
        'EUR' => 'EUR (€)',
        'RUB' => 'RUB (₽)',
    ],
    'default' => 'USD',
] ),
```

### Radio

```php
use Art\Settings\Fields\Radio;

'layout' => new Radio( [
    'label'   => 'Раскладка',
    'options' => [
        'grid' => 'Сетка',
        'list' => 'Список',
    ],
    'default' => 'grid',
] ),
```

### Checkbox

```php
use Art\Settings\Fields\Checkbox;

'enable_cache' => new Checkbox( [
    'label'   => 'Включить кеширование',
    'default' => true,
] ),
```

### Toggle

Переключатель-слайдер (стили `.switch`).

```php
use Art\Settings\Fields\Toggle;

'cron_enabled' => new Toggle( [
    'label'   => 'Периодический запуск',
    'default' => false,
] ),
```

### ColorPicker

Использует встроенный интерфейс `wp-color-picker`, ассеты подключаются автоматически при рендере поля.

```php
use Art\Settings\Fields\ColorPicker;

'bg_color' => new ColorPicker( [
    'label'       => 'Цвет фона',
    'description' => 'Основной цвет фона для промо-блока.',
    'default'     => '#f3f4f6',
] ),
```

### Repeater

Повторяющиеся строки вложенных полей. Кнопка «Добавить» доступна сверху и снизу, строкам присваивается
`row_label` с порядковым номером. Под-поля раскладываются CSS Grid и переносятся на новые линии при нехватке
ширины (корректно работает даже в узкой ячейке `form-table`).

```php
use Art\Settings\Fields\Repeater;
use Art\Settings\Fields\Text;
use Art\Settings\Fields\Number;

'price_rules' => new Repeater( [
    'label'        => 'Правила цен',
    'row_label'    => 'Правило',
    'min_rows'     => 1,
    'max_rows'     => 10,
    'button_label' => 'Добавить правило',
    'fields'       => [
        'name'   => new Text( [ 'label' => 'Имя', 'default' => '' ] ),
        'amount' => new Number( [ 'label' => 'Сумма', 'default' => 0 ] ),
    ],
    'default' => [],
] ),
```

### Button

Кнопка-экшен с HTMX: отправляет `ast_action` в `admin-ajax` (`art_settings_htmx`), ответ `{ result: <mixed> }`
обрабатывается HTMX. Колбеки регистрируются в конфиге через `htmx_callbacks`.

```php
use Art\Settings\Fields\Button;

'sync_btn' => new Button( [
    'label'     => 'Запустить синхронизацию',
    'action'    => 'sync_now',
    'confirm'   => 'Запустить синхронизацию?',
    'css_class' => 'button-secondary',
] ),
```

Колбек в конфиге:

```php
'htmx_callbacks' => [
    'sync_now' => function( array $args ): array {
        // ... синхронизация ...
        return [ 'message' => 'Запущено' ];
    },
],
```

---

## Создание кастомного поля

Чтобы добавить новый тип поля (например, `Toggle Switch` или `ColorPicker`), создайте класс, наследующий
`Art\Settings\Fields\Field`, и укажите имя его шаблона.

### Класс поля (`src/Fields/ColorPicker.php`)

```php
namespace MyPlugin\Fields;

use Art\Settings\Fields\Field;

class ColorPicker extends Field {

    /**
     * Возвращает имя файла шаблона без расширения
     * Файл должен лежать в templates/fields/color-picker.php
     */
    public function get_template_name(): string {
        return 'color-picker';
    }

    /**
     * Кастомная санитаризация значения поля при сохранении
     */
    public function sanitize( mixed $value ): string {
        $value = sanitize_hex_color( (string) $value );
        return $value ?: $this->get_default();
    }
}

```

### Шаблон поля (`templates/fields/color-picker.php`)

```php
<?php
/**
 * @var \MyPlugin\Fields\ColorPicker $field
 * @var mixed                        $value
 */
?>
<div class="art-field-color-picker">
    <label for="<?php echo esc_attr( $field->get_id() ); ?>">
        <?php echo esc_html( $field->get_label() ); ?>
    </label>
    <input 
        type="color" 
        id="<?php echo esc_attr( $field->get_id() ); ?>" 
        name="<?php echo esc_attr( $field->get_name() ); ?>" 
        value="<?php echo esc_attr( $value ); ?>"
    />
    <?php if ( $field->get_description() ) : ?>
        <p class="description"><?php echo esc_html( $field->get_description() ); ?></p>
    <?php endif; ?>
</div>

```

---

## Кастомная секция (Custom Section Render)

Если вам нужно вывести секцию со сложной разметкой, интерактивными элементами или графиками, задайте параметр `callback`
в конфигурации секции.

```php
$config = [
    // ...
    'tabs' => [
        'dashboard' => [
            'title'    => 'Дашборд',
            'sections' => [
                'analytics' => [
                    'title'    => 'Статистика системы',
                    'callback' => function( array $section, array $saved_data, $renderer ) {
                        ?>
                        <div class="my-custom-analytics-section">
                            <h3><?php echo esc_html( $section['title'] ); ?></h3>
                            <p>Здесь выводится произвольная информация, не связанная со стандартными полями.</p>
                            <div class="stat-card">
                                <span>Всего записей:</span>
                                <strong><?php echo esc_html( $saved_data['total_count'] ?? 0 ); ?></strong>
                            </div>
                        </div>
                        <?php
                    },
                ],
            ],
        ],
    ],
];

```

---

### Кастомная секция с отдельным файлом шаблона

Если кастомная секция содержит сложный HTML и его нужно вынести из конфигурационного файла в отдельный шаблон, укажите в
`callback` подключение этого файла через `include`:

```php
$config = [
    'option_key' => 'my_options',
    'menu'       => [ 
        'page_title' => 'Настройки',
        'menu_slug'  => 'my-settings',
    ],
    'tabs'       => [
        'dashboard' => [
            'label'    => 'Дашборд',
            'sections' => [
                'analytics' => [
                    'title'         => 'Статистика системы',
                    'template_path' => MY_PLUGIN_DIR . 'templates/admin/sections/analytics.php',
                    'callback'      => function( array $section, array $saved_data ) {
                        $template = $section['template_path'] ?? '';
                        
                        if ( file_exists( $template ) ) {
                            include $template;
                        }
                    },
                ],
            ],
        ],
    ],
];

```

#### Файл шаблона секции (`templates/admin/sections/analytics.php`)

Внутри подключенного файла доступны переменные `$section` и `$saved_data`:

```php
<?php
/**
 * @var array $section    Массив конфигурации текущей секции
 * @var array $saved_data Все сохраненные опции текущего option_key
 */
?>
<div class="ast-custom-section-analytics">
    <h3><?php echo esc_html( $section['title'] ); ?></h3>
    
    <div class="ast-analytics-grid">
        <div class="ast-card">
            <h4>Текущий статус</h4>
            <p><?php echo esc_html( $saved_data['api_status'] ?? 'Неактивен' ); ?></p>
        </div>
    </div>
</div>

```

---

Логика изоляции вынесена в файл шаблона без засорения основного `$config`.

---

### Получение сохраненных значений в коде плагина

Пример того, как безопасно читать сохраненные данные в любом месте приложения через `SettingsRepository`.

```php
use Art\Settings\Repositories\SettingsRepository;

$repository = new SettingsRepository( 'my_plugin_options' );

// Получить все настройки массивом
$all_settings = $repository->get();

// Получить конкретное поле с фолбэком
$api_key = $all_settings['api_key'] ?? 'default_key';

```

### Переопределение шаблонов библиотеки из плагина

Объяснение фолбэк-механизма: как подменить дефолтный шаблон библиотеки (например, `layout.php` или `notice.php`) на
свой.

1. Укажите путь к вашим шаблонам в конфиге:

```php
'template_path' => plugin_dir_path( __FILE__ ) . 'templates/admin/settings',

```

2. Положите нужный файл в папку плагина:

```text
my-plugin/
└── templates/
    └── admin/
        └── settings/
            ├── layout.php          <-- Заменит стандартный layout библиотеки
            └── fields/
                └── text.php        <-- Заменит только шаблон текстового поля

```

Все отсутствующие шаблоны автоматически подгрузятся из каталога `vendor/art/settings/templates`.

---

## SCSS-кит дашборда (dashboard kit)

Общие элементы интерфейса дашборда, которые раньше копировались в каждый плагин
(`skl-dedup-scan`, `skl-title-uniq`, `skl-product-feed`): палитра, HTMX-база, тулбар
со статусом, карточки, метрики, стадии пайплайна, бейджи, пагинация, debug-панель,
info-карточки.

### Исходники

Модули лежат в `src/scss/dashboard/`:

| Модуль | Что содержит |
|--------|-------------|
| `variables` | Палитра `$color-*`, `$status-*`, `$breakpoint-*`, WP-палитра `$wp-*` |
| `base` | `@keyframes htmx-btn-spin`, `.htmx-indicator`, `.loading`, `.is-hidden` |
| `blocks/controls` | `.scan-controls` (тулбар + статус-точка + пульс), `.btn-global-start/stop` |
| `blocks/card` | `.scan-card`, `.scan-empty`, `.scan-legend` |
| `blocks/stats` | `.stat-grid`, `.stat-card` (variants `--success/--skip/--total/--link/--cta`) |
| `blocks/pipeline` | `.scan-pipeline`, `.stage` |
| `blocks/badge` | `.mode-badge` |
| `blocks/pagination` | `.pagination` |
| `blocks/debug` | `.debug-panel`, `.debug-card`, debug-кнопки |
| `blocks/info` | `.info-card` |

### Подключение через SCSS-исходники

Плагин подключает кит через `@use`, для этого в sass-loader добавляется
`includePaths` на `vendor/art/settings/src/scss`:

```js
// webpack.config.js
{ loader: 'sass-loader', options: { implementation: require( 'sass' ),
  sassOptions: { includePaths: [ path.resolve( __dirname, 'vendor/art/settings/src/scss' ) ] } } }
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

// Дальше плагин-специфичные блоки.
@use "blocks/plugin-specific";
```

Если нужно переопределить переменные — используйте `@use "dashboard/variables" with (...)`.

### Готовый CSS

Для плагинов, которые не собирают свои стили, собирается пребилт
`assets/css/ast-dashboard-style.min.css` (webpack entry `ast-dashboard-style`).
Подключить его можно, например, в `admin_enqueue_scripts` страницы настроек.

---

## Changelog

### 1.5.3

* Репитер: под-поля обёрнуты в `.ast__repeater-fields` (CSS Grid `auto-fill minmax(180px, 1fr)`) — поля не
  сжимаются ниже 180px и переносятся на новые линии при нехватке ширины (корректно в узкой ячейке `form-table`,
  2/3/5 полей в строке).
* Контролам в строке репитера задан `max-width: 100%`; кнопка удаления прижата вправо.

### 1.5.2

* Фикс: `box-sizing: border-box` на поддереве `.ast__repeater` — строка `width: 100%` + `padding`/рамка больше не
  вылезают за контейнер.
* Рефакторинг SCSS: `admin-style.scss` разбит на блоки `src/scss/blocks/` (`_header`, `_tabs`, `_body`, `_section`,
  `_status`, `_accordion`, `_actions`, `fields/_toggle`, `fields/_repeater`); входной файл — только `@use`-импорты.
  Скомпилированный CSS не изменился.

### 1.5.1

* Репитер: кнопка «Добавить» продублирована сверху и снизу строк.
* Лейбл строки (`row_label`) копируется при добавлении: в шаблонной строке рендерится `#__INDEX__`, JS
  перенумеровывает его в `#N`.
* Стили строк: поля растягиваются на всю ширину, лейбл над инпутом (меньший размер шрифта).
* `RepeaterRenderTest` — юнит-покрытие рендера (кнопки, лейбл, обёртки).

### 1.5.0

* Поле `Toggle` — переключатель-слайдер (`.switch`).
* Поле `Repeater` — повторяющиеся строки вложенных полей с JS-добавлением/удалением и реиндексацией
  `name`/`id`/`for`.
* Поле `Button` с HTMX: колбеки через `htmx_callbacks` в конфиге, ответ `{ result: <mixed> }`.
* Интеграция HTMX: webpack-бандл `ast-admin-htmx`, общий диспетчер HTMX-колбеков.

### 1.4.1
* дополнительные колнки для вкладок

### 1.4.0

* Перемещение подменю после регистрации: `menu.position` в конфиге теперь (кроме передачи в
  `add_submenu_page`/`add_menu_page`) сортирует пункт в `$submenu` на хуке `admin_menu` с приоритетом `PHP_INT_MAX`:
  * `'first'` — пункт в начало списка подменю;
  * `'last'` — пункт в конец (переживает подменю, добавленные другими плагинами позже в том же хуке);
  * `int >= 0` — пункт вставляется на конкретный индекс;
  * `null`/отсутствие ключа — без изменений (обратная совместимость).
* Только числовые позиции передаются в `add_submenu_page`/`add_menu_page`; строки (`'first'`/`'last'`) передаются как
  `null`, чтобы не провоцировать `_doing_it_wrong` — реордер выполняется на `admin_menu` `PHP_INT_MAX`.
* `SettingsManager` работает с `global $submenu` напрямую; отдельный `admin_menu` хук с
  `PHP_INT_MAX` и ручная перекладка `move_submenu_last` в плагине больше не нужны.
* PHPUnit-тесты перекладки: конец/начало/индекс, отсутствующий пункт, `last`/`first`/`int`, нормализация числа.
* `phpcs.xml`: отключен `WordPress.WP.GlobalVariablesOverride` — легитимная работа с `$submenu`.

### 1.3.2

* Хук шапки обертки: класс на основе `menu_slug` (`d0409ad`).

### 1.3.1

* Хуки шапки со скоупом `menu_slug`: свой блок info без проверки `page`.
* `<nav>` вкладок не рендерится, если вкладка одна.

**Добавлено**

| Хук                                  | Тип    | Когда                                                   |
|--------------------------------------|--------|---------------------------------------------------------|
| `ast_info_items_{$menu_slug}`        | фильтр | После глобального `ast_info_items`, только эта страница |
| `ast_before_info_items_{$menu_slug}` | экшен  | Перед списком info-пунктов, только эта страница         |
| `ast_after_info_items_{$menu_slug}`  | экшен  | После списка, только эта страница                       |

Пример: страница со slug `my-plugin-settings` — `ast_info_items_my-plugin-settings`.

**Удалено**

| Хук                     | Тип   | Почему                                                     |
|-------------------------|-------|------------------------------------------------------------|
| `ast_before_info_items` | экшен | Глобальный: любой плагин на art-settings видел чужой вывод |
| `ast_after_info_items`  | экшен | То же                                                      |

Глобальный фильтр `ast_info_items` **не** удалён: он по-прежнему применяется ко всем страницам библиотеки. Для своей
страницы используйте `ast_info_items_{$menu_slug}`.

### 1.3.0

* Сохранение мержит поля с текущей опцией: данные с неактивных вкладок не затираются. Снятый чекбокс пишется как
  `false`.
* Сброс через `SettingsRepository::reset()`, отдельный редирект `settings-reset`.
* `SanitizationService`: санитизация перед записью, декодирование при чтении, служебные ключи из опции вычищаются.
* `menu.position` передаётся и в `add_submenu_page` (WP 5.3+).
* Версия пакета больше не дублируется в `composer.json`; потребители ставят `"art/settings": "^1.0"`, цифра берётся из
  git-тега.
* PHPUnit-тесты.

### 1.2.0

* Хелперы репозитория: `get_string`, `get_int`, `get_bool` поверх `get_field_value`.

### 1.1.0

* Класс `ast` на `body` страницы настроек.
* Правки вывода полей.

### 1.0.0

* Каркас: `SettingsManager`, табы/секции, array- и object-конфиг.
* Поля: text, number, textarea, select, checkbox, radio, color picker.
* Шаблоны с фолбэком, кастомный `callback` секции, webpack-ассеты.