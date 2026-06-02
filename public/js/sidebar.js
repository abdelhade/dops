(function () {
    var body = document.body;
    var toggle = document.getElementById('sidebarToggle');
    var closeBtn = document.getElementById('sidebarClose');
    var overlay = document.getElementById('sidebarOverlay');
    var mq = window.matchMedia('(max-width: 992px)');

    function isMobile() {
        return mq.matches;
    }

    function setOverlayVisible(visible) {
        if (!overlay) {
            return;
        }
        overlay.classList.toggle('is-visible', visible);
        overlay.setAttribute('aria-hidden', visible ? 'false' : 'true');
    }

    function setMobileOpen(open) {
        body.classList.toggle('sidebar-open', open);
        setOverlayVisible(open);
        body.style.overflow = open ? 'hidden' : '';
        updateAria();
    }

    function setDesktopCollapsed(collapsed) {
        body.classList.toggle('sidebar-collapsed', collapsed);
        updateAria();
    }

    function updateAria() {
        if (!toggle) {
            return;
        }
        var expanded = isMobile()
            ? body.classList.contains('sidebar-open')
            : !body.classList.contains('sidebar-collapsed');
        toggle.setAttribute('aria-expanded', expanded ? 'true' : 'false');
    }

    function closeSidebar() {
        if (isMobile()) {
            setMobileOpen(false);
            return;
        }
        setDesktopCollapsed(true);
    }

    function toggleSidebar() {
        if (isMobile()) {
            setMobileOpen(!body.classList.contains('sidebar-open'));
            return;
        }
        setDesktopCollapsed(!body.classList.contains('sidebar-collapsed'));
    }

    function resetOnBreakpointChange() {
        body.classList.remove('sidebar-open', 'sidebar-collapsed');
        body.style.overflow = '';
        setOverlayVisible(false);
        updateAria();
    }

    if (toggle) {
        toggle.addEventListener('click', toggleSidebar);
    }

    if (closeBtn) {
        closeBtn.addEventListener('click', closeSidebar);
    }

    if (overlay) {
        overlay.addEventListener('click', closeSidebar);
    }

    document.querySelectorAll('.sidebar .nav-item a').forEach(function (link) {
        link.addEventListener('click', function () {
            if (isMobile()) {
                closeSidebar();
            }
        });
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && body.classList.contains('sidebar-open')) {
            closeSidebar();
        }
    });

    if (typeof mq.addEventListener === 'function') {
        mq.addEventListener('change', resetOnBreakpointChange);
    } else if (typeof mq.addListener === 'function') {
        mq.addListener(resetOnBreakpointChange);
    }

    updateAria();
})();
