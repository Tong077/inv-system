@extends('layouts.app')

@section('title', 'Sales history | '.config('app.name', 'Stockroom'))
@section('breadcrumb', 'Sales history')
@section('eyebrow', 'SALES')
@section('heading', 'Sales history')
@section('description', 'Completed sales and the stock location that supplied each order.')
@section('actions')
    <a href="{{ route('pos.index') }}" class="btn btn-success"><i class="bi bi-shop me-1" aria-hidden="true"></i>Open POS</a>
@endsection

@section('content')
    <section class="card">
        <div class="card-header">
            <form action="{{ route('sales.index') }}" method="GET" class="d-flex flex-wrap gap-2">
                <label for="sales-search" class="visually-hidden">Search sales</label>
                <input id="sales-search" type="search" name="search" value="{{ $search }}" class="form-control" placeholder="Search receipt or menu item">
                <button type="submit" class="btn btn-light border">Search</button>
                @if ($search !== '')<a href="{{ route('sales.index') }}" class="btn btn-light border">Clear</a>@endif
            </form>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead><tr><th scope="col">Receipt</th><th scope="col">Date</th><th scope="col">Stock location</th><th scope="col">Payment</th><th scope="col">Lines</th><th scope="col" class="text-end">Total</th><th scope="col"><span class="visually-hidden">Actions</span></th></tr></thead>
                <tbody>
                    @forelse ($sales as $sale)
                        <tr>
                            <td class="fw-semibold">{{ $sale->receipt_number }}</td>
                            <td>{{ $sale->sold_at?->format('M j, Y H:i') }}</td>
                            <td>{{ $sale->stockLocation?->name }}</td>
                            <td>{{ ['qr' => 'QR payment'][$sale->payment_method] ?? ucfirst($sale->payment_method) }}</td>
                            <td>{{ $sale->items_count }}</td>
                            <td class="text-end">{{ $sale->currency }} {{ number_format((float) $sale->total, 2) }}</td>
                            <td class="text-end"><a href="{{ route('sales.show', $sale) }}" class="btn btn-sm btn-light border">View receipt</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-secondary py-4">{{ $search !== '' ? 'No sales match your search.' : 'No completed sales yet. Start from the POS.' }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($sales->hasPages())<div class="card-footer">{{ $sales->links() }}</div>@endif
    </section>
@endsection
