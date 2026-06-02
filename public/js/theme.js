(function () {
    var STORAGE_KEY = 'dobs-theme';
    var btn = document.getElementById('themeToggle');

    function getTheme() {
        return document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'monokai';
    }

    function setTheme(theme) {
        document.documentElement.setAttribute('data-theme', theme === 'dark' ? 'dark' : 'monokai');
        localStorage.setItem(STORAGE_KEY, theme);
        updateButton();
    }

    function toggleTheme() {
        setTheme(getTheme() === 'dark' ? 'monokai' : 'dark');
    }

    function updateButton() {
        if (!btn) {
            return;
        }

        var isMonokai = getTheme() === 'monokai';
        var iconDark = btn.querySelector('.theme-icon-dark');
        var iconMonokai = btn.querySelector('.theme-icon-monokai');

        btn.setAttribute(
            'aria-label',
            isMonokai ? btn.dataset.labelDark : btn.dataset.labelMonokai
        );

        if (iconDark) {
            iconDark.hidden = isMonokai;
        }

        if (iconMonokai) {
            iconMonokai.hidden = !isMonokai;
        }
    }

    if (btn) {
        btn.addEventListener('click', toggleTheme);
        updateButton();
    }
})();
