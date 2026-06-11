(function () {
    'use strict';

    var modal = document.getElementById('deletePasswordModal');
    if (!modal) {
        return;
    }

    var lang = window.DOBS_DELETE_LANG || {};
    var messageEl = document.getElementById('deletePasswordModalMessage');
    var formEl = document.getElementById('deletePasswordModalForm');
    var inputEl = document.getElementById('deletePasswordInput');
    var errorEl = document.getElementById('deletePasswordModalError');
    var confirmBtn = document.getElementById('deletePasswordModalConfirm');

    var pendingForm = null;
    var pendingCallback = null;

    function openModal(confirmMessage, targetForm, callback) {
        pendingForm = targetForm || null;
        pendingCallback = callback || null;

        if (messageEl) {
            messageEl.textContent = confirmMessage || lang.defaultConfirm || '';
        }

        if (inputEl) {
            inputEl.value = '';
        }

        hideError();
        modal.hidden = false;
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('delete-password-modal-open');

        window.setTimeout(function () {
            if (inputEl) {
                inputEl.focus();
            }
        }, 50);
    }

    function closeModal() {
        modal.hidden = true;
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('delete-password-modal-open');
        pendingForm = null;
        pendingCallback = null;
        hideError();
    }

    function showError(message) {
        if (!errorEl) {
            return;
        }

        errorEl.textContent = message;
        errorEl.hidden = false;
    }

    function hideError() {
        if (!errorEl) {
            return;
        }

        errorEl.textContent = '';
        errorEl.hidden = true;
    }

    function isDeleteForm(form) {
        if (!form || form.tagName !== 'FORM') {
            return false;
        }

        if (form.hasAttribute('data-dobs-delete')) {
            return true;
        }

        var methodInput = form.querySelector('input[name="_method"]');

        return !!methodInput && String(methodInput.value).toUpperCase() === 'DELETE';
    }

    function ensureHiddenPasswordField(form, password) {
        var field = form.querySelector('input[name="delete_password"]');

        if (!field) {
            field = document.createElement('input');
            field.type = 'hidden';
            field.name = 'delete_password';
            form.appendChild(field);
        }

        field.value = password;
    }

    function submitProtectedForm(form, password) {
        ensureHiddenPasswordField(form, password);
        form.dataset.dobsPasswordVerified = '1';
        form.submit();
    }

    modal.querySelectorAll('[data-delete-password-dismiss]').forEach(function (el) {
        el.addEventListener('click', closeModal);
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && !modal.hidden) {
            closeModal();
        }
    });

    document.addEventListener(
        'submit',
        function (event) {
            var form = event.target;

            if (!isDeleteForm(form)) {
                return;
            }

            if (form.dataset.dobsPasswordVerified === '1') {
                delete form.dataset.dobsPasswordVerified;
                return;
            }

            event.preventDefault();
            event.stopPropagation();

            var confirmMessage = form.getAttribute('data-dobs-confirm') || lang.defaultConfirm || '';
            openModal(confirmMessage, form, null);
        },
        true
    );

    if (formEl) {
        formEl.addEventListener('submit', function (event) {
            event.preventDefault();

            var password = inputEl ? inputEl.value : '';

            if (!password) {
                showError(lang.passwordRequired || '');
                return;
            }

            if (pendingCallback) {
                var callback = pendingCallback;
                closeModal();
                callback(password);
                return;
            }

            if (pendingForm) {
                var form = pendingForm;
                closeModal();
                submitProtectedForm(form, password);
            }
        });
    }

    window.dobsRequestDeletePassword = function (confirmMessage, callback) {
        if (typeof callback !== 'function') {
            return;
        }

        openModal(confirmMessage, null, callback);
    };
})();
