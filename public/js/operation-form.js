(function () {
    function updateQuantityPerSheet() {
        const jobSizeEl = document.getElementById('job_size');
        const pullCountEl = document.getElementById('pull_count');
        const qtyEl = document.getElementById('quantity_per_sheet');

        if (!jobSizeEl || !pullCountEl || !qtyEl) {
            return;
        }

        const jobSize = parseFloat(jobSizeEl.value);
        const pullCount = parseInt(pullCountEl.value, 10);

        if (!jobSize || jobSize <= 0 || Number.isNaN(pullCount) || pullCount < 0) {
            qtyEl.value = '';
            return;
        }

        qtyEl.value = String(Math.ceil(pullCount / jobSize));
    }

    function buildCreateLabel(template, name, escape) {
        const safeName = '<strong>' + escape(name) + '</strong>';

        return template.replace(':name', safeName);
    }

    function moveCreateOptionToEnd(tomSelect) {
        const content = tomSelect.dropdown?.querySelector('.ts-dropdown-content');
        const createOption = tomSelect.dropdown?.querySelector('.create');

        if (content && createOption) {
            content.appendChild(createOption);
        }
    }

    function optionExists(tomSelect, value) {
        const normalized = value.trim().toLowerCase();

        return Object.values(tomSelect.options).some(function (option) {
            return (option.text || '').trim().toLowerCase() === normalized;
        });
    }

    function createOptionRemotely(form, type, name, callback) {
        const createUrl = form?.dataset.optionCreateUrl;
        const csrfToken = form?.querySelector('input[name="_token"]')?.value;
        const failedMessage = form?.dataset.optionCreateFailed || 'Could not add the option.';

        if (!createUrl || !csrfToken) {
            callback();
            return;
        }

        fetch(createUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({
                type: type,
                name: name,
            }),
        })
            .then(function (response) {
                return response.json().then(function (data) {
                    return { ok: response.ok, data: data };
                });
            })
            .then(function (result) {
                if (!result.ok || !result.data?.id) {
                    const message = result.data?.message
                        || result.data?.errors?.name?.[0]
                        || failedMessage;

                    window.alert(message);
                    callback();
                    return;
                }

                callback({
                    value: String(result.data.id),
                    text: result.data.name,
                });
            })
            .catch(function () {
                window.alert(failedMessage);
                callback();
            });
    }

    function initSearchableSelects() {
        if (typeof TomSelect === 'undefined') {
            return;
        }

        const formRoot = document.querySelector('.operation-form-compact');

        if (!formRoot) {
            return;
        }

        const form = document.getElementById('operation-form');
        const searchPlaceholder = form?.dataset.selectSearch || 'Search...';
        const noResultsText = form?.dataset.selectNoResults || 'No results found';
        const createLabelTemplate = form?.dataset.optionCreateLabel || 'Add ":name"';
        const direction = document.documentElement.getAttribute('dir') === 'rtl' ? 'rtl' : 'ltr';

        formRoot.querySelectorAll('select.form-control').forEach(function (select) {
            if (select.tomselect) {
                return;
            }

            const allowCreateType = select.dataset.allowCreate || '';
            const canCreate = allowCreateType !== '' && !!form?.dataset.optionCreateUrl;

            const config = {
                plugins: ['dropdown_input'],
                allowEmptyOption: true,
                create: false,
                maxOptions: null,
                direction: direction,
                dropdownClass: 'operation-select-dropdown',
                wrapperClass: 'operation-select-wrapper',
                onDropdownOpen: function () {
                    const input = this.dropdown?.querySelector('.dropdown-input');

                    if (input) {
                        input.placeholder = searchPlaceholder;
                    }

                    moveCreateOptionToEnd(this);
                },
                onType: function () {
                    moveCreateOptionToEnd(this);
                },
                render: {
                    no_results: function (_data, escape) {
                        return '<div class="no-results">' + escape(noResultsText) + '</div>';
                    },
                },
            };

            if (canCreate) {
                config.create = function (input, callback) {
                    const name = input.trim();

                    if (name === '') {
                        callback();
                        return;
                    }

                    if (optionExists(this, name)) {
                        callback();
                        return;
                    }

                    createOptionRemotely(form, allowCreateType, name, callback);
                };

                config.createOnBlur = false;

                config.createFilter = function (input) {
                    const name = input.trim();

                    return name !== '' && !optionExists(this, name);
                };

                config.render.option_create = function (data, escape) {
                    return '<div class="create">' + buildCreateLabel(createLabelTemplate, data.input, escape) + '</div>';
                };
            }

            new TomSelect(select, config);
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        initSearchableSelects();

        const jobSizeEl = document.getElementById('job_size');
        const pullCountEl = document.getElementById('pull_count');

        if (jobSizeEl && pullCountEl) {
            ['input', 'change'].forEach(function (eventName) {
                jobSizeEl.addEventListener(eventName, updateQuantityPerSheet);
                pullCountEl.addEventListener(eventName, updateQuantityPerSheet);
            });

            updateQuantityPerSheet();
        }
    });
})();
