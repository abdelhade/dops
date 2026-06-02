(function () {
    function findCreateLink() {
        return document.querySelector('.header-actions a.btn-primary[href*="/create"]');
    }

    function findSaveButton() {
        var saveBtn = document.querySelector(
            '.page-content-body .form-actions button[type="submit"].btn-primary'
        );
        if (saveBtn) {
            return saveBtn;
        }

        return document.querySelector('.guest-wrapper form button[type="submit"]');
    }

    document.addEventListener('keydown', function (event) {
        if (event.defaultPrevented || event.ctrlKey || event.altKey || event.metaKey || event.shiftKey) {
            return;
        }

        if (event.key === 'F3') {
            var createLink = findCreateLink();
            if (createLink) {
                event.preventDefault();
                createLink.click();
            }
            return;
        }

        if (event.key === 'F12') {
            var saveBtn = findSaveButton();
            if (saveBtn && !saveBtn.disabled) {
                event.preventDefault();
                saveBtn.click();
            }
        }
    });
})();
