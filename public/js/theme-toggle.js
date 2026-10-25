// Theme toggle functionality
(function() {
    const THEME_KEY = 'theme';
    const DARK_CLASS = 'dark';
    const lightIconPath = '/public/images/icons/light_mode.svg';
    const darkIconPath = '/public/images/icons/dark_mode.svg';

    // Get elements
    const toggle = document.getElementById('theme-toggle');
    const icon = document.getElementById('theme-icon');
    const html = document.documentElement;

    if (!toggle || !icon) return;

    // Get initial theme
    const savedTheme = localStorage.getItem(THEME_KEY);
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    const initialTheme = savedTheme || (prefersDark ? 'dark' : 'light');

    // Apply initial theme
    if (initialTheme === 'dark') {
        html.classList.add(DARK_CLASS);
        icon.src = icon.src.replace('dark_mode.svg', 'light_mode.svg');
    }

    // Toggle theme
    toggle.addEventListener('click', () => {
        const isDark = html.classList.toggle(DARK_CLASS);
        localStorage.setItem(THEME_KEY, isDark ? 'dark' : 'light');

        // Update icon
        if (isDark) {
            icon.src = icon.src.replace('dark_mode.svg', 'light_mode.svg');
        } else {
            icon.src = icon.src.replace('light_mode.svg', 'dark_mode.svg');
        }
    });

    // Listen for system theme changes
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
        if (!localStorage.getItem(THEME_KEY)) {
            if (e.matches) {
                html.classList.add(DARK_CLASS);
                icon.src = icon.src.replace('dark_mode.svg', 'light_mode.svg');
            } else {
                html.classList.remove(DARK_CLASS);
                icon.src = icon.src.replace('light_mode.svg', 'dark_mode.svg');
            }
        }
    });
})();
