(() => {
    const initialize = () => {
        // Confirmation notices can be dismissed; errors stay visible.
        document.querySelectorAll('[role="status"]').forEach((notice) => {
            if (!notice.querySelector('.material-symbols-outlined') || notice.querySelector('button')) return;
            const dismiss = document.createElement('button');
            dismiss.type = 'button';
            dismiss.className = 'clcst-dismiss';
            dismiss.setAttribute('aria-label', 'Dismiss confirmation');
            dismiss.textContent = '\u00d7';
            dismiss.addEventListener('click', () => {
                const target = document.querySelector('main h1');
                if (target) { target.tabIndex = -1; target.focus({ preventScroll: true }); }
                notice.remove();
            });
            notice.append(dismiss);
        });
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
    };
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initialize, { once: true });
    else initialize();
})();
