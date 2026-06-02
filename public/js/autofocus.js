(function () {
    var FOCUSABLE_SELECTOR = [
        'input.form-control:not([type="hidden"]):not([readonly]):not([disabled])',
        'select.form-control:not([disabled])',
        'textarea.form-control:not([readonly]):not([disabled])',
    ].join(', ');

    function getMainForm() {
        var roots = document.querySelectorAll('.page-content-body form, .guest-wrapper form');

        for (var i = 0; i < roots.length; i++) {
            if (roots[i].querySelector('.form-control')) {
                return roots[i];
            }
        }

        return null;
    }

    function isVisibleField(field) {
        if (!field || field.type === 'checkbox' || field.type === 'radio') {
            return false;
        }

        var style = window.getComputedStyle(field);

        return style.display !== 'none' && style.visibility !== 'hidden';
    }

    function findFirstFocusable(form) {
        var existing = form.querySelector('[autofocus]');

        if (existing && isVisibleField(existing) && !existing.disabled && !existing.readOnly) {
            return existing;
        }

        var fields = form.querySelectorAll(FOCUSABLE_SELECTOR);

        for (var i = 0; i < fields.length; i++) {
            if (isVisibleField(fields[i])) {
                return fields[i];
            }
        }

        return null;
    }

    function initAutofocus() {
        var form = getMainForm();

        if (!form) {
            return;
        }

        var target = findFirstFocusable(form);

        if (!target) {
            return;
        }

        if (document.activeElement && document.activeElement !== document.body) {
            return;
        }

        requestAnimationFrame(function () {
            try {
                target.focus({ preventScroll: false });
            } catch (error) {
                target.focus();
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAutofocus);
    } else {
        initAutofocus();
    }
})();
