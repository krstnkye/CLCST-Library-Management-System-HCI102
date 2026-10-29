(() => {
    const initialize = () => {
        const main = document.querySelector('main');
        if (main && !document.querySelector('.clcst-skip')) {
            main.id ||= 'main-content';
            main.tabIndex = -1;
            const skip = document.createElement('a');
            skip.className = 'clcst-skip';
            skip.href = '#' + main.id;
            skip.textContent = 'Skip to main content';
            document.body.prepend(skip);
        }
        // Navigation stays in place when switching pages. Storage is optional.
        document.querySelectorAll('.clcst-sidebar .custom-scrollbar, .clcst-mobile-nav').forEach((nav) => {
            const key = 'clcst-nav-position:' + (nav.closest('[id]')?.id || nav.getAttribute('aria-label'));
            try {
                const saved = JSON.parse(sessionStorage.getItem(key) || '{}');
                nav.scrollTop = saved.top || 0;
                nav.scrollLeft = saved.left || 0;
            } catch (_) { /* Private browsing may disable storage. */ }
            const save = () => {
                try { sessionStorage.setItem(key, JSON.stringify({ top: nav.scrollTop, left: nav.scrollLeft })); }
                catch (_) { /* Navigation still works without storage. */ }
            };
            nav.addEventListener('scroll', save, { passive: true });
            nav.addEventListener('click', save);
        });
    };
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initialize, { once: true });
    else initialize();
})();
