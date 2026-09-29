/******/ (() => { // webpackBootstrap
/******/ 	"use strict";

;// ./node_modules/@babel/runtime/helpers/esm/arrayLikeToArray.js
function _arrayLikeToArray(r, a) {
  (null == a || a > r.length) && (a = r.length);
  for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e];
  return n;
}

;// ./node_modules/@babel/runtime/helpers/esm/arrayWithoutHoles.js

function _arrayWithoutHoles(r) {
  if (Array.isArray(r)) return _arrayLikeToArray(r);
}

;// ./node_modules/@babel/runtime/helpers/esm/iterableToArray.js
function _iterableToArray(r) {
  if ("undefined" != typeof Symbol && null != r[Symbol.iterator] || null != r["@@iterator"]) return Array.from(r);
}

;// ./node_modules/@babel/runtime/helpers/esm/unsupportedIterableToArray.js

function _unsupportedIterableToArray(r, a) {
  if (r) {
    if ("string" == typeof r) return _arrayLikeToArray(r, a);
    var t = {}.toString.call(r).slice(8, -1);
    return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0;
  }
}

;// ./node_modules/@babel/runtime/helpers/esm/nonIterableSpread.js
function _nonIterableSpread() {
  throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.");
}

;// ./node_modules/@babel/runtime/helpers/esm/toConsumableArray.js




function _toConsumableArray(r) {
  return _arrayWithoutHoles(r) || _iterableToArray(r) || _unsupportedIterableToArray(r) || _nonIterableSpread();
}

;// ./src/js/admin-script.js

/**
 * art-settings: поведение полей на странице настроек.
 * Vanilla JS, без jQuery-зависимости для новых фич.
 */
(function () {
  'use strict';

  var INDEX_PLACEHOLDER = '__INDEX__';
  var DEBUG_FIX = Boolean(window.astSettingsDebug);
  function logFix(message, data) {
    if (DEBUG_FIX && window.console) {
      window.console.log('[FIX]', message, data);
    }
  }

  /**
   * Экспонирует текущий индекс строки в name/id/for атрибутах вложенных полей.
   * Паттерн: {fieldId}[{oldIndex}][{subFieldId}]
   */
  function reindexRow(row, fieldId, newIndex) {
    var escapedFieldId = fieldId.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    var pattern = new RegExp(escapedFieldId + '\\[(\\d+)\\]');
    var rows = Array.prototype.filter.call(row.querySelectorAll('[name], [id], [for]'), function (el) {
      var value = el.getAttribute('name') || el.getAttribute('id') || el.getAttribute('for');
      return pattern.test(value);
    });
    rows.forEach(function (el) {
      ['name', 'id', 'for'].forEach(function (attr) {
        var value = el.getAttribute(attr);
        if (!value) {
          return;
        }
        el.setAttribute(attr, value.replace(pattern, function () {
          return fieldId + '[' + newIndex + ']';
        }));
      });
    });
  }

  /**
   * Собирает строки контейнера и перенумеровывает индексы по порядку.
   */
  function renumberRows(container, fieldId) {
    var rows = Array.prototype.filter.call(container.querySelectorAll('[data-repeater-row]'), function (el) {
      return !el.closest('[data-repeater-template]');
    });
    rows.forEach(function (row, index) {
      reindexRow(row, fieldId, index);
      var label = row.querySelector('.ast__repeater-row-label');
      if (label) {
        label.textContent = label.textContent.replace(/#(?:__INDEX__|\d+)$/, '#' + (index + 1));
      }
    });
  }

  /**
   * Клонирует скрытую шаблонную строку и вставляет новую с актуальным индексом.
   */
  function addRow(container) {
    var fieldId = container.dataset.fieldId;
    var maxRows = container.dataset.maxRows;
    var currentRows = Array.prototype.filter.call(container.querySelectorAll('[data-repeater-row]'), function (el) {
      return !el.closest('[data-repeater-template]');
    }).length;
    if (maxRows && currentRows >= parseInt(maxRows, 10)) {
      return;
    }
    var template = container.querySelector('[data-repeater-template] [data-repeater-row]');
    if (!template) {
      return;
    }
    var clone = template.cloneNode(true);
    clone.removeAttribute('hidden');
    clone.classList.remove('ast__repeater-template');

    // Замена плейсхолдера индекса на актуальный во всех name/id/for.
    [clone].concat(_toConsumableArray(clone.querySelectorAll('[name], [id], [for]'))).forEach(function (el) {
      ['name', 'id', 'for'].forEach(function (attr) {
        var value = el.getAttribute(attr);
        if (value && value.includes(INDEX_PLACEHOLDER)) {
          el.setAttribute(attr, value.replace(INDEX_PLACEHOLDER, String(currentRows)));
        }
      });
    });
    container.querySelector('[data-repeater-rows]').appendChild(clone);
    renumberRows(container, fieldId);
    logFix('Repeater row added', {
      fieldId: fieldId,
      rowIndex: currentRows
    });
  }

  /**
   * Удаляет строку (не ниже min_rows).
   */
  function removeRow(container, row) {
    var minRows = parseInt(container.dataset.minRows || '1', 10);
    var fieldId = container.dataset.fieldId;
    var rows = Array.prototype.filter.call(container.querySelectorAll('[data-repeater-row]'), function (el) {
      return !el.closest('[data-repeater-template]');
    });
    if (rows.length <= minRows) {
      return;
    }
    row.remove();
    renumberRows(container, fieldId);
    logFix('Repeater row removed', {
      fieldId: fieldId
    });
  }
  function initRepeaters() {
    document.addEventListener('click', function (event) {
      var addButton = event.target.closest('[data-repeater-add]');
      if (addButton) {
        var container = addButton.closest('.ast__repeater');
        if (container) {
          addRow(container);
        }
        return;
      }
      var removeButton = event.target.closest('[data-repeater-remove]');
      if (removeButton) {
        var _container = removeButton.closest('.ast__repeater');
        var row = removeButton.closest('[data-repeater-row]');
        if (_container && row) {
          removeRow(_container, row);
        }
      }
    });
  }
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initRepeaters);
  } else {
    initRepeaters();
  }
})();
/******/ })()
;