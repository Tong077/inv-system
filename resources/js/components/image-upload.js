const formatFileSize = (bytes) => {
    if (bytes < 1024) return `${bytes} bytes`;

    const units = ['KB', 'MB', 'GB'];
    let size = bytes / 1024;
    let unit = units.shift();

    while (size >= 1024 && units.length) {
        size /= 1024;
        unit = units.shift();
    }

    return `${size.toFixed(size < 10 ? 1 : 0)} ${unit}`;
};

const setPreviewVisibility = (component, visible) => {
    const previewPanel = component.querySelector('[data-image-preview-panel]');
    const dropzone = component.querySelector('[data-image-dropzone]');

    previewPanel.hidden = !visible;
    dropzone.hidden = visible;
};

document.addEventListener('change', (event) => {
    const control = event.target;
    if (!(control instanceof HTMLInputElement)) return;

    const component = control.closest('[data-image-upload]');
    if (!component) return;

    const preview = component.querySelector('[data-image-preview]');
    if (!preview) return;

    if (control.matches('[data-image-preview-input]')) {
        const file = control.files?.[0];
        if (!file) return;

        if (preview.dataset.objectUrl) URL.revokeObjectURL(preview.dataset.objectUrl);
        const objectUrl = URL.createObjectURL(file);
        preview.dataset.objectUrl = objectUrl;
        preview.src = objectUrl;
        preview.alt = file.name;
        component.querySelector('[data-image-file-name]').textContent = file.name;
        component.querySelector('[data-image-file-size]').textContent = formatFileSize(file.size);
        component.querySelector('[data-image-clear]').hidden = false;
        setPreviewVisibility(component, true);

        const removeInput = component.querySelector('[data-image-remove]');
        if (removeInput) removeInput.checked = false;
        return;
    }

    if (control.matches('[data-image-remove]')) {
        setPreviewVisibility(component, !control.checked);
    }
});

document.addEventListener('click', (event) => {
    const control = event.target.closest('[data-image-change], [data-image-clear]');
    if (!control) return;

    const component = control.closest('[data-image-upload]');
    const input = component?.querySelector('[data-image-preview-input]');
    const preview = component?.querySelector('[data-image-preview]');
    if (!input || !preview) return;

    if (control.matches('[data-image-change]')) {
        input.click();
        return;
    }

    if (preview.dataset.objectUrl) URL.revokeObjectURL(preview.dataset.objectUrl);
    delete preview.dataset.objectUrl;
    input.value = '';
    preview.src = preview.dataset.originalSrc;
    preview.alt = preview.dataset.originalAlt;
    component.querySelector('[data-image-file-name]').textContent = 'Current image';
    component.querySelector('[data-image-file-size]').textContent = 'Ready to keep';
    control.hidden = true;

    if (preview.dataset.originalSrc) {
        setPreviewVisibility(component, true);
    } else {
        setPreviewVisibility(component, false);
    }
});

document.addEventListener('dragover', (event) => {
    const dropzone = event.target.closest?.('[data-image-dropzone]');
    if (!dropzone) return;

    event.preventDefault();
    dropzone.classList.add('is-dragover');
});

document.addEventListener('dragleave', (event) => {
    const dropzone = event.target.closest?.('[data-image-dropzone]');
    if (dropzone && !dropzone.contains(event.relatedTarget)) {
        dropzone.classList.remove('is-dragover');
    }
});

document.addEventListener('drop', (event) => {
    const dropzone = event.target.closest?.('[data-image-dropzone]');
    if (!dropzone) return;

    event.preventDefault();
    dropzone.classList.remove('is-dragover');

    const component = dropzone.closest('[data-image-upload]');
    const input = component?.querySelector('[data-image-preview-input]');
    const files = event.dataTransfer?.files;
    if (!input || !files?.length) return;

    input.files = files;
    input.dispatchEvent(new Event('change', { bubbles: true }));
});

window.addEventListener('beforeunload', () => {
    document.querySelectorAll('[data-image-preview][data-object-url]').forEach((preview) => {
        URL.revokeObjectURL(preview.dataset.objectUrl);
    });
});
