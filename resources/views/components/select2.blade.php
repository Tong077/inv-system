@props([
    'name',
    'options' => [],
    'value' => null,
    'label' => null,
    'labelHidden' => false,
    'placeholder' => 'Select an option',
    'required' => false,
    'disabled' => false,
    'multiple' => false,
    'allowClear' => true,
    'showPlaceholderOption' => true,
    'resetValue' => null,
    'minimumResultsForSearch' => 0,
    'containerClass' => '',
    'selectClass' => '',
    'helpText' => null,
])

@php
    $id = $attributes->get('id', \Illuminate\Support\Str::slug($name));
    $selectedValues = old($name, $value);
    $selectedValues = is_array($selectedValues) ? array_map('strval', $selectedValues) : [(string) $selectedValues];
@endphp

<div @class([$containerClass])>
    @if ($label)
        <label for="{{ $id }}" @class(['form-label', 'visually-hidden' => $labelHidden])>
            {{ $label }}
            @if ($required)
                <span class="text-danger" aria-hidden="true">*</span>
            @endif
        </label>
    @endif

    <select
        name="{{ $name }}{{ $multiple ? '[]' : '' }}"
        id="{{ $id }}"
        @class(['form-select', 'js-select2', 'is-invalid' => $errors->has($name), $selectClass])
        data-select2="true"
        data-placeholder="{{ $placeholder }}"
        data-allow-clear="{{ $allowClear ? 'true' : 'false' }}"
        data-reset-value="{{ $resetValue ?? '' }}"
        data-minimum-results-for-search="{{ $minimumResultsForSearch }}"
        @required($required)
        @disabled($disabled)
        @multiple($multiple)
        {{ $attributes->except(['id'])->merge() }}
    >
        @if (! $multiple && $showPlaceholderOption)
            <option value=""></option>
        @endif
        @foreach ($options as $optionValue => $optionLabel)
            <option value="{{ $optionValue }}" @selected(in_array((string) $optionValue, $selectedValues, true))>
                {{ $optionLabel }}
            </option>
        @endforeach
    </select>

    @if ($helpText)
        <div class="form-text">{{ $helpText }}</div>
    @endif

    @error($name)
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
</div>
