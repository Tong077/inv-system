@props([
    'action' => request()->url(),
    'searchName' => 'search',
    'searchLabel' => 'Search records',
    'searchPlaceholder' => 'Search records',
    'searchValue' => null,
    'searchContainerClass' => 'flex-grow-1',
    'searchClass' => '',
    'filters' => [],
    'submitLabel' => 'Search',
    'clearLabel' => 'Clear',
    'preserve' => [],
    'showSearch' => true,
])

@php
    $searchValue ??= request($searchName, '');
    $hasFilters = filled($searchValue);

    foreach ($filters as $filter) {
        $hasFilters = $hasFilters || filled(request($filter['name']));
    }
@endphp

<form action="{{ $action }}" method="GET" {{ $attributes->merge(['class' => 'd-flex flex-wrap gap-2 w-100 align-items-center']) }}>
    @foreach ($preserve as $name)
        @if (request()->filled($name))
            <input type="hidden" name="{{ $name }}" value="{{ request($name) }}">
        @endif
    @endforeach

    @if ($showSearch)
        <div @class([$searchContainerClass])>
            <label for="filter-{{ \Illuminate\Support\Str::slug($searchName) }}" class="visually-hidden">{{ $searchLabel }}</label>
            <input
                id="filter-{{ \Illuminate\Support\Str::slug($searchName) }}"
                type="search"
                name="{{ $searchName }}"
                value="{{ $searchValue }}"
                @class(['form-control', $searchClass])
                placeholder="{{ $searchPlaceholder }}"
            >
        </div>
    @endif

    @foreach ($filters as $filter)
        @php
            $name = $filter['name'];
            $id = 'filter-'.\Illuminate\Support\Str::slug($name);
            $value = request($name, $filter['value'] ?? '');
            $hasFilters = $hasFilters || filled($value);
        @endphp

        @if (($filter['type'] ?? 'select') === 'select')
            <x-select2
                :name="$name"
                :id="$id"
                :options="$filter['options'] ?? []"
                :value="$value"
                :label="$filter['label']"
                label-hidden
                :placeholder="$filter['placeholder'] ?? 'All '.$filter['label']"
                :container-class="$filter['class'] ?? 'workflow-filter'"
                select-class="workflow-filter"
                :allow-clear="false"
                :show-placeholder-option="$filter['showPlaceholderOption'] ?? true"
                :reset-value="$filter['resetValue'] ?? null"
            />
        @else
            <div class="{{ $filter['class'] ?? 'workflow-filter' }}">
                <label for="{{ $id }}" class="visually-hidden">{{ $filter['label'] }}</label>
                <input
                    id="{{ $id }}"
                    type="{{ $filter['type'] }}"
                    name="{{ $name }}"
                    value="{{ $value }}"
                    class="form-control"
                    placeholder="{{ $filter['placeholder'] ?? $filter['label'] }}"
                    @if (isset($filter['min'])) min="{{ $filter['min'] }}" @endif
                    @if (isset($filter['max'])) max="{{ $filter['max'] }}" @endif
                    @if (isset($filter['step'])) step="{{ $filter['step'] }}" @endif
                >
            </div>
        @endif
    @endforeach

    {{ $slot }}

    <button class="btn btn-light border" type="submit">{{ $submitLabel }}</button>
    @if ($hasFilters)
        <a href="{{ $action }}" class="btn btn-light border search-filter-clear">{{ $clearLabel }}</a>
    @endif
</form>
