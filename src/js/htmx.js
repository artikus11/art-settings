/**
 * HTMX: подключается на страницах настроек art-settings.
 * Ожидает глобальный `htmx` для совместимости с hx-атрибутами кнопок.
 */
import 'htmx.org/dist/htmx.js';

window.htmx = window.htmx || ( typeof htmx !== 'undefined' ? htmx : null );

export default window.htmx;