@extends('layouts.app')

@section('title', 'Point of sale | '.config('app.name', 'Stockroom'))
@section('breadcrumb', 'Point of sale')
@section('eyebrow', 'SALES')
@section('heading', 'Point of sale')
@section('description', 'Browse menu items, build an order, and complete payment. Stock is checked before checkout.')

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger" role="alert">
            <div class="fw-semibold">Sale not completed</div>
            <ul class="mb-0 mt-1">@foreach ($errors->all() as $message)<li>{{ $message }}</li>@endforeach</ul>
        </div>
    @endif

    @if ($menuItems->isEmpty())
        <div class="card"><div class="card-body text-secondary">Add an active menu item with either a current recipe or a direct inventory item before using the POS.</div></div>
    @elseif ($locations->isEmpty())
        <div class="card"><div class="card-body text-secondary">Add an active stock location before completing sales.</div></div>
    @else
        @php($posCategories = $menuItems->pluck('category')->filter()->unique('id')->sortBy('name'))
        <form method="POST" action="{{ route('pos.checkout') }}" id="pos-checkout-form">
            @csrf
            <div class="row g-3 align-items-start">
                <div class="col-12 col-xl-8">
                    <section class="card pos-menu-card" aria-labelledby="pos-menu-heading">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div>
                                <h2 class="card-title h5 mb-1" id="pos-menu-heading">Choose items</h2>
                                <span class="small text-secondary">{{ $menuItems->count() }} menu items available</span>
                            </div>
                            <span class="pos-menu-heading-icon" aria-hidden="true"><i class="bi bi-grid"></i></span>
                        </div>
                        <div class="card-body">
                            <div class="row g-2 mb-3">
                                <div class="col-12 col-md">
                                    <label for="pos-search" class="visually-hidden">Search menu</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white"><i class="bi bi-search" aria-hidden="true"></i></span>
                                        <input id="pos-search" type="search" class="form-control border-start-0 ps-0" placeholder="Search products..." autocomplete="off">
                                    </div>
                                </div>
                                <div class="col-12 col-md-auto pos-barcode-field">
                                    <label for="pos-barcode" class="visually-hidden">Scan item barcode</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white"><i class="bi bi-upc-scan" aria-hidden="true"></i></span>
                                        <input id="pos-barcode" type="text" class="form-control border-start-0 ps-0" placeholder="Scan barcode" autocomplete="off" autofocus aria-describedby="pos-barcode-feedback">
                                    </div>
                                    <span id="pos-barcode-feedback" class="small text-danger" role="status" hidden>Barcode not found.</span>
                                </div>
                            </div>

                            <div class="pos-category-filters mb-3" role="group" aria-label="Filter menu by category">
                                <button type="button" class="pos-category-filter is-active" data-pos-category="all" aria-pressed="true">All items</button>
                                @foreach ($posCategories as $category)
                                    <button type="button" class="pos-category-filter" data-pos-category="{{ strtolower($category->name) }}" aria-pressed="false">{{ $category->name }}</button>
                                @endforeach
                            </div>

                            <div class="row g-3" id="pos-product-grid">
                                @foreach ($menuItems as $menuItem)
                                    <div class="col-6 col-md-4 col-xxl-3 pos-product" data-menu-item-id="{{ $menuItem->id }}" data-category="{{ strtolower($menuItem->category?->name ?? '') }}" data-barcode="{{ $menuItem->barcode }}" data-available="{{ json_encode($menuItem->available_quantities) }}" data-available-label="{{ $menuItem->isDirectStockSale() ? ($menuItem->stockIngredient?->baseUnit?->abbreviation ?? 'units') : 'portions' }}" data-search="{{ strtolower($menuItem->name.' '.$menuItem->code.' '.$menuItem->barcode.' '.$menuItem->category?->name) }}">
                                        <article class="card h-100 pos-product-card">
                                            <div class="card-body d-flex flex-column">
                                                @if ($menuItem->image_url)
                                                    <button type="button" class="pos-image-preview-button mb-2" data-bs-toggle="modal" data-bs-target="#posImagePreviewModal" data-image-src="{{ $menuItem->image_url }}" data-image-alt="{{ $menuItem->name }}" aria-label="View {{ $menuItem->name }} photo">
                                                        <img src="{{ $menuItem->image_url }}" alt="Preview {{ $menuItem->name }} photo" class="pos-product-image" loading="lazy">
                                                    </button>
                                                @else
                                                    <div class="pos-product-image pos-product-image-empty mb-2" aria-hidden="true"><i class="bi bi-image"></i></div>
                                                @endif
                                                <span class="small text-secondary pos-product-code">{{ $menuItem->code }}</span>
                                                <h3 class="h6 mt-1 mb-1">{{ $menuItem->name }}</h3>
                                                @if ($menuItem->category)
                                                    <span class="pos-product-category mb-2">{{ $menuItem->category->name }}</span>
                                                @else
                                                    <span class="pos-product-category mb-2">Uncategorized</span>
                                                @endif
                                                @if ($menuItem->isDirectStockSale())
                                                    <span class="badge text-bg-info align-self-start mb-2">Direct stock</span>
                                                @endif
                                                <span class="small text-secondary mb-2 pos-availability" aria-live="polite"></span>
                                                <div class="pos-product-price mt-auto mb-2">{{ $menuItem->currency }} {{ number_format((float) $menuItem->selling_price, 2) }}</div>
                                                <div class="input-group input-group-sm pos-product-quantity">
                                                    <button type="button" class="btn btn-light border pos-quantity-step" data-step="-1" aria-label="Remove one {{ $menuItem->name }}">−</button>
                                                    <input class="form-control text-center pos-quantity" type="number" min="0" max="999999.999" step="0.001" name="items[{{ $menuItem->id }}]" value="{{ old('items.'.$menuItem->id, 0) }}" inputmode="decimal" data-price="{{ $menuItem->selling_price }}" data-currency="{{ $menuItem->currency }}" aria-label="Quantity of {{ $menuItem->name }}">
                                                    <button type="button" class="btn btn-success pos-quantity-step" data-step="1" aria-label="Add one {{ $menuItem->name }}"><i class="bi bi-plus-lg" aria-hidden="true"></i></button>
                                                </div>
                                            </div>
                                        </article>
                                    </div>
                                @endforeach
                            </div>
                            <div class="pos-empty-search mt-3" id="pos-no-results" hidden>
                                <i class="bi bi-search" aria-hidden="true"></i>
                                <p class="mb-0">No menu items match your search.</p>
                            </div>
                        </div>
                    </section>
                </div>

                <div class="col-12 col-xl-4">
                    <section class="card pos-cart-card" aria-labelledby="pos-cart-heading">
                        <div class="card-header pos-cart-header">
                            <div>
                                <h2 class="card-title h5 mb-1" id="pos-cart-heading">Current order</h2>
                                <span class="small text-secondary">Review items before checkout</span>
                            </div>
                            <span class="pos-cart-count" id="pos-item-count">0 items</span>
                        </div>
                        <div class="card-body pos-cart-body">
                            <div class="row g-2 mb-3">
                                <div class="col-12">
                                    <label for="pos-stock-location" class="form-label">Stock location <span class="text-danger" aria-hidden="true">*</span></label>
                                    <select class="form-select" name="stock_location_id" id="pos-stock-location" required>
                                        @foreach ($locations as $location)
                                            <option value="{{ $location->id }}" @selected((string) old('stock_location_id') === (string) $location->id)>{{ $location->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label for="pos-payment-method" class="form-label">Payment method</label>
                                    <select class="form-select" name="payment_method" id="pos-payment-method" required>
                                        <option value="cash" @selected(old('payment_method', 'cash') === 'cash')>Cash</option>
                                        <option value="card" @selected(old('payment_method') === 'card')>Card</option>
                                        <option value="qr" @selected(old('payment_method') === 'qr')>QR payment</option>
                                        <option value="other" @selected(old('payment_method') === 'other')>Other</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-text pos-payment-note mb-3">
                                <i class="bi bi-info-circle me-1" aria-hidden="true"></i>Card and QR payments are recorded here; process payment at your terminal.
                            </div>

                            <div class="pos-cart-items" id="pos-cart-lines" aria-live="polite" aria-relevant="additions removals text">
                                <div class="pos-cart-empty">
                                    <i class="bi bi-basket2" aria-hidden="true"></i>
                                    <span>Your order is empty</span>
                                    <small>Add a menu item to get started.</small>
                                </div>
                            </div>

                            <div id="pos-stock-warning" class="pos-stock-warning mt-3" role="status" hidden>
                                <i class="bi bi-exclamation-triangle me-1" aria-hidden="true"></i>One or more quantities exceed the selected location’s available stock.
                            </div>

                            <div class="pos-cart-totals mt-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span>Subtotal</span>
                                    <output id="pos-subtotal">0.00</output>
                                </div>
                                <div class="d-flex justify-content-between align-items-center pos-total-row">
                                    <span>Total</span>
                                    <output id="pos-total">0.00</output>
                                </div>
                            </div>

                            <div id="pos-cash-fields" class="mb-3">
                                <label for="pos-amount-tendered" class="form-label">Cash received</label>
                                <input class="form-control" type="number" id="pos-amount-tendered" name="amount_tendered" min="0" step="0.01" value="{{ old('amount_tendered') }}" inputmode="decimal" placeholder="Enter amount received">
                                <div class="form-text" id="pos-change-due" aria-live="polite">Change due: 0.00</div>
                            </div>
                        </div>
                        <div class="card-footer pos-cart-footer">
                            <button class="btn btn-success w-100 pos-checkout-button" type="submit" id="pos-submit" disabled>
                                <i class="bi bi-credit-card me-2" aria-hidden="true"></i>Complete sale
                            </button>
                            <div class="form-text text-center mt-2">Stock will be deducted after checkout.</div>
                        </div>
                    </section>
                </div>
            </div>
        </form>

        <div class="modal fade" id="posImagePreviewModal" tabindex="-1" aria-labelledby="posImagePreviewTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header"><h2 class="modal-title fs-5" id="posImagePreviewTitle">Menu photo</h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
                    <div class="modal-body text-center"><img id="posImagePreview" src="" alt="" class="img-fluid rounded pos-image-preview-large"></div>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const form = document.getElementById('pos-checkout-form');
                const inputs = [...form.querySelectorAll('.pos-quantity')];
                const total = document.getElementById('pos-total');
                const subtotal = document.getElementById('pos-subtotal');
                const cart = document.getElementById('pos-cart-lines');
                const itemCount = document.getElementById('pos-item-count');
                const submit = document.getElementById('pos-submit');
                const paymentMethod = document.getElementById('pos-payment-method');
                const amountTendered = document.getElementById('pos-amount-tendered');
                const cashFields = document.getElementById('pos-cash-fields');
                const changeDue = document.getElementById('pos-change-due');
                const search = document.getElementById('pos-search');
                const barcodeInput = document.getElementById('pos-barcode');
                const location = document.getElementById('pos-stock-location');
                const currencyFormat = new Intl.NumberFormat(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                let activeCategory = 'all';

                const updateCart = () => {
                    inputs.forEach((input) => {
                        const product = input.closest('.pos-product');
                        const available = Number(JSON.parse(product.dataset.available || '{}')[location.value] || 0);
                        input.max = available.toFixed(3);
                        const availability = product.querySelector('.pos-availability');
                        const unitLabel = product.dataset.availableLabel || 'portions';
                        availability.textContent = available > 0
                            ? `${available.toLocaleString()} ${unitLabel} available`
                            : 'Out of stock';
                        availability.classList.toggle('text-danger', available <= 0);
                        availability.classList.toggle('text-secondary', available > 0);
                    });

                    const selected = inputs.filter((input) => Number(input.value) > 0);
                    const exceedsStock = selected.some((input) => {
                        const available = Number(JSON.parse(input.closest('.pos-product').dataset.available || '{}')[location.value] || 0);
                        return Number(input.value) > available + 0.0005;
                    });
                    const currencies = [...new Set(selected.map((input) => input.dataset.currency))];
                    let amount = 0;
                    const lines = selected.map((input) => {
                        const product = input.closest('.pos-product');
                        const name = product.querySelector('h3').textContent.trim();
                        const quantity = Number(input.value);
                        const unitPrice = Number(input.dataset.price);
                        const lineTotal = quantity * unitPrice;
                        amount += lineTotal;

                        const line = document.createElement('article');
                        const heading = document.createElement('div');
                        const itemName = document.createElement('strong');
                        const remove = document.createElement('button');
                        const removeIcon = document.createElement('i');
                        const price = document.createElement('div');
                        const controls = document.createElement('div');
                        const decrease = document.createElement('button');
                        const quantityLabel = document.createElement('span');
                        const increase = document.createElement('button');
                        line.className = 'pos-cart-line';
                        heading.className = 'pos-cart-line-heading';
                        itemName.className = 'pos-cart-item-name';
                        itemName.textContent = name;
                        remove.type = 'button';
                        remove.className = 'pos-cart-remove';
                        remove.dataset.posCartRemove = '';
                        remove.dataset.menuItemId = product.dataset.menuItemId;
                        remove.setAttribute('aria-label', `Remove ${name} from order`);
                        removeIcon.className = 'bi bi-trash3';
                        removeIcon.setAttribute('aria-hidden', 'true');
                        remove.append(removeIcon);
                        heading.append(itemName, remove);

                        price.className = 'pos-cart-line-price';
                        price.textContent = `${input.dataset.currency} ${currencyFormat.format(unitPrice)} × ${quantity.toLocaleString()} = ${input.dataset.currency} ${currencyFormat.format(lineTotal)}`;

                        controls.className = 'pos-cart-line-controls';
                        decrease.type = increase.type = 'button';
                        decrease.className = increase.className = 'pos-cart-step';
                        decrease.dataset.posCartStep = '-1';
                        increase.dataset.posCartStep = '1';
                        decrease.dataset.menuItemId = increase.dataset.menuItemId = product.dataset.menuItemId;
                        decrease.setAttribute('aria-label', `Remove one ${name}`);
                        increase.setAttribute('aria-label', `Add one ${name}`);
                        decrease.textContent = '−';
                        increase.textContent = '+';
                        quantityLabel.className = 'pos-cart-quantity';
                        quantityLabel.textContent = quantity.toLocaleString();
                        controls.append(decrease, quantityLabel, increase);
                        line.append(heading, price, controls);
                        return line;
                    });

                    if (lines.length) {
                        cart.replaceChildren(...lines);
                    } else {
                        const empty = document.createElement('div');
                        const icon = document.createElement('i');
                        const title = document.createElement('span');
                        const hint = document.createElement('small');
                        empty.className = 'pos-cart-empty';
                        icon.className = 'bi bi-basket2';
                        icon.setAttribute('aria-hidden', 'true');
                        title.textContent = 'Your order is empty';
                        hint.textContent = 'Add a menu item to get started.';
                        empty.append(icon, title, hint);
                        cart.replaceChildren(empty);
                    }

                    const displayAmount = currencies.length === 1
                        ? `${currencies[0]} ${currencyFormat.format(amount)}`
                        : currencyFormat.format(amount);
                    subtotal.textContent = displayAmount;
                    total.textContent = displayAmount;
                    const quantityCount = selected.reduce((sum, input) => sum + Number(input.value), 0);
                    itemCount.textContent = `${quantityCount.toLocaleString()} ${quantityCount === 1 ? 'item' : 'items'}`;
                    const totalAmount = Math.round((amount + Number.EPSILON) * 100) / 100;
                    const cashReceived = Number(amountTendered.value || 0);
                    const isCash = paymentMethod.value === 'cash';
                    cashFields.hidden = !isCash;
                    amountTendered.required = isCash;
                    changeDue.textContent = `Change due: ${currencyFormat.format(Math.max(0, cashReceived - totalAmount))}`;
                    document.getElementById('pos-stock-warning').hidden = !exceedsStock;
                    submit.disabled = selected.length === 0 || currencies.length > 1 || (isCash && cashReceived < totalAmount) || exceedsStock;
                    submit.title = currencies.length > 1 ? 'A sale can contain items in one currency only.' : '';
                };

                inputs.forEach((input) => input.addEventListener('input', updateCart));
                amountTendered.addEventListener('input', updateCart);
                paymentMethod.addEventListener('change', updateCart);
                location.addEventListener('change', updateCart);
                form.querySelectorAll('.pos-quantity-step').forEach((button) => button.addEventListener('click', () => {
                    const input = button.parentElement.querySelector('.pos-quantity');
                    const available = Number(JSON.parse(input.closest('.pos-product').dataset.available || '{}')[location.value] || 0);
                    input.value = Math.min(available, Math.max(0, Math.round((Number(input.value || 0) + Number(button.dataset.step)) * 1000) / 1000));
                    input.dispatchEvent(new Event('input', { bubbles: true }));
                }));

                const updateProductVisibility = () => {
                    const term = search.value.trim().toLocaleLowerCase();
                    let visible = 0;
                    document.querySelectorAll('.pos-product').forEach((product) => {
                        const matches = product.dataset.search.includes(term)
                            && (activeCategory === 'all' || product.dataset.category === activeCategory);
                        product.hidden = !matches;
                        if (matches) visible++;
                    });
                    document.getElementById('pos-no-results').hidden = visible > 0;
                };

                search.addEventListener('input', updateProductVisibility);
                document.querySelectorAll('[data-pos-category]').forEach((button) => {
                    button.addEventListener('click', () => {
                        activeCategory = button.dataset.posCategory;
                        document.querySelectorAll('[data-pos-category]').forEach((categoryButton) => {
                            const isActive = categoryButton === button;
                            categoryButton.classList.toggle('is-active', isActive);
                            categoryButton.setAttribute('aria-pressed', String(isActive));
                        });
                        updateProductVisibility();
                    });
                });

                cart.addEventListener('click', (event) => {
                    const button = event.target.closest('[data-pos-cart-step], [data-pos-cart-remove]');
                    if (!button) return;

                    const input = inputs.find((quantityInput) =>
                        quantityInput.closest('.pos-product').dataset.menuItemId === button.dataset.menuItemId);
                    if (!input) return;

                    if (button.matches('[data-pos-cart-remove]')) {
                        input.value = '0';
                    } else {
                        const available = Number(JSON.parse(input.closest('.pos-product').dataset.available || '{}')[location.value] || 0);
                        input.value = Math.min(available, Math.max(0, Math.round((Number(input.value || 0) + Number(button.dataset.posCartStep)) * 1000) / 1000));
                    }
                    input.dispatchEvent(new Event('input', { bubbles: true }));
                });

                barcodeInput.addEventListener('keydown', (event) => {
                    if (event.key !== 'Enter') return;
                    event.preventDefault();

                    const barcode = barcodeInput.value.trim().toUpperCase();
                    const match = inputs.find((input) => input.closest('.pos-product').dataset.barcode === barcode && barcode !== '');
                    const feedback = document.getElementById('pos-barcode-feedback');
                    feedback.hidden = Boolean(match);
                    if (!match) return;

                    match.value = Math.round((Number(match.value || 0) + 1) * 1000) / 1000;
                    match.dispatchEvent(new Event('input', { bubbles: true }));
                    barcodeInput.value = '';
                });

                const imageModal = document.getElementById('posImagePreviewModal');
                imageModal.addEventListener('show.bs.modal', (event) => {
                    const trigger = event.relatedTarget;
                    const image = imageModal.querySelector('#posImagePreview');
                    image.src = trigger.dataset.imageSrc;
                    image.alt = trigger.dataset.imageAlt;
                    imageModal.querySelector('#posImagePreviewTitle').textContent = trigger.dataset.imageAlt;
                });

                updateCart();
            });
        </script>
    @endif
@endsection
