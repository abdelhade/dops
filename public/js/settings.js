(function () {
    var labelEl = document.getElementById('settingsThemeLabel');
    var lang = window.DOBS_SETTINGS_LANG || {};

    function updateThemeLabel() {
        if (!labelEl) {
            return;
        }

        var isDark = document.documentElement.getAttribute('data-theme') === 'dark';
        labelEl.textContent = isDark ? (lang.themeDark || '') : (lang.themeMonokai || '');
    }

    document.addEventListener('DOMContentLoaded', updateThemeLabel);

    var btn = document.getElementById('themeToggle');
    if (btn) {
        btn.addEventListener('click', function () {
            window.setTimeout(updateThemeLabel, 0);
        });
    }
})();
