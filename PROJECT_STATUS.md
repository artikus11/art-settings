# PROJECT_STATUS.md

## 1. Исходные данные и контекст

* **Проект:** Модульная система настроек для WordPress-плагинов (`Art\Settings`).
* **Ключевые компоненты:**
* `SanitizationService` — рекурсивная санитизация данных перед записью в БД и декодирование спецсимволов/эмодзи при
  чтении.
* `SettingsRepository` — обёртка над `get_option` / `update_option` с кешированием в памяти и методами приведения
  типов (`get_string`, `get_int`, `get_bool`).
* `SettingsManager` — контроллер инициализации хуков WordPress, проверки прав, валидации nonce и обработки
  HTTP-запросов (сохранение/сброс).
* `PageRenderer` и шаблоны рендеринга страницы настроек, табов и секций.

---

## 2. Завершённые задачи (Текущий этап)

### 1.4.0 — перемещение подменю через `menu.position`

* [x] **Гарантированная позиция подменю после всех регистраций:** `SettingsManager::register_menu()` после
  `add_submenu_page()`/`add_menu_page()` вызывает `reorder_submenu( $menu )`, который перекладывает пункт в
  `$submenu` на `admin_menu`. Раньше порядок фиксировался в момент регистрации, и подменю, добавленные другими
  плагинами позже (например, Skl Promotion), оказывались ниже «Версий плагинов».
* [x] **Соглашение `menu.position`:** `'first'` — в начало; `'last'` — в конец; `int >= 0` — на конкретный индекс;
  `null`/без ключа — без изменений. Передаётся в `add_submenu_page`/`add_menu_page` и в `reorder_submenu`.
* [x] **Внутренние помощники:** `move_submenu_last/first/at` и `extract_submenu_item` — защищённые, работают с
  `global $submenu`; отсутствующий пункт оставляет массив нетронутым.
* [x] **PHPUnit:** 7 новых тестов перекладки (конец/начало/индекс, отсутствующий пункт, нулевая позиция, noop) —
  22 теста / 45 ассертов, зелёные.
* [x] **phpcs:** отключен `WordPress.WP.GlobalVariablesOverride` (легитимная работа с `$submenu`); новых ошибок
  относительно базлайна нет.

* [x] **Изоляция сохранения данных:** Переписана логика `SettingsManager::handle_actions` с разделением на 
  `process_update`
  и `process_reset`. Сохранение теперь работает по принципу объединения (`array_merge`) текущих настроек из БД и
  пришедших из формы полей, что предотвращает сброс настроек с неактивных вкладок.
* [x] **Корректный сброс настроек:** Реализована команда сброса опций через `SettingsRepository::reset()` с отдельным
  редиректом (`settings-reset=true`) и подтверждением действия на клиенте (`confirm`).
* [x] **Системный блок информации в шапке:** Добавлен вывод системной информации (`WP-Cron` / `System Cron`, версия
  плагина) через массив `$info_items` с возможностью расширения через кастомный экшен
  `do_action('ast_title_section_info')`.
* [x] **`position` для подменю:** В `SettingsManager::register_menu()` седьмой аргумент `add_submenu_page()` берётся из
  `$menu['position']` (как у `add_menu_page`). Без ключа передаётся `null` — поведение WP по умолчанию.
* [x] **Стратегия версий `^1.0`:** Поле `"version"` убрано из `composer.json`. Версия пакета для Composer берётся из
  git-тега, потребители ставят `"art/settings": "^1.0"`.
* [x] **Скоуп хуков шапки по странице:** Рядом с глобальными `ast_info_items` / `ast_before_info_items` /
  `ast_after_info_items` вызываются `ast_info_items_{$menu_slug}` и те же суффиксы для before/after. Плагин вешается на
  свой `menu_slug` и не фильтрует чужие страницы вручную. Глобальные хуки оставлены для совместимости.
* [x] **Одна вкладка без `<nav>`:** `templates/tabs.php` не рендерит навигацию при `count( $tabs ) < 2`. CSS-скрытие
  пустого враппера больше не нужно.

---

## 3. Ключевые технические решения

### Архитектура сохранения (`SettingsManager`)

1. **Обработка булевых полей:** Поля типов `Checkbox` и `Toggle` обновляются даже при их отсутствии в `$_POST` (когда
   чекбокс снят в HTML-форме), отправляя `null` в метод `sanitize()`, что гарантирует сброс значения в `false`.
2. **Делегирование роутинга:** Входной метод `handle_save()` проверяет только права и nonce, после чего передает
   управление в `process_update()` или `process_reset()`.

```php
// Принцип обновления плоского массива настроек в process_update:
$settings = $this->repository->get();

foreach ( $this->get_registered_fields() as $field_id => $field_object ) {
    $is_in_post = array_key_exists( $field_id, $_POST );
    $is_boolean = $field_object instanceof \Art\Settings\Fields\Checkbox
               || $field_object instanceof \Art\Settings\Fields\Toggle;

    if ( $is_in_post || $is_boolean ) {
        $raw_value            = $_POST[ $field_id ] ?? null;
        $settings[ $field_id ] = $field_object->sanitize( $raw_value );
    }
}

$this->repository->update( $settings );

```

### Меню (`register_menu`)

При непустом `parent_slug` вызывается `add_submenu_page()`. С WP 5.3 у неё есть `$position`; раньше конфиг `menu.position`
работал только у верхней страницы. Приоритет хука `admin_menu` по-прежнему 10, отдельного ключа в конфиге нет.

С 1.4.0 `menu.position` также сортирует пункт в `$submenu` в конце `register_menu()`: `'first'` / `'last'` / `int`.
Это делает позицию устойчивой к подменю, добавленным другими плагинами позже в тот же хук. Внутренние
`move_submenu_*` менять не нужно — они перекладывают пункт по слагу внутри $submenu родителя.

### Версии

Захардкоженная `"version": "1.3.0"` в `composer.json` ломает SemVer для VCS-установки: Composer игнорирует теги и отдаёт
одну цифру. Источник правды — git-тег. Constraint `^1.0` пускает патчи и миноры 1.x, мажор режет.

### Хуки шапки (`layout.php`)

Порядок: сначала общий фильтр/экшен, затем вариант с `menu_slug`. Потребитель без проверки `page` подписывается на
`ast_info_items_{$menu_slug}` (и `ast_before_info_items_{$menu_slug}` / `ast_after_info_items_{$menu_slug}`).

### Табы (`tabs.php`)

`<nav class="ast__tabs-wrapper">` выводится только если вкладок две и больше. Класс `ast__tabs-wrapper--empty` и правило
в SCSS не добавлялись: пустой nav в DOM не нужен.

---

## 4. Изменённые файлы

* `src/php/SettingsManager.php` — седьмой аргумент `add_submenu_page()`: `$menu['position'] ?? null`; новый
  `reorder_submenu()` и помощники `move_submenu_*`/`extract_submenu_item`.
* `tests/Unit/SettingsManagerTest.php` — 7 тестов перекладки подменю.
* `tests/Support/TestableSettingsManager.php` — раннеры `run_move_submenu_*` и `run_reorder_submenu`.
* `phpcs.xml` — отключен `WordPress.WP.GlobalVariablesOverride`.
* `composer.json` — удалено поле `"version"`.
* `composer.lock` — пересчитан `content-hash`, подтянулись dev-зависимости после `composer update`.
* `templates/layout.php` — второй `apply_filters( "ast_info_items_{$menu_slug}" )`; `do_action` before/after с тем же
  суффиксом.
* `templates/tabs.php` — early return, если вкладок меньше двух.

---

## 5. Результаты проверки

* **Переключение вкладок:** При сохранении формы на Табе А данные с Таба Б не стираются из базы данных.
* **Снятие чекбоксов:** Отключение чекбокса корректно записывает `false` в соответствующий ключ массива настроек.
* **Сброс:** Кнопка «Сбросить настройки» очищает опцию в БД до пустого массива `[]`.
* **Подменю:** `position` из конфига `menu` доходит до `add_submenu_page()` и (с 1.4.0) сортирует пункт в `$submenu`
  — пункт стабильно последний/первый/на заданном индексе после всех регистраций.
* **Composer:** в `composer.json` нет `"version"`; установка по `^1.0` заработает только после появления git-тега `1.x`.
* **Хуки шапки:** фильтр `ast_info_items_{menu_slug}` меняет блок только на своей странице; глобальный `ast_info_items`
  по-прежнему виден всем потребителям библиотеки.
* **Одна вкладка:** `<nav>` в разметке нет, отдельный CSS не требуется.

---

## 6. Отклоненные / Несработавшие подходы

* **Фильтрация полей строго по текущему табу при сохранении (`get_fields_by_tab`):** Избыточная логика для системы с
  единым плоским массивом настроек. Отклонено в пользу проверки наличия ключей в `$_POST` в сочетании с явной проверкой
  типов булевых полей (`instanceof Checkbox`).

---

## 7. Следующие шаги разработки (Backlog)

1. **Тег релиза 1.x:** без git-тега constraint `^1.0` из VCS-репозитория не резолвится, остаётся только `dev-master`.

2. **Унификация HTML-атрибутов в рендере полей:**

* Заложить в базовый класс поля / рендерер поддержку всех стандартных HTML-атрибутов (`placeholder`, `disabled`,
  `readonly`, `required`, `min`, `max`, `step`, `pattern`, `autocomplete`, `data-*` атрибуты) для всех базовых типов (
  `text`, `number`, `textarea`, `select`).


3. **Добавление компонента `Toggle` (Свитч):**

* Создать класс поля `Toggle` на базе стандартного чекбокса с кастомной UI-оберткой (CSS-переключатель).
* Добавить проверку `instanceof Toggle` в `SettingsManager::process_update` для корректной обработки состояния `false`.


4. **Добавление типа поля `Multiselect`:**

* Реализовать класс поля и рендер тега `<select multiple>`.
* Обеспечить санитизацию входящего массива значений и корректное сохранение/чтение.


5. **Добавление составных полей (`MultiCheckbox` и `MultiToggle`):**

* Реализовать поля с множественным выбором (группа чекбоксов или свитчей).
* Использовать их для глобальных настроек (например, выбор списка типов записей `get_post_types()`, таксономий или ролей
  пользователей).
* Обеспечить корректную санитизацию массива выбранных ключей (сброс в пустой массив, если не выбрано ни одного
  элемента).