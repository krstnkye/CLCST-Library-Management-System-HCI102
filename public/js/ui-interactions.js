// CLCST library: interactive touches shared by every portal. No build step required.
(() => {
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const typing = (el) => el && (el.isContentEditable || /^(INPUT|TEXTAREA|SELECT)$/.test(el.tagName));

    // A thin bar at the top shows that a page is loading.
    const progress = () => {
        const bar = document.createElement('div');
        bar.className = 'ui-progress';
        bar.setAttribute('aria-hidden', 'true');
        document.body.append(bar);
        const start = () => { bar.classList.remove('is-running'); void bar.offsetWidth; bar.classList.add('is-running'); };
        document.addEventListener('click', (event) => {
            const link = event.target.closest('a[href]');
            if (!link || event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
            if (link.target && link.target !== '_self') return;
            if (link.hasAttribute('download') || link.origin !== location.origin) return;
            if (link.hash && link.pathname === location.pathname) return;
            start();
        });
        document.addEventListener('submit', (event) => { if (!event.defaultPrevented && !event.target.target) start(); });
        window.addEventListener('pageshow', () => bar.classList.remove('is-running'));
    };

    // Ripple feedback on solid buttons.
    const ripple = () => {
        document.addEventListener('pointerdown', (event) => {
            const host = event.target.closest('.clcst-portal main button, .clcst-portal main a[class*="bg-"], .login-submit, .registration-actions > button');
            if (!host || host.disabled || reduceMotion) return;
            const rect = host.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const dot = document.createElement('span');
            dot.className = 'ui-ripple';
            dot.style.cssText = `width:${size}px;height:${size}px;left:${event.clientX - rect.left - size / 2}px;top:${event.clientY - rect.top - size / 2}px`;
            host.classList.add('ui-ripple-host');
            host.append(dot);
            dot.addEventListener('animationend', () => dot.remove(), { once: true });
        });
    };

    // Summary numbers count up from zero when the page opens.
    const countUp = () => {
        if (reduceMotion) return;
        document.querySelectorAll('main .library-metrics :is(.text-2xl,.text-3xl), main [data-count-up]').forEach((el) => {
            const walker = document.createTreeWalker(el, NodeFilter.SHOW_TEXT);
            let node;
            while ((node = walker.nextNode()) && !/\d/.test(node.nodeValue)) { /* find the first number */ }
            if (!node) return;
            const match = node.nodeValue.match(/(\d[\d,]*)(\.\d+)?/);
            if (!match) return;
            const target = parseFloat(match[0].replace(/,/g, ''));
            if (!target || target > 100000) return;
            const decimals = match[2] ? match[2].length - 1 : 0;
            const [before, after] = [node.nodeValue.slice(0, match.index), node.nodeValue.slice(match.index + match[0].length)];
            const format = (value) => value.toLocaleString('en-US', { minimumFractionDigits: decimals, maximumFractionDigits: decimals });
            const started = performance.now();
            const tick = (now) => {
                const t = Math.min(1, (now - started) / 900);
                node.nodeValue = before + format(target * (1 - Math.pow(1 - t, 3))) + after;
                if (t < 1) requestAnimationFrame(tick);
                else node.nodeValue = before + match[0] + after;
            };
            requestAnimationFrame(tick);
        });
    };

    // Cards below the fold fade in as they scroll into view.
    const reveal = () => {
        if (reduceMotion || !('IntersectionObserver' in window)) return;
        const targets = [...document.querySelectorAll('main :is(.library-section, section, article)')].filter((el) => !el.closest('.ui-reveal'));
        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            });
        }, { rootMargin: '0px 0px -40px 0px' });
        targets.forEach((el) => {
            if (el.getBoundingClientRect().top < window.innerHeight) return;
            const siblings = el.parentElement ? [...el.parentElement.children].indexOf(el) : 0;
            el.style.setProperty('--ui-delay', `${Math.min(siblings, 4) * 70}ms`);
            el.classList.add('ui-reveal');
            observer.observe(el);
        });
    };

    // A soft light follows the pointer across summary cards.
    const glow = () => {
        if (reduceMotion || !window.matchMedia('(hover: hover)').matches) return;
        document.querySelectorAll('main .library-metrics > *, main article').forEach((card) => {
            card.classList.add('ui-glow');
            card.addEventListener('pointermove', (event) => {
                const rect = card.getBoundingClientRect();
                card.style.setProperty('--mx', `${event.clientX - rect.left}px`);
                card.style.setProperty('--my', `${event.clientY - rect.top}px`);
            });
        });
    };

    // Long tables get a quick filter that hides rows that do not match.
    const tableFilters = () => {
        document.querySelectorAll('main table').forEach((table, index) => {
            const body = table.tBodies[0];
            if (!body) return;
            const rows = [...body.rows].filter((row) => !row.querySelector('td[colspan]'));
            if (rows.length < 6) return;
            const anchor = table.closest('.overflow-x-auto, .overflow-auto') || table;
            const tools = document.createElement('div');
            tools.className = 'ui-table-tools';
            const id = `ui-table-filter-${index}`;
            tools.innerHTML = `<label class="ui-table-filter" for="${id}"><span class="material-symbols-outlined" aria-hidden="true">filter_list</span><span class="sr-only">Filter rows in this table</span><input id="${id}" type="search" placeholder="Filter these ${rows.length} rows..." autocomplete="off"></label><span class="ui-table-count" aria-live="polite">${rows.length} shown</span>`;
            anchor.before(tools);
            const input = tools.querySelector('input');
            const count = tools.querySelector('.ui-table-count');
            const empty = document.createElement('tr');
            empty.className = 'ui-table-empty';
            empty.hidden = true;
            empty.innerHTML = `<td colspan="${table.rows[0]?.cells.length || 1}">No rows match your filter.</td>`;
            body.append(empty);
            input.addEventListener('input', () => {
                const terms = input.value.trim().toLowerCase().split(/\s+/).filter(Boolean);
                let shown = 0;
                rows.forEach((row) => {
                    const text = row.textContent.toLowerCase();
                    const match = terms.every((term) => text.includes(term));
                    row.hidden = !match;
                    if (match) shown++;
                });
                empty.hidden = shown > 0;
                count.textContent = `${shown} shown`;
            });
        });
    };

    // Ctrl+K (or /) opens a quick jump to any page in the current portal.
    const commandPalette = () => {
        const topbar = document.querySelector('.clcst-topbar');
        if (!topbar) return;
        const seen = new Set();
        const pages = [];
        document.querySelectorAll('.clcst-sidebar a[href], .clcst-topbar a[href], .clcst-mobile-nav a[href]').forEach((link) => {
            const url = new URL(link.href, location.href);
            if (url.origin !== location.origin || seen.has(url.pathname + url.search)) return;
            const icon = link.querySelector('.material-symbols-outlined')?.textContent.trim() || 'arrow_forward';
            const copy = link.cloneNode(true);
            copy.querySelectorAll('.material-symbols-outlined, img').forEach((el) => el.remove());
            const label = (link.getAttribute('aria-label') || copy.textContent).replace(/\(\d+\)/g, '').replace(/\s+/g, ' ').trim();
            if (!label || label.length > 60) return;
            seen.add(url.pathname + url.search);
            pages.push({ label: label.replace(/\s\d+$/, '').replace(/\sPHP\s[\d,]+$/, ''), icon, href: link.href, current: link.getAttribute('aria-current') === 'page' });
        });
        if (pages.length < 2) return;
        const searchForm = topbar.querySelector('form[role="search"]') || document.querySelector('.clcst-sidebar form[role="search"]');

        const trigger = document.createElement('button');
        trigger.type = 'button';
        trigger.className = 'ui-cmdk-trigger';
        trigger.setAttribute('aria-label', 'Quick jump to a page');
        trigger.setAttribute('aria-haspopup', 'dialog');
        trigger.innerHTML = '<span class="material-symbols-outlined" aria-hidden="true">bolt</span><span>Quick jump</span><kbd>Ctrl K</kbd>';
        const right = topbar.lastElementChild;
        right.prepend(trigger);

        const dialog = document.createElement('div');
        dialog.className = 'ui-cmdk';
        dialog.hidden = true;
        dialog.innerHTML = `<div class="ui-cmdk-panel" role="dialog" aria-modal="true" aria-label="Quick jump">
            <div class="ui-cmdk-search"><span class="material-symbols-outlined" aria-hidden="true">search</span>
            <input type="text" role="combobox" aria-expanded="true" aria-controls="ui-cmdk-list" aria-autocomplete="list" placeholder="Go to a page${searchForm ? ' or search the catalog' : ''}..." autocomplete="off"></div>
            <ul class="ui-cmdk-list" id="ui-cmdk-list" role="listbox"></ul>
            <div class="ui-cmdk-foot"><span><kbd>&uarr;</kbd> <kbd>&darr;</kbd> to move</span><span><kbd>Enter</kbd> to open</span><span><kbd>Esc</kbd> to close</span></div></div>`;
        document.body.append(dialog);
        const input = dialog.querySelector('input');
        const list = dialog.querySelector('ul');
        let items = [];
        let active = 0;
        let lastFocus = null;

        const select = (index) => {
            active = (index + items.length) % items.length;
            const options = list.querySelectorAll('[role="option"]');
            options.forEach((el, i) => el.setAttribute('aria-selected', String(i === active)));
            if (options[active]) { options[active].scrollIntoView({ block: 'nearest' }); input.setAttribute('aria-activedescendant', options[active].id); }
        };
        const render = () => {
            const query = input.value.trim().toLowerCase();
            items = pages.filter((page) => !query || page.label.toLowerCase().includes(query));
            if (query && searchForm) items.push({ label: `Search for "${input.value.trim()}"`, icon: 'travel_explore', search: input.value.trim() });
            list.innerHTML = items.length ? '<li class="ui-cmdk-group" role="presentation">Pages</li>' : '<li class="ui-cmdk-empty" role="presentation">No pages match.</li>';
            items.forEach((item, i) => {
                const li = document.createElement('li');
                li.className = 'ui-cmdk-item';
                li.id = `ui-cmdk-option-${i}`;
                li.setAttribute('role', 'option');
                li.innerHTML = `<span class="material-symbols-outlined" aria-hidden="true"></span><span></span>${item.current ? '<small>You are here</small>' : ''}`;
                li.children[0].textContent = item.icon;
                li.children[1].textContent = item.label;
                li.addEventListener('mousemove', () => { if (active !== i) select(i); });
                li.addEventListener('click', () => go(item));
                list.append(li);
            });
            if (items.length) select(0);
        };
        const go = (item) => {
            if (!item) return;
            if (item.search) {
                const field = searchForm.querySelector('input[type="search"], input[name]');
                if (field) field.value = item.search;
                searchForm.requestSubmit ? searchForm.requestSubmit() : searchForm.submit();
            } else {
                location.href = item.href;
            }
            close();
        };
        const open = () => {
            lastFocus = document.activeElement;
            dialog.hidden = false;
            input.value = '';
            render();
            input.focus();
        };
        const close = () => {
            if (dialog.hidden) return;
            dialog.hidden = true;
            lastFocus?.focus?.();
        };
        trigger.addEventListener('click', open);
        dialog.addEventListener('click', (event) => { if (event.target === dialog) close(); });
        input.addEventListener('input', render);
        input.addEventListener('keydown', (event) => {
            if (event.key === 'ArrowDown') { event.preventDefault(); select(active + 1); }
            else if (event.key === 'ArrowUp') { event.preventDefault(); select(active - 1); }
            else if (event.key === 'Enter') { event.preventDefault(); go(items[active]); }
            else if (event.key === 'Tab') { event.preventDefault(); }
        });
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') { close(); return; }
            const combo = (event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k';
            const slash = event.key === '/' && !typing(document.activeElement);
            if ((combo || slash) && dialog.hidden) { event.preventDefault(); open(); }
        });
    };

    // A back-to-top button appears on long pages.
    const backToTop = () => {
        if (!document.querySelector('.clcst-topbar')) return;
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'ui-top';
        button.setAttribute('aria-label', 'Back to top');
        button.innerHTML = '<span class="material-symbols-outlined" aria-hidden="true">arrow_upward</span>';
        button.addEventListener('click', () => window.scrollTo({ top: 0, behavior: reduceMotion ? 'auto' : 'smooth' }));
        document.body.append(button);
        const update = () => button.classList.toggle('is-visible', window.scrollY > 600);
        window.addEventListener('scroll', update, { passive: true });
        update();
    };

    const initialize = () => {
        progress();
        ripple();
        tableFilters();
        countUp();
        reveal();
        glow();
        commandPalette();
        backToTop();
    };
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initialize, { once: true });
    else initialize();
})();
