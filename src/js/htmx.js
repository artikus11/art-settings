/**
 * HTMX: подключается на страницах настроек art-settings.
 * Ожидает глобальный `htmx` для совместимости с hx-атрибутами кнопок.
 */
import 'htmx.org/dist/htmx.js';

window.htmx = window.htmx || ( typeof htmx !== 'undefined' ? htmx : null );

/**
 * art/settings: общий разворачиватель JSON-конверта HTMX-роута art_settings_htmx.
 *
 * HtmxHandler отвечает { data: { result: <mixed> } }; HTML-фрагменты колбеков
 * потребителя обёрнуты в result.html — разворачиваем до свапа htmx.
 *
 * Разворачивание сделано ПЕРЕХВАТОМ XHR, а не через htmx:beforeSwap: когда ответы
 * поллинга и кнопки приходят в одной гонке, htmx диспатчит beforeSwap на УЖЕ
 * ОТОРВАННОМ таргете (его заменил первый ответ) — событие на оторванном элементе
 * не всплывает до body, конверт не разворачивается, и htmx парсит сырой JSON как
 * HTML (битые hx-swap-oob селекторы, htmx:oobErrorNoTarget, свап мусора).
 *
 * result.oob (HTML уведомлений, если есть) вставляется в .ast__notices здесь же,
 * до htmx-обработки. Потребители возвращают oob как ОТДЕЛЬНОЕ поле (НЕ конкатенацией
 * со встроенным hx-swap-oob внутри html) — OOB-парсинг htmx внутри JSON-конверта
 * ненадёжен по той же причине.
 *
 * Потребительские beforeSwap-хендлеры (result.html unwrap) после этого не нужны;
 * собственные поля result (например queue_active) читаются как раньше.
 */
( function () {
	'use strict';

	const ROUTE_MARKER = 'action=art_settings_htmx';

	function insertOob( oobHtml ) {
		if ( 'string' !== typeof oobHtml || '' === oobHtml.trim() ) {
			return;
		}

		const container = document.querySelector( '.ast__notices' );

		if ( container ) {
			container.insertAdjacentHTML( 'afterbegin', oobHtml );
		}
	}

	function unwrapResult( xhr ) {
		const raw = xhr.responseText;

		if ( ! raw || '{' !== raw.charAt( 0 ) ) {
			return;
		}

		let result;

		try {
			result = JSON.parse( raw ).data.result;
		} catch ( e ) {
			return;
		}

		if ( ! result || 'object' !== typeof result || null === result ) {
			return;
		}

		if ( 'oob' in result ) {
			insertOob( result.oob );
		}

		const html = result.html;

		if ( 'string' === typeof html ) {
			try {
				Object.defineProperty( xhr, 'response', {
					configurable: true,
					get() {
						return html;
					},
				} );
				Object.defineProperty( xhr, 'responseText', {
					configurable: true,
					get() {
						return html;
					},
				} );
			} catch ( e ) {
				// responseText не переопределяется — htmx получит сырой JSON,
				// фолбэк beforeSwap ниже подстрахует.
			}
		}
	}

	const origOpen = XMLHttpRequest.prototype.open;

	XMLHttpRequest.prototype.open = function ( method, url ) {
		const xhr = this;

		if ( String( url || '' ).indexOf( ROUTE_MARKER ) !== -1 ) {
			xhr.addEventListener( 'load', function () {
				unwrapResult( xhr );
			} );
		}

		return origOpen.apply( this, arguments );
	};

	/**
	 * Фолбэк для случаев, когда перехват не переопределил response (не-JSON,
	 * ошибка defineProperty): разворачиваем на beforeSwap как раньше. Двойной
	 * вставки oob нет — перехват уже вставил его до этого события.
	 */
	document.body.addEventListener( 'htmx:beforeSwap', function ( evt ) {
		const detail = evt.detail || {};
		const xhr = detail.xhr;

		if ( ! xhr || ! xhr.responseText ) {
			return;
		}

		const raw = xhr.responseText;

		if ( '' === raw || '{' !== raw.charAt( 0 ) ) {
			return; // уже развёрнуто перехватом или это обычный HTML
		}

		let parsed;

		try {
			parsed = JSON.parse( raw );
		} catch ( e ) {
			return;
		}

		if (
			parsed &&
			parsed.data &&
			typeof parsed.data.result === 'object' &&
			parsed.data.result !== null
		) {
			const result = parsed.data.result;

			if ( 'oob' in result ) {
				insertOob( result.oob );
			}

			const html = result.html;

			if ( 'string' === typeof html ) {
				detail.serverResponse = html;
			}
		}
	} );
}() );
