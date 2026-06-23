import Alpine from 'alpinejs';

const THEME_KEY = 'kindo-theme';

function getSystemTheme() {
    return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
}

function applyTheme(theme) {
    document.documentElement.classList.toggle('dark', theme === 'dark');
    const meta = document.querySelector('meta[name="theme-color"]');
    if (meta) {
        meta.setAttribute('content', theme === 'dark' ? '#12121a' : '#2979FF');
    }
}

function initTheme() {
    const stored = localStorage.getItem(THEME_KEY);
    const theme = stored === 'dark' || stored === 'light' ? stored : getSystemTheme();
    applyTheme(theme);
    return theme;
}

// FOUC prevention — run before Alpine
window.__kindoTheme = initTheme();

document.addEventListener('alpine:init', () => {
    Alpine.store('theme', {
        current: window.__kindoTheme,

        toggle() {
            this.set(this.current === 'dark' ? 'light' : 'dark');
        },

        set(theme, persist = true) {
            this.current = theme;
            applyTheme(theme);
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
