@props([
    'id',
    'name' => 'image',
    'label' => 'Image',
    'hint' => 'JPG, PNG, or WebP up to 5 MB.',
    'accept' => 'image/jpeg,image/png,image/webp',
    'currentUrl' => null,
    'currentAlt' => 'Current image',
    'previewAlt' => 'Selected image preview',
    'previewClass' => 'image-upload-preview',
    'previewWidth' => '8rem',
    'previewHeight' => '8rem',
    'previewFit' => 'cover',
    'removable' => false,
    'removeName' => 'remove_image',
    'removeLabel' => 'Remove current image',
    'removeChecked' => false,
])

@php
    $previewId = $id . '-preview';
    $hintId = $id . '-hint';
    $errorId = $id . '-error';
    $hasError = $errors->has($name);
@endphp

<div {{ $attributes->class(['mb-3', 'image-upload'])->merge(['data-image-upload' => '']) }}>
    <label for="{{ $id }}" class="form-label">{{ $label }} <span class="text-secondary">(optional)</span></label>

    <label for="{{ $id }}" class="image-upload-dropzone" data-image-dropzone>
        <input id="{{ $id }}" name="{{ $name }}" type="file" accept="{{ $accept }}"
            class="image-upload-input visually-hidden"
            data-image-preview-input
            aria-describedby="{{ $hint ? $hintId : '' }}{{ $hasError ? ' '.$errorId : '' }}"
            @if ($hasError) aria-invalid="true" @endif>
        <span class="image-upload-dropzone-icon" aria-hidden="true">
            <i class="bi bi-cloud-arrow-up"></i>
        </span>
        <span class="image-upload-dropzone-title">Drag and drop an image, or <span>browse files</span></span>
        <span class="image-upload-dropzone-copy">Choose a clear, well-lit photo</span>
    </label>

    @if ($hint)
        <div id="{{ $hintId }}" class="form-text image-upload-hint">{{ $hint }}</div>
    @endif
    @if ($hasError)
        <div id="{{ $errorId }}" class="invalid-feedback d-block">{{ $errors->first($name) }}</div>
    @endif

    <div class="image-upload-preview-panel" data-image-preview-panel @if (! $currentUrl || $removeChecked) hidden @endif>
        <img id="{{ $previewId }}" @if ($currentUrl) src="{{ $currentUrl }}" @endif
            data-image-preview data-original-src="{{ $currentUrl ?? '' }}" data-original-alt="{{ $currentAlt }}"
            class="{{ $previewClass }}"
            style="width: {{ $previewWidth }}; height: {{ $previewHeight }}; object-fit: {{ in_array($previewFit, ['cover', 'contain', 'fill', 'none', 'scale-down'], true) ? $previewFit : 'cover' }};"
            alt="{{ $currentUrl ? $currentAlt : $previewAlt }}">
        <div class="image-upload-preview-details">
            <span class="image-upload-file-name" data-image-file-name>Current image</span>
            <span class="image-upload-file-size" data-image-file-size>Ready to keep</span>
        </div>
        <div class="image-upload-preview-actions">
            <button type="button" class="btn btn-sm btn-light border image-upload-action" data-image-change>
                <i class="bi bi-arrow-repeat me-1" aria-hidden="true"></i>Change
            </button>
            <button type="button" class="btn btn-sm btn-outline-danger image-upload-action" data-image-clear hidden>
                <i class="bi bi-x-lg me-1" aria-hidden="true"></i>Clear
            </button>
        </div>
    </div>

    @if ($removable)
        <input type="hidden" name="{{ $removeName }}" value="0">
        @if ($currentUrl)
            <div class="form-check image-upload-remove">
                <input id="{{ $id }}-remove" name="{{ $removeName }}" type="checkbox" value="1"
                    class="form-check-input" data-image-remove @checked($removeChecked)>
                <label for="{{ $id }}-remove" class="form-check-label">{{ $removeLabel }}</label>
            </div>
        @endif
    @endif
</div>
