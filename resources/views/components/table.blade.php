@props([
    'rows' => null,
    'emptyTitle' => 'Nothing to show',
    'emptyDescription' => 'There are no records to display.',
    'caption' => null,
    'tableClass' => '',
    'pagination' => true,
])

@php
    $isEmpty = $rows !== null && is_countable($rows) && count($rows) === 0;
@endphp

<section {{ $attributes->class(['card', 'data-table-card']) }}>
    @isset($toolbar)
        <div class="card-header data-table-toolbar">
            {{ $toolbar }}
        </div>
    @endisset

    @if ($isEmpty)
        <div class="card-body">
            @isset($empty)
                {{ $empty }}
            @else
                <div class="data-table-empty text-center">
                    <h2 class="h5">{{ $emptyTitle }}</h2>
                    <p class="text-secondary mb-0">{{ $emptyDescription }}</p>
                </div>
            @endisset
        </div>
    @else
        <div class="card-body p-0">
            <div class="table-responsive data-table-responsive">
                <table class="table data-table {{ $tableClass }}">
                    @if ($caption)
                        <caption class="visually-hidden">{{ $caption }}</caption>
                    @endif

                    @isset($head)
                        <thead>{{ $head }}</thead>
                    @endisset

                    @isset($body)
                        <tbody>{{ $body }}</tbody>
                    @endisset
                </table>
            </div>
        </div>
    @endif

    @isset($footer)
        <div class="card-footer data-table-footer">
            {{ $footer }}
        </div>
    @elseif ($pagination && $rows !== null && method_exists($rows, 'hasPages') && $rows->hasPages())
        <div class="card-footer data-table-footer">
            {{ $rows->withQueryString()->links('pagination::bootstrap-5') }}
        </div>
    @endisset
</section>
