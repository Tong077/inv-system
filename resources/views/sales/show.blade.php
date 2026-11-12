@extends('layouts.app')

@section('title', $sale->receipt_number.' | '.config('app.name', 'Stockroom'))
@section('breadcrumb', 'Sales receipt')
@section('eyebrow', 'SALES RECEIPT')
@section('heading', $sale->receipt_number)
@section('description', $sale->sold_at?->format('M j, Y H:i').' · '.$sale->stockLocation?->name)
@section('actions')
    <div class="receipt-actions d-flex gap-2">
        <button type="button" class="btn btn-light border" onclick="window.print()"><i class="bi bi-printer me-1" aria-hidden="true"></i>Print receipt</button>
        <a href="{{ route('sales.index') }}" class="btn btn-light border">Sales history</a>
        <a href="{{ route('pos.index') }}" class="btn btn-success"><i class="bi bi-plus-lg me-1" aria-hidden="true"></i>New sale</a>
    </div>
@endsection

@section('content')
    <section class="card sale-receipt">
        <div class="card-header d-flex flex-wrap justify-content-between gap-2">
            <div><span class="text-secondary">Receipt</span><div class="fw-semibold">{{ $sale->receipt_number }}</div><span class="small text-secondary">{{ $sale->sold_at?->format('M j, Y H:i') }}</span></div>
            <div><span class="text-secondary">Location</span><div class="fw-semibold">{{ $sale->stockLocation?->name }}</div></div>
            <div class="text-end"><span class="text-secondary">Payment</span><div class="fw-semibold">{{ ['qr' => 'QR payment'][$sale->payment_method] ?? ucfirst($sale->payment_method) }}</div></div>
        </div>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead><tr><th scope="col">Item</th><th scope="col" class="text-end">Qty</th><th scope="col" class="text-end">Price</th><th scope="col" class="text-end">Total</th></tr></thead>
                <tbody>
                    @foreach ($sale->items as $item)
                        <tr><td><span class="fw-semibold">{{ $item->item_name }}</span><span class="d-block small text-secondary">{{ $item->item_code }}</span>@if ($item->stockIngredient)<span class="d-block small text-secondary">Deducted {{ number_format((float) $item->stock_quantity, 3) }} {{ $item->stockIngredient->baseUnit?->abbreviation }} of {{ $item->stockIngredient->name }}</span>@endif</td><td class="text-end">{{ number_format((float) $item->quantity, 3) }}</td><td class="text-end">{{ $sale->currency }} {{ number_format((float) $item->unit_price, 2) }}</td><td class="text-end">{{ $sale->currency }} {{ number_format((float) $item->line_total, 2) }}</td></tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr><th colspan="3" class="text-end">Subtotal</th><th class="text-end">{{ $sale->currency }} {{ number_format((float) $sale->subtotal, 2) }}</th></tr>
                    <tr><th colspan="3" class="text-end">Total</th><th class="text-end">{{ $sale->currency }} {{ number_format((float) $sale->total, 2) }}</th></tr>
                    <tr><td colspan="3" class="text-end">Amount received</td><td class="text-end">{{ $sale->currency }} {{ number_format((float) $sale->amount_tendered, 2) }}</td></tr>
                    <tr><td colspan="3" class="text-end">Change</td><td class="text-end">{{ $sale->currency }} {{ number_format((float) $sale->change_due, 2) }}</td></tr>
                </tfoot>
            </table>
        </div>
        <div class="card-footer small text-secondary">Stock was deducted from the selected location using the recipe or direct stock setting when this sale was completed.</div>
    </section>
@endsection
