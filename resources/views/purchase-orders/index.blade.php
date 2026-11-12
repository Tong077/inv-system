@extends('layouts.app')

@section('title', 'Purchase orders | Stockroom')
@section('breadcrumb', 'Purchase orders')
@section('eyebrow', 'PURCHASING')
@section('heading', 'Purchase orders')
@section('description', 'Track intended purchases. A purchase order does not increase stock.')

@section('content')
    <div id="purchase-order-results" aria-live="polite" aria-busy="false">
        @include('purchase-orders.partials.results')
    </div>
    @include('purchase-orders.partials.drawers')

    @if (session('openPurchaseOrderDrawer'))
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const drawerId = @json(session('openPurchaseOrderDrawer') === 'create' ? 'createPurchaseOrderDrawer' : 'editPurchaseOrderDrawer-' . session('openPurchaseOrderDrawer'));
                const drawer = document.getElementById(drawerId);
                if (drawer) window.bootstrap.Offcanvas.getOrCreateInstance(drawer).show();
            });
        </script>
    @endif
@endsection
