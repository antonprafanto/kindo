import Alpine from 'alpinejs';

const THEME_KEY = 'kindo-theme';
const THEME_ANIMATE_MS = 480;

function getSystemTheme() {
    return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
}

function prefersReducedMotion() {
    return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
}

function updateThemeColor(theme) {
    const meta = document.querySelector('meta[name="theme-color"]');
    if (meta) {
        meta.setAttribute('content', theme === 'dark' ? '#12121a' : '#2979FF');
    }
}

function setDarkClass(theme) {
    document.documentElement.classList.toggle('dark', theme === 'dark');
    updateThemeColor(theme);
}

function getTransitionOrigin(event) {
    if (event?.clientX != null && event?.clientY != null) {
        return { x: event.clientX, y: event.clientY };
    }

    const toggle = document.querySelector('[data-theme-toggle]');
    if (toggle) {
        const rect = toggle.getBoundingClientRect();
        return { x: rect.left + rect.width / 2, y: rect.top + rect.height / 2 };
    }

    return { x: window.innerWidth / 2, y: window.innerHeight / 2 };
}

function playCircularReveal(theme, event) {
    const reducedMotion = prefersReducedMotion();
    const canViewTransition = !reducedMotion && typeof document.startViewTransition === 'function';

    const apply = () => setDarkClass(theme);

    if (!canViewTransition) {
        if (!reducedMotion) {
            document.documentElement.classList.add('theme-animate');
            window.setTimeout(() => {
                document.documentElement.classList.remove('theme-animate');
            }, THEME_ANIMATE_MS);
        }
        apply();
        return;
    }

    const { x, y } = getTransitionOrigin(event);
    const endRadius = Math.hypot(
        Math.max(x, window.innerWidth - x),
        Math.max(y, window.innerHeight - y),
    );

    document.documentElement.classList.add('theme-animate');

    const transition = document.startViewTransition(apply);

    transition.ready
        .then(() => {
            document.documentElement.animate(
                {
                    clipPath: [
                        `circle(0px at ${x}px ${y}px)`,
                        `circle(${endRadius}px at ${x}px ${y}px)`,
                    ],
                },
                {
                    duration: THEME_ANIMATE_MS,
                    easing: 'cubic-bezier(0.4, 0, 0.2, 1)',
                    pseudoElement: '::view-transition-new(root)',
                },
            );
        })
        .finally(() => {
            window.setTimeout(() => {
                document.documentElement.classList.remove('theme-animate');
            }, THEME_ANIMATE_MS);
        });
}

function applyTheme(theme, event = null) {
    if (event) {
        playCircularReveal(theme, event);
        return;
    }

    setDarkClass(theme);
}

function initTheme() {
    const stored = localStorage.getItem(THEME_KEY);
    const theme = stored === 'dark' || stored === 'light' ? stored : getSystemTheme();
    applyTheme(theme);
    return theme;
}

// FOUC prevention — run before Alpine (no animation on first paint)
window.__kindoTheme = initTheme();

document.addEventListener('alpine:init', () => {
    Alpine.store('theme', {
        current: window.__kindoTheme,

        toggle(event) {
            this.set(this.current === 'dark' ? 'light' : 'dark', true, event);
        },

        set(theme, persist = true, event = null) {
            this.current = theme;
            applyTheme(theme, event);
            if (persist) {
                localStorage.setItem(THEME_KEY, theme);
            }
        },
    });
});

window.Alpine = Alpine;
Alpine.start();

window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
    if (!localStorage.getItem(THEME_KEY)) {
        Alpine.store('theme').set(e.matches ? 'dark' : 'light', false);
    }
});
