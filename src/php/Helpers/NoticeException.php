<?php

namespace Art\Settings\Helpers;

/**
 * Исключение-носитель готового HTML-ответа для HTMX-колбеков.
 *
 * Контракт: message исключения — не текст для лога, а готовый HTML-фрагмент
 * (обычно Notice::html()), который catch-блок колбека возвращает как результат.
 *
 * Проблема: HTMX-колбек рендерится через ob_start() → буфер → return (JSON
 * { result } общего роута art_settings_htmx). Ошибка валидации на любом уровне
 * вложенности не может корректно завершить ответ: wp_die() внутри чужого буфера
 * обрывает рендер и отдаёт клиенту '0' вместо нотиса; протаскивание HTML через
 * return-значения промежуточных методов размывает сигнатуры.
 *
 * Решение: вложенный код бросает NoticeException с готовым notice-HTML —
 * catch на верхнем уровне (рендер колбека) чистит буфер и возвращает HTML.
 *
 * Пример:
 *
 *   try {
 *       $uuid = $this->get_donor_uuid(); // внутри: throw new NoticeException( Notice::html(...) )
 *   } catch ( NoticeException $e ) {
 *       return (string) $e->getMessage(); // HTML уходит в JSON { result }
 *   }
 *
 * Шаблон для skl-плагинов: не копировать локальные RenderException-классы —
 * использовать этот и рендерить message через Notice::html().
 */
final class NoticeException extends \Exception {
}
