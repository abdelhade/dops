(function () {
    function submitImportForm(form) {
        if (typeof form.requestSubmit === 'function') {
            form.requestSubmit();
            return;
        }

        form.submit();
    }

    document.addEventListener('change', function (event) {
        var input = event.target;

        if (!input.matches('.spreadsheet-import-file')) {
            return;
        }

        if (!input.files || !input.files.length) {
            return;
        }

        var form = input.closest('form');

        if (form) {
            submitImportForm(form);
        }
    });
})();
