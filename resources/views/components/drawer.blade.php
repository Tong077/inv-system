@props([
    'id',
    'placement' => 'end',
    'title' => null,
    'width' => '26rem',
    'height' => '50vh',
    'backdrop' => true,
    'scroll' => false,
    'keyboard' => true,
])

@php
    $placement = in_array($placement, ['start', 'end', 'top', 'bottom'], true) ? $placement : 'end';
    $labelId = $id . '-label';
    $backdropValue = is_bool($backdrop)
        ? ($backdrop ? 'true' : 'false')
        : (in_array($backdrop, ['true', 'false', 'static'], true) ? $backdrop : 'true');
    $sizeStyle = in_array($placement, ['start', 'end'], true)
        ? '--bs-offcanvas-width: ' . $width . ';'
        : '--bs-offcanvas-height: ' . $height . ';';
@endphp

<div {{ $attributes->class(['offcanvas', 'offcanvas-' . $placement, 'app-drawer'])->merge([
    'id' => $id,
    'tabindex' => '-1',
    'aria-labelledby' => $labelId,
    'data-bs-scroll' => $scroll ? 'true' : 'false',
    'data-bs-backdrop' => $backdropValue,
    'data-bs-keyboard' => $keyboard ? 'true' : 'false',
    'style' => $sizeStyle,
]) }}>
    <div class="offcanvas-header app-drawer-header border">
        @isset($header)
            {{ $header }}
        @else
            <h2 class="offcanvas-title fs-5" id="{{ $labelId }}">{{ $title }}</h2>
        @endisset
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>

    <div class="offcanvas-body">
        {{ $slot }}
    </div>

    @isset($footer)
        <div class="app-drawer-footer mb-2">
            {{ $footer }}
        </div>
    @endisset
</div>
