import './bootstrap';
import './components/date-picker';
import './components/image-upload';
import './components/menu-item-sale-mode';
import * as bootstrap from 'bootstrap';
import 'admin-lte/dist/js/adminlte.esm.js';
import $ from 'jquery';

window.bootstrap = bootstrap;

document.querySelectorAll('.toast').forEach((element) => {
    bootstrap.Toast.getOrCreateInstance(element).show();
});

document.addEventListener('submit', (event) => {
    const form = event.target.closest('form[data-swal-confirm]');

    if (!form) return;

    if (form.dataset.swalConfirmed === 'true') {
        delete form.dataset.swalConfirmed;
        return;
    }

    event.preventDefault();
    if (form.dataset.swalPending === 'true') return;

    form.dataset.swalPending = 'true';

    void (async () => {
        try {
            const [sweetAlertModule] = await Promise.all([
                import('sweetalert2'),
                import('sweetalert2/dist/sweetalert2.min.css'),
            ]);
            const result = await sweetAlertModule.default.fire({
                title: form.dataset.swalTitle || 'Delete this record?',
                text: form.dataset.swalText || 'This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: form.dataset.swalConfirmText || 'Yes, delete it',
                cancelButtonText: form.dataset.swalCancelText || 'Cancel',
                reverseButtons: true,
                focusCancel: true,
                buttonsStyling: false,
                customClass: {
                    popup: 'app-swal-popup',
                    confirmButton: 'btn btn-danger',
                    cancelButton: 'btn btn-light',
                },
            });

            if (result.isConfirmed) {
                form.dataset.swalConfirmed = 'true';
                form.requestSubmit();
            }
        } catch (error) {
            console.error('Unable to load the delete confirmation dialog.', error);
        } finally {
            delete form.dataset.swalPending;
        }
    })();
});

const liveSearchUrl = (form) => {
    const url = new URL(form.action, window.location.origin);

    for (const [key, value] of new FormData(form).entries()) {
        if (typeof value !== 'string' || value.trim() === '') {
            url.searchParams.delete(key);
        } else {
            url.searchParams.set(key, value);
        }
    }

    url.searchParams.delete('page');

    return url;
};

document.querySelectorAll('form[data-live-search="true"]').forEach((form) => {
    const results = document.querySelector(form.dataset.liveTarget);

    if (!results) return;

    let debounceTimer;
    let activeRequest;
    const delay = Number(form.dataset.liveDelay) || 300;

    const loadResults = async (url, historyMode = 'replace') => {
        activeRequest?.abort();
        const requestController = new AbortController();
        activeRequest = requestController;
        results.setAttribute('aria-busy', 'true');

        try {
            const response = await fetch(url, {
                headers: {
                    'X-Live-Search': 'true',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                signal: requestController.signal,
            });

            if (!response.ok) throw new Error(`Live search failed with status ${response.status}.`);

            const responseDocument = new DOMParser().parseFromString(await response.text(), 'text/html');
            const currentCard = results.querySelector('.data-table-card');
            const nextCard = responseDocument.querySelector('.data-table-card');
            const currentBody = currentCard?.querySelector('.card-body');
            const nextBody = nextCard?.querySelector('.card-body');

            if (!currentCard || !nextCard || !currentBody || !nextBody) {
                throw new Error('Live search response did not contain the expected results card.');
            }

            currentBody.replaceWith(nextBody);

            const currentFooter = currentCard.querySelector('.card-footer');
            const nextFooter = nextCard.querySelector('.card-footer');

            if (currentFooter && nextFooter) {
                currentFooter.replaceWith(nextFooter);
            } else if (currentFooter) {
                currentFooter.remove();
            } else if (nextFooter) {
                currentCard.append(nextFooter);
            }

            const currentDrawers = results.querySelector('[data-live-edit-drawers]');
            const nextDrawers = responseDocument.querySelector('[data-live-edit-drawers]');

            if (!currentDrawers || !nextDrawers) {
                throw new Error('Live search response did not contain the category drawers.');
            }

            currentDrawers.replaceWith(nextDrawers);

            if (historyMode === 'push') {
                window.history.pushState({ liveSearch: true }, '', url);
            } else if (historyMode === 'replace') {
                window.history.replaceState({ liveSearch: true }, '', url);
            }
        } catch (error) {
            if (error.name !== 'AbortError') {
                window.location.assign(url);
            }
        } finally {
            if (activeRequest === requestController && !requestController.signal.aborted) {
                results.setAttribute('aria-busy', 'false');
            }
        }
    };

    const scheduleSearch = (immediate = false) => {
        window.clearTimeout(debounceTimer);
        debounceTimer = window.setTimeout(
            () => loadResults(liveSearchUrl(form)),
            immediate ? 0 : delay,
        );
    };

    form.addEventListener('input', (event) => {
        if (event.target.matches('input[type="search"]')) scheduleSearch();
    });

    form.addEventListener('change', (event) => {
        if (event.target.matches('select, input:not([type="search"])')) scheduleSearch(true);
    });

    form.addEventListener('submit', (event) => {
        event.preventDefault();
        scheduleSearch(true);
    });

    form.addEventListener('click', (event) => {
        const clearLink = event.target.closest('.search-filter-clear');

        if (!clearLink) return;

        event.preventDefault();
        form.querySelectorAll('input[type="search"], select').forEach((field) => {
            field.value = field.dataset.resetValue ?? '';
            field.dispatchEvent(new Event('change', { bubbles: true }));
        });
        scheduleSearch(true);
    });

    results.addEventListener('click', (event) => {
        const pageLink = event.target.closest('.pagination a[href]');

        if (!pageLink) return;

        event.preventDefault();
        loadResults(new URL(pageLink.href), 'push');
    });

    window.addEventListener('popstate', () => {
        const url = new URL(window.location.href);
        const searchUrl = new URL(form.action, window.location.origin);

        if (url.pathname !== searchUrl.pathname) return;

        form.querySelectorAll('[name]').forEach((field) => {
            if (field.type !== 'hidden') field.value = url.searchParams.get(field.name) ?? '';
        });

        loadResults(url, 'none');
    });
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

if (document.querySelector('[data-select2="true"]')) {
    void initializeSelect2();
}
