/**
 * EPSelect — global themed dropdown.
 *
 * Progressively enhances every native <select> into a dropdown styled with the
 * app theme, instead of the OS-native popup. The original <select> stays in the
 * DOM (visually hidden) so forms keep submitting and existing JS that reads/sets
 * `.value` or listens for `change` keeps working unchanged.
 *
 * Opt out per element with `data-no-enhance`.
 */
(function () {
    'use strict';

    var GAP = 4;
    var open = null; // { btn, menu } currently open instance, or null

    var valueDesc = Object.getOwnPropertyDescriptor(HTMLSelectElement.prototype, 'value');
    var indexDesc = Object.getOwnPropertyDescriptor(HTMLSelectElement.prototype, 'selectedIndex');

    function closeOpen() {
        if (!open) return;
        open.menu.remove();
        open.btn.setAttribute('aria-expanded', 'false');
        open = null;
    }

    function positionMenu(btn, menu) {
        var r = btn.getBoundingClientRect();
        menu.style.position = 'fixed';
        menu.style.minWidth = r.width + 'px';

        var below = window.innerHeight - r.bottom - GAP;
        var above = r.top - GAP;
        var height = Math.min(menu.scrollHeight, 320);

        if (below >= height || below >= above) {
            menu.style.top = (r.bottom + GAP) + 'px';
            menu.style.bottom = 'auto';
            menu.style.maxHeight = Math.max(120, below) + 'px';
        } else {
            menu.style.bottom = (window.innerHeight - r.top + GAP) + 'px';
            menu.style.top = 'auto';
            menu.style.maxHeight = Math.max(120, above) + 'px';
        }

        // Horizontal: align to the button, clamped to the viewport.
        var mw = menu.offsetWidth;
        var left = r.left;
        if (left + mw > window.innerWidth - 8) left = Math.max(8, window.innerWidth - 8 - mw);
        menu.style.left = left + 'px';
    }

    function enhance(select) {
        if (select.dataset.epEnhanced || select.multiple || select.hasAttribute('data-no-enhance')) return;
        select.dataset.epEnhanced = '1';

        var wrap = document.createElement('div');
        wrap.className = 'ep-select';
        // Carry over sizing hint classes (e.g. ep-w-auto) to the wrapper.
        select.classList.forEach(function (c) { if (c.indexOf('ep-') === 0) wrap.classList.add(c); });

        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'ep-select-btn';
        btn.setAttribute('aria-haspopup', 'listbox');
        btn.setAttribute('aria-expanded', 'false');
        btn.innerHTML = '<span class="ep-select-label"></span><i class="bi bi-chevron-down ep-select-caret"></i>';
        var labelEl = btn.querySelector('.ep-select-label');

        select.parentNode.insertBefore(wrap, select);
        wrap.appendChild(select);
        wrap.appendChild(btn);
        select.classList.add('ep-select-native');

        function syncLabel() {
            var opt = select.options[select.selectedIndex];
            labelEl.textContent = opt ? opt.textContent.trim() : '';
            btn.classList.toggle('is-placeholder', !select.value);
            if (select.disabled) btn.setAttribute('disabled', ''); else btn.removeAttribute('disabled');
        }

        // Catch programmatic `.value = …` / `.selectedIndex = …` so the label stays in sync.
        Object.defineProperty(select, 'value', {
            configurable: true,
            get: function () { return valueDesc.get.call(this); },
            set: function (v) { valueDesc.set.call(this, v); syncLabel(); },
        });
        Object.defineProperty(select, 'selectedIndex', {
            configurable: true,
            get: function () { return indexDesc.get.call(this); },
            set: function (v) { indexDesc.set.call(this, v); syncLabel(); },
        });
        select.addEventListener('change', syncLabel);

        function buildMenu() {
            var menu = document.createElement('div');
            menu.className = 'ep-select-menu';
            menu.setAttribute('role', 'listbox');
            Array.prototype.forEach.call(select.options, function (opt) {
                var item = document.createElement('button');
                item.type = 'button';
                item.className = 'ep-select-opt' + (opt.selected ? ' is-active' : '');
                item.textContent = opt.textContent.trim() || opt.value;
                if (opt.disabled) item.disabled = true;
                item.addEventListener('click', function () {
                    if (opt.disabled) return;
                    select.value = opt.value;
                    select.dispatchEvent(new Event('input', { bubbles: true }));
                    select.dispatchEvent(new Event('change', { bubbles: true }));
                    closeOpen();
                    btn.focus();
                });
                menu.appendChild(item);
            });
            return menu;
        }

        function openMenu() {
            closeOpen();
            if (select.disabled) return;
            var menu = buildMenu();
            document.body.appendChild(menu);
            open = { btn: btn, menu: menu };
            btn.setAttribute('aria-expanded', 'true');
            positionMenu(btn, menu);
            var active = menu.querySelector('.ep-select-opt.is-active');
            if (active) active.scrollIntoView({ block: 'nearest' });
        }

        btn.addEventListener('click', function (e) {
            e.preventDefault();
            if (open && open.btn === btn) closeOpen(); else openMenu();
        });
        btn.addEventListener('keydown', function (e) {
            if (e.key === 'ArrowDown' || e.key === 'Enter' || e.key === ' ') { e.preventDefault(); openMenu(); }
        });

        syncLabel();
    }

    function enhanceAll(root) {
        (root || document).querySelectorAll('select').forEach(enhance);
    }

    // Close on outside interaction / viewport changes (fixed menu won't follow).
    document.addEventListener('mousedown', function (e) {
        if (open && !open.menu.contains(e.target) && !open.btn.contains(e.target)) closeOpen();
    });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeOpen(); });
    window.addEventListener('resize', closeOpen, true);
    window.addEventListener('scroll', closeOpen, true);

    // Auto-enhance any selects added later (so this is the standard for all future markup).
    var observer = new MutationObserver(function (mutations) {
        mutations.forEach(function (m) {
            m.addedNodes.forEach(function (node) {
                if (node.nodeType !== 1) return;
                if (node.tagName === 'SELECT') enhance(node);
                else if (node.querySelectorAll) node.querySelectorAll('select').forEach(enhance);
            });
        });
    });

    function init() {
        enhanceAll(document);
        observer.observe(document.body, { childList: true, subtree: true });
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
    else init();

    window.EPSelect = { enhance: enhance, enhanceAll: enhanceAll };
})();
