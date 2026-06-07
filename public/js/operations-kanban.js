(function ($, Sortable) {
    'use strict';

    if (!$ || !Sortable || !window.OPS_KANBAN_CONFIG) {
        return;
    }

    var config = window.OPS_KANBAN_CONFIG;
    var lang = window.OPS_KANBAN_LANG || {};
    var columnState = {};
    var sortableInstances = [];
    var toastTimer = null;

    function escapeHtml(value) {
        return String(value == null ? '' : value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function getFilters() {
        return {
            search: $('#opsKanbanSearch').val() || '',
            date_from: $('#opsKanbanDateFrom').val() || '',
            date_to: $('#opsKanbanDateTo').val() || '',
        };
    }

    function showToast(message, type) {
        var $toast = $('#opsKanbanToast');
        $toast.removeClass('is-success is-error')
            .addClass(type === 'error' ? 'is-error' : 'is-success')
            .text(message)
            .fadeIn(150);

        if (toastTimer) {
            clearTimeout(toastTimer);
        }

        toastTimer = setTimeout(function () {
            $toast.fadeOut(200);
        }, 2800);
    }

    function updateColumnCount($column, total) {
        $column.find('[data-ops-kanban-count]').text(
            (lang.operationsCount || ':count operations').replace(':count', total)
        );
    }

    function buildCardHtml(operation) {
        var chips = [];

        if (operation.quantity != null) {
            chips.push('<span class="ops-kanban-card-chip">' + escapeHtml(lang.quantity) + ': ' + escapeHtml(operation.quantity) + '</span>');
        }
        if (operation.color_count != null) {
            chips.push('<span class="ops-kanban-card-chip">' + escapeHtml(lang.colors) + ': ' + escapeHtml(operation.color_count) + '</span>');
        }
        if (operation.pull_count != null) {
            chips.push('<span class="ops-kanban-card-chip">' + escapeHtml(lang.pulls) + ': ' + escapeHtml(operation.pull_count) + '</span>');
        }
        if (operation.paper_type_name) {
            chips.push('<span class="ops-kanban-card-chip">' + escapeHtml(operation.paper_type_name) + '</span>');
        }
        if (operation.printing_supplier_name) {
            chips.push('<span class="ops-kanban-card-chip">' + escapeHtml(operation.printing_supplier_name) + '</span>');
        }
        if (Array.isArray(operation.services)) {
            operation.services.forEach(function (serviceName) {
                chips.push('<span class="ops-kanban-card-chip">' + escapeHtml(serviceName) + '</span>');
            });
        }

        var dateLabel = operation.operation_date || config.dash;
        if (operation.operation_time) {
            dateLabel += ' · ' + operation.operation_time;
        }

        var statementHtml = operation.statement
            ? '<p class="ops-kanban-card-statement">' + escapeHtml(operation.statement) + '</p>'
            : '';

        var readonlyClass = config.canDrag ? '' : ' is-readonly';

        return (
            '<article class="ops-kanban-card' + readonlyClass + '" data-operation-id="' + escapeHtml(operation.id) + '">' +
                '<div class="ops-kanban-card-header">' +
                    '<a href="' + escapeHtml(operation.show_url) + '" class="ops-kanban-card-number">' + escapeHtml(operation.operation_number) + '</a>' +
                    '<span class="ops-kanban-card-date">' + escapeHtml(dateLabel) + '</span>' +
                '</div>' +
                '<div class="ops-kanban-card-item">' + escapeHtml(operation.item_name || config.dash) + '</div>' +
                (operation.client_name ? '<div class="ops-kanban-card-item" style="font-size:0.78rem;font-weight:500;color:var(--text-secondary);">' + escapeHtml(operation.client_name) + '</div>' : '') +
                '<div class="ops-kanban-card-meta">' + chips.join('') + '</div>' +
                statementHtml +
            '</article>'
        );
    }

    function getColumnElements($column) {
        return {
            $column: $column,
            $list: $column.find('[data-ops-kanban-list]'),
            statusId: String($column.data('status-id')),
        };
    }

    function renderLoading($list) {
        $list.find('.ops-kanban-loading, .ops-kanban-empty, .ops-kanban-load-more-wrap').remove();
        $list.append('<div class="ops-kanban-loading"><i class="fa-solid fa-spinner fa-spin"></i> ' + escapeHtml(lang.loading) + '</div>');
    }

    function renderEmpty($list) {
        $list.find('.ops-kanban-loading, .ops-kanban-empty, .ops-kanban-load-more-wrap').remove();
        if ($list.find('.ops-kanban-card').length === 0) {
            $list.append('<div class="ops-kanban-empty">' + escapeHtml(lang.emptyColumn) + '</div>');
        }
    }

    function renderLoadMore($list, statusId) {
        $list.find('.ops-kanban-load-more-wrap').remove();
        $list.append(
            '<div class="ops-kanban-load-more-wrap">' +
                '<button type="button" class="btn btn-secondary btn-sm" data-ops-kanban-load-more data-status-id="' + escapeHtml(statusId) + '">' +
                    '<i class="fa-solid fa-arrow-down"></i> ' + escapeHtml(lang.loadMore) +
                '</button>' +
            '</div>'
        );
    }

    function appendOperations($list, operations) {
        operations.forEach(function (operation) {
            $list.append(buildCardHtml(operation));
        });
    }

    function loadColumnPage(statusId, page, replace) {
        var $column = $('[data-ops-kanban-column][data-status-id="' + statusId + '"]');
        var parts = getColumnElements($column);
        var state = columnState[statusId] || { page: 1, hasMore: true, loading: false, total: 0 };

        if (state.loading) {
            return $.Deferred().reject().promise();
        }

        state.loading = true;
        columnState[statusId] = state;

        if (replace) {
            parts.$list.empty();
            renderLoading(parts.$list);
        } else {
            parts.$list.find('.ops-kanban-load-more-wrap').remove();
            parts.$list.append('<div class="ops-kanban-loading" data-ops-kanban-temp-loading><i class="fa-solid fa-spinner fa-spin"></i> ' + escapeHtml(lang.loading) + '</div>');
        }

        return $.ajax({
            url: config.loadUrl,
            method: 'GET',
            dataType: 'json',
            data: $.extend({ operation_status_id: statusId, page: page }, getFilters()),
        }).done(function (response) {
            parts.$list.find('.ops-kanban-loading, [data-ops-kanban-temp-loading]').remove();

            if (replace) {
                parts.$list.empty();
            }

            appendOperations(parts.$list, response.operations || []);

            state.page = page;
            state.hasMore = !!response.has_more;
            state.total = response.total || 0;
            state.loading = false;
            columnState[statusId] = state;

            updateColumnCount(parts.$column, state.total);

            if (state.hasMore) {
                renderLoadMore(parts.$list, statusId);
            }

            renderEmpty(parts.$list);
        }).fail(function () {
            parts.$list.find('.ops-kanban-loading, [data-ops-kanban-temp-loading]').remove();
            state.loading = false;
            columnState[statusId] = state;
            renderEmpty(parts.$list);
        });
    }

    function reloadAllColumns() {
        $('[data-ops-kanban-column]').each(function () {
            var statusId = String($(this).data('status-id'));
            columnState[statusId] = { page: 1, hasMore: true, loading: false, total: 0 };
            loadColumnPage(statusId, 1, true);
        });
    }

    function initLazyScroll() {
        $('[data-ops-kanban-list]').on('scroll', function () {
            var $list = $(this);
            var $column = $list.closest('[data-ops-kanban-column]');
            var statusId = String($column.data('status-id'));
            var state = columnState[statusId];

            if (!state || state.loading || !state.hasMore) {
                return;
            }

            var threshold = 80;
            var scrollBottom = $list[0].scrollHeight - $list.scrollTop() - $list.outerHeight();

            if (scrollBottom <= threshold) {
                loadColumnPage(statusId, (state.page || 1) + 1, false);
            }
        });
    }

    function initSortable() {
        sortableInstances.forEach(function (instance) {
            instance.destroy();
        });
        sortableInstances = [];

        if (!config.canDrag) {
            return;
        }

        $('[data-ops-kanban-list]').each(function () {
            var instance = Sortable.create(this, {
                group: 'ops-kanban-group',
                animation: 160,
                draggable: '.ops-kanban-card',
                ghostClass: 'is-dragging',
                dragClass: 'is-dragging',
                onStart: function () {
                    $(this.el).addClass('is-drag-over');
                },
                onEnd: function (evt) {
                    $(evt.from).removeClass('is-drag-over');
                    $(evt.to).removeClass('is-drag-over');

                    var $item = $(evt.item);
                    var operationId = $item.data('operation-id');
                    var $fromColumn = $(evt.from).closest('[data-ops-kanban-column]');
                    var $toColumn = $(evt.to).closest('[data-ops-kanban-column]');
                    var fromStatusId = String($fromColumn.data('status-id'));
                    var toStatusId = String($toColumn.data('status-id'));

                    $(evt.from).find('.ops-kanban-empty').remove();
                    $(evt.to).find('.ops-kanban-empty').remove();
                    renderEmpty($(evt.from));
                    renderEmpty($(evt.to));

                    if (fromStatusId === toStatusId) {
                        return;
                    }

                    var updateUrl = config.statusUpdateUrlTemplate.replace('__ID__', operationId);

                    $.ajax({
                        url: updateUrl,
                        method: 'PATCH',
                        dataType: 'json',
                        headers: {
                            'X-CSRF-TOKEN': config.csrfToken,
                            'Accept': 'application/json',
                        },
                        data: {
                            operation_status_id: toStatusId,
                        },
                    }).done(function (response) {
                        if (columnState[fromStatusId]) {
                            columnState[fromStatusId].total = Math.max(0, (columnState[fromStatusId].total || 0) - 1);
                            updateColumnCount($fromColumn, columnState[fromStatusId].total);
                        }
                        if (columnState[toStatusId]) {
                            columnState[toStatusId].total = (columnState[toStatusId].total || 0) + 1;
                            updateColumnCount($toColumn, columnState[toStatusId].total);
                        }

                        if (response && response.message) {
                            showToast(response.message, 'success');
                        }
                    }).fail(function (xhr) {
                        if (evt.from && evt.item) {
                            if (evt.oldIndex != null && evt.oldIndex < evt.from.children.length) {
                                evt.from.insertBefore(evt.item, evt.from.children[evt.oldIndex]);
                            } else {
                                evt.from.appendChild(evt.item);
                            }
                        }

                        renderEmpty($(evt.from));
                        renderEmpty($(evt.to));

                        var message = (xhr.responseJSON && xhr.responseJSON.message)
                            ? xhr.responseJSON.message
                            : lang.statusUpdateFailed;
                        showToast(message, 'error');
                    });
                },
            });

            sortableInstances.push(instance);
        });
    }

    function bindEvents() {
        $('#opsKanbanFiltersForm').on('submit', function (event) {
            event.preventDefault();
            reloadAllColumns();
        });

        $('#opsKanbanClearFilters').on('click', function () {
            $('#opsKanbanSearch').val('');
            $('#opsKanbanDateFrom').val('');
            $('#opsKanbanDateTo').val('');
            reloadAllColumns();
        });

        $('#opsKanbanRefreshBtn').on('click', function () {
            reloadAllColumns();
        });

        $(document).on('click', '[data-ops-kanban-load-more]', function () {
            var statusId = String($(this).data('status-id'));
            var state = columnState[statusId] || { page: 1 };
            loadColumnPage(statusId, (state.page || 1) + 1, false);
        });
    }

    $(function () {
        bindEvents();
        initLazyScroll();
        reloadAllColumns();
        initSortable();
    });
})(window.jQuery, window.Sortable);
