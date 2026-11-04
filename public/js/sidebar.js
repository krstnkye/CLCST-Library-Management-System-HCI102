// Shared sidebar behaviour for every portal.
// Desktop: a menu button in the top bar collapses the sidebar to an icon rail (remembered per browser).
// Phone: the sidebar is hidden; the same button slides it in as a drawer over the page.
(() => {
    const STORAGE_KEY = 'clcst-sidebar';
    const root = document.documentElement;
    const desktop = window.matchMedia('(min-width: 768px)');

    // The sidebar keeps its scroll position from page to page. Tailwind's CDN styles arrive a moment
    // after the HTML, so the list only becomes scrollable a few frames later: keep trying until it is.
    const rememberScroll = (sidebar) => {
        const list = [...sidebar.querySelectorAll('div, nav')].find((el) => /(auto|scroll)/.test(getComputedStyle(el).overflowY)) || sidebar.firstElementChild;
        if (!list) return;
        const key = 'clcst-sidebar-scroll:' + sidebar.id;
        const save = () => { try { sessionStorage.setItem(key, String(Math.round(list.scrollTop))); } catch (_) { /* storage is optional */ } };
        let saved = null;
        try { saved = sessionStorage.getItem(key); } catch (_) { /* storage is optional */ }
        let tries = 0;
        const restore = () => {
            const scrollable = list.scrollHeight > list.clientHeight && /(auto|scroll)/.test(getComputedStyle(list).overflowY);
            if (!scrollable && tries++ < 60) { requestAnimationFrame(restore); return; }
            if (saved !== null) list.scrollTop = Number(saved);
            // Whatever was saved, the current page's item should be in view.
            const active = list.querySelector('a[aria-current="page"]');
            if (active) {
                const box = list.getBoundingClientRect(), item = active.getBoundingClientRect();
                if (item.top < box.top || item.bottom > box.bottom) list.scrollTop += item.top - box.top - (box.height - item.height) / 2;
            }
            list.addEventListener('scroll', save, { passive: true });
        };
        restore();
        list.addEventListener('click', save);
        window.addEventListener('pagehide', save);
    };

    const initialize = () => {
        const sidebar = document.querySelector('.clcst-sidebar');
        const topbar = document.querySelector('.clcst-topbar');
        if (!sidebar || !topbar) return;
        sidebar.id ||= 'portal-sidebar';

        // Icon-only rail still needs readable names on hover and for screen readers.
        sidebar.querySelectorAll('a, button').forEach((item) => {
            const label = item.textContent.replace(/\s+/g, ' ').trim();
            const iconText = [...item.querySelectorAll('.material-symbols-outlined')].map((icon) => icon.textContent.trim());
            const name = iconText.reduce((text, icon) => text.replace(icon, ''), label).trim();
            if (name && !item.title) item.title = name;
        });

        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'clcst-sidebar-toggle';
        button.setAttribute('aria-controls', sidebar.id);
        button.innerHTML = '<span class="material-symbols-outlined" aria-hidden="true">menu</span>';

        // Keep the brand and the new button together on the left of the top bar.
        const brand = topbar.firstElementChild;
        const wrap = document.createElement('div');
        wrap.className = 'clcst-brand-wrap';
        brand.before(wrap);
        wrap.append(button, brand);

        const backdrop = document.createElement('div');
        backdrop.className = 'clcst-sidebar-backdrop';
        backdrop.hidden = true;
        document.body.append(backdrop);

        rememberScroll(sidebar);

        const sync = () => {
            const isDesktop = desktop.matches;
            const expanded = isDesktop ? !root.classList.contains('sidebar-collapsed') : root.classList.contains('sidebar-open');
            button.setAttribute('aria-expanded', String(expanded));
            const label = isDesktop ? (expanded ? 'Collapse the sidebar' : 'Expand the sidebar') : (expanded ? 'Close the menu' : 'Open the menu');
            button.setAttribute('aria-label', label);
            button.title = label;
            button.firstElementChild.textContent = isDesktop && expanded ? 'menu_open' : (!isDesktop && expanded ? 'close' : 'menu');
            backdrop.hidden = isDesktop || !expanded;
        };

        const closeDrawer = () => { root.classList.remove('sidebar-open'); sync(); };

        button.addEventListener('click', () => {
            if (desktop.matches) {
                const collapsed = root.classList.toggle('sidebar-collapsed');
                try { localStorage.setItem(STORAGE_KEY, collapsed ? 'collapsed' : 'expanded'); } catch (_) { /* storage is optional */ }
            } else {
                root.classList.toggle('sidebar-open');
                if (root.classList.contains('sidebar-open')) sidebar.querySelector('a[aria-current="page"], a')?.focus({ preventScroll: true });
            }
            sync();
        });
        backdrop.addEventListener('click', closeDrawer);
        sidebar.addEventListener('click', (event) => { if (!desktop.matches && event.target.closest('a')) closeDrawer(); });
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && root.classList.contains('sidebar-open')) { closeDrawer(); button.focus(); }
        });
        desktop.addEventListener('change', () => { root.classList.remove('sidebar-open'); sync(); });
        sync();
        requestAnimationFrame(() => root.classList.add('sidebar-ready'));
    };

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initialize, { once: true });
    else initialize();
})();
