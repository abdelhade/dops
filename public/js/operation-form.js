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

    document.addEventListener('DOMContentLoaded', function () {
        const jobSizeEl = document.getElementById('job_size');
        const pullCountEl = document.getElementById('pull_count');

        if (!jobSizeEl || !pullCountEl) {
            return;
        }

        ['input', 'change'].forEach(function (eventName) {
            jobSizeEl.addEventListener(eventName, updateQuantityPerSheet);
            pullCountEl.addEventListener(eventName, updateQuantityPerSheet);
        });

        updateQuantityPerSheet();
    });
})();
