(function () {
    'use strict';

    function enhanceAdminInterface() {
        var main = document.querySelector('main');
        if (!main) return;

        main.querySelectorAll('.panel, .module-panel, .users-panel, .roles-panel').forEach(function (panel) {
            panel.classList.add('card');
        });

        main.querySelectorAll('.admin-table-wrap, .table-wrap').forEach(function (wrapper) {
            wrapper.classList.add('table-responsive');
        });

        main.querySelectorAll('.admin-table, .table-wrap table').forEach(function (table) {
            table.classList.add('table', 'table-hover', 'align-middle', 'mb-0');
        });

        main.querySelectorAll('input:not([type="hidden"]):not([type="checkbox"]):not([type="radio"]):not([type="range"]), textarea').forEach(function (control) {
            control.classList.add('form-control');
        });

        main.querySelectorAll('select').forEach(function (control) {
            control.classList.add('form-select');
        });

        main.querySelectorAll('input[type="checkbox"], input[type="radio"]').forEach(function (control) {
            control.classList.add('form-check-input');
        });

        main.querySelectorAll('.flash.success').forEach(function (message) {
            message.classList.add('alert', 'alert-success');
        });

        main.querySelectorAll('.flash.error').forEach(function (message) {
            message.classList.add('alert', 'alert-danger');
        });

        main.querySelectorAll('.table-status, .gateway-state, .result-count').forEach(function (status) {
            status.classList.add('badge');
        });

        main.querySelectorAll('button[type="submit"]:not(.btn):not(.modal-close)').forEach(function (button) {
            button.classList.add('btn', 'btn-primary');
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', enhanceAdminInterface);
    } else {
        enhanceAdminInterface();
    }
})();
