import './bootstrap';
import 'bootstrap/dist/js/bootstrap.bundle.min.js';
import 'admin-lte/dist/js/adminlte.esm.js';
import $ from 'jquery';

document.querySelectorAll('.toast').forEach((element) => {
    window.bootstrap.Toast.getOrCreateInstance(element).show();
});

window.$ = window.jQuery = $;

const initializeSelect2 = async () => {
    const select2 = await import('select2');
    if (!$.fn.select2 && typeof select2.default === 'function') {
        select2.default(window, $);
    }

    $('[data-select2="true"]').each(function () {
        const element = $(this);

        element.select2({
            width: '100%',
            placeholder: element.data('placeholder'),
            allowClear: element.data('allow-clear') === true,
            minimumResultsForSearch: element.data('minimum-results-for-search'),
        });
    });
};

void initializeSelect2();
