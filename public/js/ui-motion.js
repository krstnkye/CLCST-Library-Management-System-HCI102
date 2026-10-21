(() => {
    const initializeMotion = () => {
        const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        const revealTargets = document.querySelectorAll([
            'main > section',
            'main > div > section',
            'main article',
            'main form > section',
            'main aside > section',
            'main table'
        ].join(','));

        revealTargets.forEach((element, index) => {
            element.classList.add('ui-reveal');
            element.style.setProperty('--ui-delay', `${Math.min(index % 6, 5) * 45}ms`);
        });

        document.querySelectorAll('[role="alert"], main .bg-emerald-50, main .bg-red-50')
            .forEach((element) => element.classList.add('ui-status'));

        document.querySelectorAll('main article, main section[class*="shadow"]')
            .forEach((element) => element.classList.add('ui-lift'));

        document.querySelectorAll('main a[class*="rounded"], main button')
            .forEach((element) => element.classList.add('ui-action'));

        document.body.classList.add('ui-motion-ready');

        if (reduceMotion || !('IntersectionObserver' in window)) {
            revealTargets.forEach((element) => element.classList.add('is-visible'));
            return;
        }

        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) {
                    return;
                }

                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            });
        }, { rootMargin: '0px 0px -6% 0px', threshold: 0.06 });

        revealTargets.forEach((element) => observer.observe(element));
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeMotion, { once: true });
    } else {
        initializeMotion();
    }
})();
