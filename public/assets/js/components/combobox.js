/**
 * Reusable searchable combobox.
 *
 * Progressive enhancement for any <select data-combobox>:
 *   - single select     -> type-to-filter dropdown
 *   - <select multiple> -> same, with removable chips
 * The native <select> stays in the DOM as the source of truth (form value,
 * validation, `change` events), so server code and other scripts keep working.
 *
 * Attributes: data-placeholder, data-empty-text; per option: data-chip (short chip label).
 * After changing options/disabled state from script, call `select.comboboxRefresh()`.
 * `select.comboboxFocus()` moves focus into the field (e.g. to continue to the next one).
 * After setting `select.value` from script, dispatch a `change` event.
 */
(function () {
    'use strict';

    function enhance(select) {
        if (select.dataset.cbxReady) return;
        select.dataset.cbxReady = '1';

        var multiple = select.multiple;
        var placeholder = select.dataset.placeholder || 'Search...';
        var emptyText = select.dataset.emptyText || 'No matches found';

        var root = document.createElement('div');
        root.className = 'cbx' + (multiple ? ' cbx-multiple' : '');
        var control = document.createElement('div');
        control.className = 'form-control cbx-control';
        var chips = document.createElement('div');
        chips.className = 'cbx-chips';
        var input = document.createElement('input');
        input.type = 'text';
        input.className = 'cbx-input';
        input.autocomplete = 'off';
        input.setAttribute('role', 'combobox');
        input.setAttribute('aria-expanded', 'false');
        var caret = document.createElement('i');
        caret.className = 'bi bi-chevron-expand cbx-caret';
        var list = document.createElement('div');
        list.className = 'cbx-list';
        list.setAttribute('role', 'listbox');
        list.hidden = true;

        chips.appendChild(input);
        control.appendChild(chips);
        control.appendChild(caret);
        root.appendChild(control);
        document.body.appendChild(list);
        select.parentNode.insertBefore(root, select);
        select.classList.add('cbx-native');
        select.tabIndex = -1;
        if (select.id) {
            var label = document.querySelector('label[for="' + select.id + '"]');
            if (label) {
                label.addEventListener('click', function (e) {
                    e.preventDefault();
                    input.focus();
                });
            }
        }

        var active = -1;
        var open = false;

        function items() {
            return Array.prototype.filter.call(select.options, function (o) { return o.value !== ''; });
        }

        function labelOf(o) {
            return (o.dataset.chip || o.textContent).trim();
        }

        function syncDisplay() {
            var selected = items().filter(function (o) { return o.selected; });
            if (multiple) {
                Array.prototype.slice.call(chips.querySelectorAll('.cbx-chip')).forEach(function (c) { c.remove(); });
                selected.forEach(function (o) {
                    var chip = document.createElement('span');
                    chip.className = 'cbx-chip';
                    chip.textContent = labelOf(o);
                    var x = document.createElement('button');
                    x.type = 'button';
                    x.className = 'cbx-chip-remove';
                    x.setAttribute('aria-label', 'Remove ' + labelOf(o));
                    x.innerHTML = '&times;';
                    x.addEventListener('mousedown', function (e) {
                        e.preventDefault();
                        o.selected = false;
                        fire();
                    });
                    chip.appendChild(x);
                    chips.insertBefore(chip, input);
                });
                input.placeholder = selected.length ? '' : placeholder;
            } else {
                if (document.activeElement !== input || !open) {
                    input.value = selected.length ? labelOf(selected[0]) : '';
                }
                input.placeholder = placeholder;
            }
        }

        function fire() {
            syncDisplay();
            select.dispatchEvent(new Event('change', { bubbles: true }));
            if (open) render();
        }

        function render() {
            var q = (multiple || input.dataset.typing === '1') ? input.value.trim().toLowerCase() : '';
            var keepScroll = list.scrollTop;
            list.innerHTML = '';
            var shown = items().filter(function (o) { return !q || o.textContent.toLowerCase().indexOf(q) !== -1; });
            if (!shown.length) {
                var empty = document.createElement('div');
                empty.className = 'cbx-empty';
                empty.textContent = emptyText;
                list.appendChild(empty);
                active = -1;
                return;
            }
            if (active < 0 || active >= shown.length) active = 0;
            shown.forEach(function (o, i) {
                var row = document.createElement('div');
                row.className = 'cbx-option' + (o.selected ? ' is-selected' : '') + (o.disabled ? ' is-disabled' : '') + (i === active ? ' is-active' : '');
                row.setAttribute('role', 'option');
                row.setAttribute('aria-selected', o.selected ? 'true' : 'false');
                var text = document.createElement('span');
                text.textContent = o.textContent.trim();
                row.appendChild(text);
                if (o.selected) {
                    var tick = document.createElement('i');
                    tick.className = 'bi bi-check2 cbx-tick';
                    row.appendChild(tick);
                }
                row._opt = o;
                row.addEventListener('mousedown', function (e) {
                    e.preventDefault();
                    choose(o);
                });
                list.appendChild(row);
            });
            list.scrollTop = keepScroll;
        }

        function choose(o) {
            if (o.disabled) return;
            if (multiple) {
                o.selected = !o.selected;
                input.value = '';
            } else {
                select.value = o.value;
                input.dataset.typing = '0';
                close();
            }
            fire();
        }

        function position() {
            var r = control.getBoundingClientRect();
            var below = window.innerHeight - r.bottom;
            list.style.left = r.left + 'px';
            list.style.width = r.width + 'px';
            if (below < 200 && r.top > below) {
                list.style.top = 'auto';
                list.style.bottom = (window.innerHeight - r.top + 4) + 'px';
                list.style.maxHeight = Math.min(240, r.top - 12) + 'px';
            } else {
                list.style.bottom = 'auto';
                list.style.top = (r.bottom + 4) + 'px';
                list.style.maxHeight = Math.min(240, below - 12) + 'px';
            }
        }

        function show() {
            if (open) return;
            open = true;
            list.hidden = false;
            input.setAttribute('aria-expanded', 'true');
            root.classList.add('is-open');
            render();
            position();
        }

        function close() {
            if (!open) return;
            open = false;
            list.hidden = true;
            input.setAttribute('aria-expanded', 'false');
            root.classList.remove('is-open');
            input.dataset.typing = '0';
            if (multiple) input.value = '';
            syncDisplay();
        }

        control.addEventListener('mousedown', function (e) {
            if (e.target === input) return;
            e.preventDefault();
            if (document.activeElement !== input) {
                input.focus();
            } else {
                open ? close() : show();
            }
        });
        input.addEventListener('focus', function () {
            if (!multiple) input.select();
            show();
        });
        input.addEventListener('input', function () {
            input.dataset.typing = '1';
            active = 0;
            show();
            render();
        });
        input.addEventListener('keydown', function (e) {
            var rows = list.querySelectorAll('.cbx-option');
            if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
                e.preventDefault();
                show();
                if (!rows.length) return;
                active = (active + (e.key === 'ArrowDown' ? 1 : -1) + rows.length) % rows.length;
                render();
                var el = list.querySelector('.is-active');
                if (el && el.scrollIntoView) el.scrollIntoView({ block: 'nearest' });
            } else if (e.key === 'Enter') {
                if (open) {
                    e.preventDefault();
                    var cur = list.querySelector('.is-active');
                    if (cur && cur._opt) choose(cur._opt);
                }
            } else if (e.key === 'Escape') {
                if (open) {
                    e.stopPropagation();
                    close();
                }
            } else if (e.key === 'Backspace' && multiple && !input.value) {
                var sel = items().filter(function (o) { return o.selected; });
                if (sel.length) {
                    sel[sel.length - 1].selected = false;
                    fire();
                }
            } else if (e.key === 'Tab') {
                close();
            }
        });
        document.addEventListener('mousedown', function (e) {
            if (open && !root.contains(e.target) && !list.contains(e.target)) close();
        });
        window.addEventListener('resize', function () { if (open) position(); });
        window.addEventListener('scroll', function () { if (open) position(); }, true);
        select.addEventListener('change', syncDisplay);
        select.comboboxFocus = function () { input.focus(); };
        select.comboboxRefresh = function () {
            syncDisplay();
            if (open) render();
        };

        var modal = select.closest('.modal');
        if (modal) modal.addEventListener('hide.bs.modal', close);

        syncDisplay();
    }

    function init(scope) {
        (scope || document).querySelectorAll('select[data-combobox]').forEach(enhance);
    }

    window.Combobox = { init: init, enhance: enhance };
    document.addEventListener('DOMContentLoaded', function () { init(); });
})();
