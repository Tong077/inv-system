@props([
    'id',
    'size' => '',
    'centered' => false,
    'scrollable' => false,
    'dialogClass' => '',
])

@php
    $sizeClass = in_array($size, ['sm', 'lg', 'xl', 'fullscreen'], true) ? 'modal-' . $size : '';
    $labelId = $id . '-label';
@endphp

<div {{ $attributes->class(['modal', 'fade'])->merge(['id' => $id, 'tabindex' => '-1', 'aria-labelledby' => $labelId, 'aria-hidden' => 'true']) }}>
    <div class="modal-dialog {{ $sizeClass }} {{ $centered ? 'modal-dialog-centered' : '' }} {{ $scrollable ? 'modal-dialog-scrollable' : '' }} {{ $dialogClass }}">
        {{ $slot }}
    </div>
</div>
