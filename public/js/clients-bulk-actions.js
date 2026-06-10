(function ($) {
    'use strict';

    var $root = $('#clientsBulkRoot');
    if (!$root.length) {
        return;
    }

    var config = {
        url: $root.data('bulk-url'),
        csrfToken: $root.data('bulk-csrf'),
    };
    var lang = window.CLIENTS_BULK_LANG || {};

    var $bar = $('#clientsBulkBar');
    var $count = $('#clientsBulkCount');
    var $selectAll = $('#clientsBulkSelectAll');
    var $deleteBtn = $('#clientsBulkDeleteBtn');
    var $tableBody = $('#clientsBulkTableBody');

    function getRowCheckboxes() {
        return $tableBody.find('.clients-bulk-row-cb');
    }

    function getSelectedIds() {
        return getRowCheckboxes()
            .filter(':checked')
            .map(function () {
                return parseInt($(this).val(), 10);
            })
            .get();
    }

    function updateBarState() {
        var selected = getSelectedIds();
        var total = getRowCheckboxes().length;

        if (selected.length === 0) {
            $bar.prop('hidden', true);
            $count.text('');
            $selectAll.prop('checked', false).prop('indeterminate', false);
            return;
        }

        $bar.prop('hidden', false);
        $count.text(
            (lang.selectedCount || ':count selected').replace(':count', String(selected.length))
        );

        if (selected.length === total) {
            $selectAll.prop('checked', true).prop('indeterminate', false);
        } else {
            $selectAll.prop('checked', false).prop('indeterminate', true);
        }
    }

    function showAlert(type, message) {
        var icon = type === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation';
        var alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        var $alert = $(
            '<div class="alert ' + alertClass + ' clients-bulk-flash">' +
                '<i class="fa-solid ' + icon + '"></i>' +
                '<div></div>' +
            '</div>'
        );

        $alert.find('div').text(message);
        $root.find('.clients-bulk-flash').remove();
        $root.prepend($alert);

        window.setTimeout(function () {
            $alert.fadeOut(200, function () {
                $(this).remove();
            });
        }, 5000);
    }

    function removeRows(ids) {
        ids.forEach(function (id) {
            $tableBody.find('.clients-bulk-row-cb[value="' + id + '"]').closest('tr').remove();
        });

        if ($tableBody.find('tr').length === 0) {
            var colCount = $root.find('.custom-table thead th').length;
            $tableBody.html(
                '<tr>' +
                    '<td colspan="' + colCount + '" class="empty-state">' +
                        '<i class="fa-solid fa-user-tie"></i> ' +
                        (lang.noClients || '') +
                    '</td>' +
                '</tr>'
            );
        }

        updateBarState();
    }

    $selectAll.on('change', function () {
        var checked = $(this).is(':checked');
        getRowCheckboxes().prop('checked', checked);
        updateBarState();
    });

    $tableBody.on('change', '.clients-bulk-row-cb', updateBarState);

    $deleteBtn.on('click', function () {
        var ids = getSelectedIds();

        if (ids.length === 0) {
            showAlert('error', lang.noSelection || 'No records selected.');
            return;
        }

        if (!window.confirm(lang.confirmDelete || 'Are you sure?')) {
            return;
        }

        $deleteBtn.prop('disabled', true);

        $.ajax({
            url: config.url,
            method: 'POST',
            dataType: 'json',
            data: {
                _token: config.csrfToken,
                ids: ids,
            },
        })
            .done(function (response) {
                if (response.deleted_ids && response.deleted_ids.length) {
                    removeRows(response.deleted_ids);
                }

                showAlert(response.success ? 'success' : 'error', response.message || '');

                if (!response.success && response.deleted === 0) {
                    getRowCheckboxes().prop('checked', false);
                    updateBarState();
                }
            })
            .fail(function () {
                showAlert('error', lang.deleteFailed || 'Bulk delete failed.');
            })
            .always(function () {
                $deleteBtn.prop('disabled', false);
            });
    });
})(jQuery);
