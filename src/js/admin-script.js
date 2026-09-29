/**
 * art-settings: поведение полей на странице настроек.
 * Vanilla JS, без jQuery-зависимости для новых фич.
 */
( function () {
	'use strict';

	const INDEX_PLACEHOLDER = '__INDEX__';
	const DEBUG_FIX = Boolean( window.astSettingsDebug );

	function logFix( message, data ) {
		if ( DEBUG_FIX && window.console ) {
			window.console.log( '[FIX]', message, data );
		}
	}

	/**
	 * Экспонирует текущий индекс строки в name/id/for атрибутах вложенных полей.
	 * Паттерн: {fieldId}[{oldIndex}][{subFieldId}]
	 */
	function reindexRow( row, fieldId, newIndex ) {
		const escapedFieldId = fieldId.replace( /[.*+?^${}()|[\]\\]/g, '\\$&' );
		const pattern = new RegExp( escapedFieldId + '\\[(\\d+)\\]' );

		const rows = Array.prototype.filter.call(
			row.querySelectorAll( '[name], [id], [for]' ),
			( el ) => {
				const value = el.getAttribute( 'name' ) || el.getAttribute( 'id' ) || el.getAttribute( 'for' );
				return pattern.test( value );
			}
		);

		rows.forEach( ( el ) => {
			[ 'name', 'id', 'for' ].forEach( ( attr ) => {
				const value = el.getAttribute( attr );
				if ( ! value ) {
					return;
				}
				el.setAttribute( attr, value.replace( pattern, () => fieldId + '[' + newIndex + ']' ) );
			} );
		} );
	}

	/**
	 * Собирает строки контейнера и перенумеровывает индексы по порядку.
	 */
	function renumberRows( container, fieldId ) {
		const rows = Array.prototype.filter.call(
			container.querySelectorAll( '[data-repeater-row]' ),
			( el ) => ! el.closest( '[data-repeater-template]' )
		);

		rows.forEach( ( row, index ) => {
			reindexRow( row, fieldId, index );
			const label = row.querySelector( '.ast__repeater-row-label' );
			if ( label ) {
				label.textContent = label.textContent.replace( /#(?:__INDEX__|\d+)$/, '#' + ( index + 1 ) );
			}
		} );
	}

	/**
	 * Клонирует скрытую шаблонную строку и вставляет новую с актуальным индексом.
	 */
	function addRow( container ) {
		const fieldId = container.dataset.fieldId;
		const maxRows = container.dataset.maxRows;
		const currentRows = Array.prototype.filter.call(
			container.querySelectorAll( '[data-repeater-row]' ),
			( el ) => ! el.closest( '[data-repeater-template]' )
		).length;

		if ( maxRows && currentRows >= parseInt( maxRows, 10 ) ) {
			return;
		}

		const template = container.querySelector( '[data-repeater-template] [data-repeater-row]' );
		if ( ! template ) {
			return;
		}

		const clone = template.cloneNode( true );
		clone.removeAttribute( 'hidden' );
		clone.classList.remove( 'ast__repeater-template' );

		// Замена плейсхолдера индекса на актуальный во всех name/id/for.
		[ clone, ...clone.querySelectorAll( '[name], [id], [for]' ) ].forEach( ( el ) => {
			[ 'name', 'id', 'for' ].forEach( ( attr ) => {
				const value = el.getAttribute( attr );
				if ( value && value.includes( INDEX_PLACEHOLDER ) ) {
					el.setAttribute( attr, value.replace( INDEX_PLACEHOLDER, String( currentRows ) ) );
				}
			} );
		} );

		container.querySelector( '[data-repeater-rows]' ).appendChild( clone );
		renumberRows( container, fieldId );
		logFix( 'Repeater row added', { fieldId, rowIndex: currentRows } );
	}

	/**
	 * Удаляет строку (не ниже min_rows).
	 */
	function removeRow( container, row ) {
		const minRows = parseInt( container.dataset.minRows || '1', 10 );
		const fieldId = container.dataset.fieldId;

		const rows = Array.prototype.filter.call(
			container.querySelectorAll( '[data-repeater-row]' ),
			( el ) => ! el.closest( '[data-repeater-template]' )
		);

		if ( rows.length <= minRows ) {
			return;
		}

		row.remove();
		renumberRows( container, fieldId );
		logFix( 'Repeater row removed', { fieldId } );
	}

	function initRepeaters() {
		document.addEventListener( 'click', ( event ) => {
			const addButton = event.target.closest( '[data-repeater-add]' );
			if ( addButton ) {
				const container = addButton.closest( '.ast__repeater' );
				if ( container ) {
					addRow( container );
				}
				return;
			}

			const removeButton = event.target.closest( '[data-repeater-remove]' );
			if ( removeButton ) {
				const container = removeButton.closest( '.ast__repeater' );
				const row = removeButton.closest( '[data-repeater-row]' );
				if ( container && row ) {
					removeRow( container, row );
				}
			}
		} );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', initRepeaters );
	} else {
		initRepeaters();
	}
}() );