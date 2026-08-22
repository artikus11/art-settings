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

### Поле выбора цвета (`ColorPicker`)

Поле `ColorPicker` использует встроенный интерфейс `wp-color-picker` и не требует подключения внешних скриптов в
`SettingsManager`. Ассеты подключаются автоматически при рендере поля.

#### Инициализация в массиве секции

```php
use Art\Settings\Fields\ColorPicker;

'banner_styles' => [
    'title'  => 'Оформление баннера',
    'fields' => [
        'bg_color' => new ColorPicker( [
            'label'       => 'Цвет фона',
            'description' => 'Основной цвет фона для промо-блока.',
            'default'     => '#f3f4f6',
        ] ),
        'text_color' => new ColorPicker( [
            'label'       => 'Цвет текста',
            'description' => 'Цвет заголовка и основного текста.',
            'default'     => '#1f2937',
        ] ),
    ],
],
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

## Changelog

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