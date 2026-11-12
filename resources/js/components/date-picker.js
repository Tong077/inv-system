import flatpickr from 'flatpickr';

const datePickerSelector = 'input[type="date"], input[data-flatpickr="true"]';

const initializeDatePicker = (input) => {
    if (input._flatpickr) return;

    // Flatpickr uses a text input so the date can also be entered by hand.
    if (input.type === 'date') {
        input.type = 'text';
    }

    input.dataset.flatpickr = 'true';
    input.inputMode = 'numeric';
    input.placeholder ||= 'YYYY-MM-DD';

    flatpickr(input, {
        allowInput: true,
        dateFormat: input.dataset.dateFormat || 'Y-m-d',
        disableMobile: true,
    });
};

export const initializeDatePickers = (root = document) => {
    if (root instanceof Element && root.matches(datePickerSelector)) {
        initializeDatePicker(root);
    }

    root.querySelectorAll?.(datePickerSelector).forEach(initializeDatePicker);
};

initializeDatePickers();

const datePickerObserver = new MutationObserver((mutations) => {
    mutations.forEach(({ addedNodes }) => {
        addedNodes.forEach((node) => {
            if (node instanceof Element) initializeDatePickers(node);
        });
    });
});

datePickerObserver.observe(document.body, { childList: true, subtree: true });
