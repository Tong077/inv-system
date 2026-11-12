@extends('layouts.app')

@section('title', 'Inventory overview | Stockroom')
@section('breadcrumb', 'Overview')
@section('eyebrow', 'WORKSPACE OVERVIEW')
@section('heading', 'Inventory overview')
@section('description', 'A live view of stock levels, purchasing activity, and recent inventory movements.')

@section('content')
    <div class="row g-3">
        <div class="col-12 col-sm-6 col-xl-4">
            <div class="info-box">
                <span class="info-box-icon text-bg-success shadow-sm" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none">
                        <path d="M4 7.5 12 3l8 4.5v9L12 21l-8-4.5v-9Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
                        <path d="m4.5 7.7 7.5 4.4 7.5-4.4M12 12.1V21" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
                    </svg>
                </span>
                <div class="info-box-content">
                    <span class="info-box-text">Inventory items</span>
                    <span class="info-box-number">{{ number_format($ingredientCount) }}</span>
                    <span class="text-secondary small">In your inventory catalogue</span>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-4">
            <div class="info-box"><span class="info-box-icon text-bg-danger shadow-sm" aria-hidden="true"><i class="bi bi-exclamation-triangle"></i></span><div class="info-box-content"><span class="info-box-text">Low stock items</span><span class="info-box-number">{{ number_format($lowStockCount) }}</span><span class="text-secondary small">Below minimum across all locations</span></div></div>
        </div>
        <div class="col-12 col-sm-6 col-xl-4">
            <div class="info-box"><span class="info-box-icon text-bg-info shadow-sm" aria-hidden="true"><i class="bi bi-clipboard-check"></i></span><div class="info-box-content"><span class="info-box-text">Open purchase orders</span><span class="info-box-number">{{ number_format($openOrderCount) }}</span><span class="text-secondary small">Ordered or partially received</span></div></div>
        </div>
        <div class="col-12 col-sm-6 col-xl-4">
            <div class="info-box"><span class="info-box-icon text-bg-secondary shadow-sm" aria-hidden="true"><i class="bi bi-box-arrow-in-down"></i></span><div class="info-box-content"><span class="info-box-text">Draft receipts</span><span class="info-box-number">{{ number_format($draftReceiptCount) }}</span><span class="text-secondary small">Awaiting review and posting</span></div></div>
        </div>

        <div class="col-12 col-sm-6 col-xl-4">
            <div class="info-box">
                <span class="info-box-icon text-bg-primary shadow-sm" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none">
                        <path d="M3.5 10 12 4l8.5 6v9.5h-17V10Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
                        <path d="M9 19.5v-6h6v6" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
                    </svg>
                </span>
                <div class="info-box-content">
                    <span class="info-box-text">Stock locations</span>
                    <span class="info-box-number">{{ number_format($locationCount) }}</span>
                    <span class="text-secondary small">Active storage locations</span>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-4">
            <div class="info-box">
                <span class="info-box-icon text-bg-warning shadow-sm" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none">
                        <path d="M5 7h14M9 7V4h6v3m3 0-.8 13H6.8L6 7m4 3v6m4-6v6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
                <div class="info-box-content">
                    <span class="info-box-text">Waste records</span>
                    <span class="info-box-number">{{ number_format($postedWasteCount) }}</span>
                    <span class="text-secondary small">Posted waste records</span>
                </div>
            </div>
        </div>
    </div>

    <section class="card mt-3" aria-labelledby="movement-trend-heading">
        <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h2 class="card-title h5 mb-1" id="movement-trend-heading">Stock movement trend</h2>
                <p class="text-secondary small mb-0">Daily stock in and out over the last 14 days, measured in the inventory item base unit.</p>
            </div>
            @if ($trackedIngredients->isNotEmpty())
                <form method="GET" action="{{ route('dashboard') }}" class="d-flex flex-wrap align-items-end gap-2">
                    <div>
                        <label for="tracked-ingredient" class="form-label visually-hidden">Inventory item to track</label>
                        <select id="tracked-ingredient" name="ingredient_id" class="form-select form-select-sm">
                            @foreach ($trackedIngredients as $ingredient)
                                <option value="{{ $ingredient->id }}" @selected((int)$trackedIngredientId === (int)$ingredient->id)>{{ $ingredient->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-sm btn-light border">Update graph</button>
                </form>
            @endif
        </div>
        @if ($trackedIngredientId && $movementChart['hasData'])
            <div class="overflow-auto px-3 pt-2">
                <svg viewBox="0 0 820 270" class="w-100" style="height: 280px; min-width: 640px" role="img" aria-labelledby="movement-chart-title movement-chart-description">
                    <title id="movement-chart-title">Stock in and out for the selected inventory item</title>
                    <desc id="movement-chart-description">Daily quantities in {{ $movementChart['unit'] }} for the last 14 days.</desc>
                    @foreach ($movementChart['ticks'] as $tick)
                        <line x1="60" y1="{{ $tick['y'] }}" x2="760" y2="{{ $tick['y'] }}" stroke="currentColor" opacity=".12" />
                        <text x="48" y="{{ $tick['y'] + 4 }}" text-anchor="end" class="small" fill="currentColor" opacity=".65">{{ $tick['label'] }}</text>
                    @endforeach
                    <polyline points="{{ $movementChart['inPoints'] }}" fill="none" stroke="var(--bs-success)" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />
                    <polyline points="{{ $movementChart['outPoints'] }}" fill="none" stroke="var(--bs-danger)" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />
                    @foreach ($movementChart['days'] as $index => $day)
                        <circle cx="{{ $day['x'] }}" cy="{{ $day['inY'] }}" r="4" fill="var(--bs-success)"><title>{{ $day['label'] }}: in {{ number_format($day['in'], 3) }} {{ $movementChart['unit'] }}</title></circle>
                        <circle cx="{{ $day['x'] }}" cy="{{ $day['outY'] }}" r="4" fill="var(--bs-danger)"><title>{{ $day['label'] }}: out {{ number_format($day['out'], 3) }} {{ $movementChart['unit'] }}</title></circle>
                        @if ($index % 2 === 0 || $index === 13)
                            <text x="{{ $day['x'] }}" y="238" text-anchor="middle" class="small" fill="currentColor" opacity=".7">{{ $day['label'] }}</text>
                        @endif
                    @endforeach
                </svg>
            </div>
            <div class="card-body pt-0">
                <div class="d-flex gap-4 small">
                    <span><i class="bi bi-circle-fill text-success me-1" aria-hidden="true"></i>Stock in</span>
                    <span><i class="bi bi-circle-fill text-danger me-1" aria-hidden="true"></i>Stock out</span>
                </div>
            </div>
        @else
            <div class="card-body text-secondary">
                @if ($trackedIngredientId)
                    No stock movements for this item in the last 14 days.
                @else
                    Post stock receipts or operations to start tracking movement trends.
                @endif
            </div>
        @endif
    </section>

    <section class="card mt-3" aria-labelledby="recent-movements-heading">
        <div class="card-header d-flex align-items-center">
            <h2 class="card-title h5 mb-0" id="recent-movements-heading">Recent stock movements</h2>
            <a class="btn btn-sm btn-light border ms-auto flex-shrink-0" href="{{ route('stock-movements.index') }}">View ledger</a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead><tr><th scope="col">Date</th><th scope="col">Inventory item</th><th scope="col">Location</th><th scope="col">Type</th><th scope="col" class="text-end">In</th><th scope="col" class="text-end">Out</th><th scope="col" class="text-end">Balance</th></tr></thead>
                <tbody>
                    @forelse($recentMovements as $movement)
                        <tr><td>{{ $movement->movement_date?->format('M j, Y H:i') }}</td><td>{{ $movement->ingredient?->name }}</td><td>{{ $movement->stockLocation?->name }}</td><td class="text-capitalize">{{ str_replace('_', ' ', $movement->movement_type) }}</td><td class="text-end">{{ number_format((float)$movement->quantity_in, 3) }}</td><td class="text-end">{{ number_format((float)$movement->quantity_out, 3) }}</td><td class="text-end">{{ number_format((float)$movement->balance_after, 3) }} {{ $movement->ingredient?->baseUnit?->abbreviation }}</td></tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-secondary py-4">No stock movements have been posted.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="card mt-3" aria-labelledby="reorder-suggestions-heading">
        <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                <h2 class="card-title h5 mb-1" id="reorder-suggestions-heading">Reorder suggestions</h2>
                <p class="text-secondary small mb-0">Stock due on open orders is subtracted first. Supplier quotes are compared by cost per inventory base unit. Drafts remain for review.</p>
            </div>
            @if (collect($replenishmentSuggestions)->contains(fn ($suggestion) => $suggestion['orderable']))
                <form method="POST" action="{{ route('replenishment.drafts.store') }}" data-swal-confirm data-swal-title="Prepare suggested purchase orders?" data-swal-text="This creates draft orders and adds missing suggested items to existing auto-restock drafts for review. It will not place orders with suppliers." data-swal-confirm-text="Prepare drafts" data-swal-cancel-text="Cancel">
                    @csrf
                    <button class="btn btn-sm btn-success" type="submit"><i class="bi bi-magic me-1" aria-hidden="true"></i>Prepare draft orders</button>
                </form>
            @endif
        </div>
        @if (count($replenishmentSuggestions))
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead><tr><th scope="col">Inventory item</th><th scope="col">Stock position</th><th scope="col" class="text-end">Need after incoming</th><th scope="col">Suggested order</th><th scope="col">Supplier price comparison</th><th scope="col" class="text-end">Est. cost</th><th scope="col">Why this suggestion</th></tr></thead>
                    <tbody>
                        @foreach ($replenishmentSuggestions as $suggestion)
                            <tr>
                                <td><span class="fw-semibold">{{ $suggestion['ingredient']->name }}</span><span class="d-block small text-secondary">{{ $suggestion['ingredient']->code }}</span></td>
                                <td>
                                    {{ number_format($suggestion['onHand'], 3) }} / {{ number_format($suggestion['minimum'], 3) }} {{ $suggestion['ingredient']->baseUnit?->abbreviation }}
                                    <span class="d-block small text-secondary">On hand / minimum</span>
                                    @if ($suggestion['incoming'] > 0)
                                        <span class="d-block small text-info">{{ number_format($suggestion['incoming'], 3) }} {{ $suggestion['ingredient']->baseUnit?->abbreviation }} incoming</span>
                                    @endif
                                </td>
                                <td class="text-end">{{ number_format($suggestion['uncoveredShortage'], 3) }} {{ $suggestion['ingredient']->baseUnit?->abbreviation }}</td>
                                <td>
                                    @if ($suggestion['uncoveredShortage'] <= 0)
                                        <span class="badge text-bg-info">Covered by incoming stock</span>
                                    @elseif ($suggestion['orderable'])
                                        <span class="fw-semibold">{{ number_format($suggestion['packages'] * $suggestion['packageQuantity'], 3) }} {{ $suggestion['packageUnit']->abbreviation }}</span>
                                        <span class="d-block small text-secondary">{{ $suggestion['packages'] }} package(s)</span>
                                    @else
                                        <a class="btn btn-sm btn-outline-primary text-nowrap" href="{{ route('ingredient-price-histories.index', ['ingredient_id' => $suggestion['ingredient']->id]) }}">
                                            <i class="bi bi-plus-circle me-1" aria-hidden="true"></i>Add supplier price
                                        </a>
                                    @endif
                                </td>
                                <td>
                                    @if ($suggestion['supplier'])
                                        <span class="fw-semibold">{{ $suggestion['supplier']->name }}</span>
                                        <span class="d-block small">{{ number_format($suggestion['supplierPrices'][0]['unitCost'], 6) }} / {{ $suggestion['ingredient']->baseUnit?->abbreviation }}</span>
                                        <span class="d-block small text-secondary">Latest recorded quote {{ $suggestion['priceDate']?->format('M j, Y') }}</span>
                                        @if (count($suggestion['supplierPrices']) > 1)
                                            <details class="small mt-1">
                                                <summary>Compare {{ count($suggestion['supplierPrices']) }} suppliers</summary>
                                                <ul class="list-unstyled mb-0 mt-1">
                                                    @foreach ($suggestion['supplierPrices'] as $priceOption)
                                                        <li class="mb-1">
                                                            {{ $priceOption['supplier']->name }}:
                                                            {{ number_format($priceOption['unitCost'], 6) }} / {{ $suggestion['ingredient']->baseUnit?->abbreviation }}
                                                            <span class="text-secondary">({{ number_format($priceOption['packagePrice'], 2) }} per {{ number_format($priceOption['packageQuantity'], 3) }} {{ $priceOption['packageUnit']->abbreviation }}, {{ $priceOption['priceDate']->format('M j, Y') }})</span>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </details>
                                        @endif
                                    @else
                                        <span class="text-secondary">No compatible active quote</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    {{ $suggestion['orderable'] ? number_format($suggestion['estimatedCost'], 2) : '-' }}
                                </td>
                                <td class="small">{{ $suggestion['reason'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="card-body text-secondary">
                @if ($lowStockCount > 0)
                    Low stock items are covered by open purchase orders or need a compatible active supplier price before a draft can be prepared.
                @else
                    Nothing is below minimum stock right now.
                @endif
            </div>
        @endif
    </section>

    <div class="row g-3 mt-1">
        <div class="col-12 col-xl-7">
            <section class="card welcome-card h-100" aria-labelledby="welcome-heading">
                <div class="card-body d-flex align-items-center justify-content-between gap-4">
                    <div class="welcome-copy">
                        <span class="welcome-kicker">
                            <span class="welcome-kicker-dot" aria-hidden="true"></span>
                            READY WHEN YOU ARE
                        </span>
                        <h2 id="welcome-heading">Build a reliable inventory foundation.</h2>
                        <p>
                            Start with inventory items and base units. Then receiving, recipe costing,
                            and sales consumption can use consistent stock data.
                        </p>
                    </div>
                    <div class="welcome-art d-none d-sm-block" aria-hidden="true">
                        <div class="art-card art-card-back"></div>
                        <div class="art-card art-card-front">
                            <span class="art-box art-box-one"></span>
                            <span class="art-box art-box-two"></span>
                            <span class="art-box art-box-three"></span>
                        </div>
                        <span class="art-spark art-spark-one"></span>
                        <span class="art-spark art-spark-two"></span>
                    </div>
                </div>
            </section>
        </div>

        <div class="col-12 col-xl-5">
            <section class="card h-100" aria-labelledby="getting-started-heading">
                <div class="card-header">
                    <h3 class="card-title" id="getting-started-heading">Getting started</h3>
                    <div class="card-tools">
                        <span class="badge text-bg-light">Phase 1</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item d-flex gap-3 py-3">
                            <span class="step-number">01</span>
                            <div>
                                <h4 class="step-title">Set up stock locations</h4>
                                <p class="step-description">Active locations available: {{ number_format($locationCount) }}.</p>
                            </div>
                        </div>
                        <div class="list-group-item d-flex gap-3 py-3">
                            <span class="step-number">02</span>
                            <div>
                                <h4 class="step-title">Build your inventory catalogue</h4>
                                <p class="step-description">{{ number_format($ingredientCount) }} inventory items currently tracked.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
@endsection
