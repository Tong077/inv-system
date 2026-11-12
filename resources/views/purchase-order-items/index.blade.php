@extends('layouts.app')

@section('title', 'Purchase order lines | Stockroom')
@section('breadcrumb', 'Purchase order lines')
@section('eyebrow', 'PURCHASING')
@section('heading', 'Purchase order lines')
@section('description', 'Manage the ingredients and quantities on draft purchase orders.')

@section('content')
    <div id="purchase-order-item-results" aria-live="polite" aria-busy="false">
        @include('purchase-order-items.partials.results')
    </div>
    @include('purchase-order-items.partials.drawers')

    @if (session('openPurchaseOrderItemDrawer'))
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const drawerId = @json(session('openPurchaseOrderItemDrawer') === 'create' ? 'createPurchaseOrderItemDrawer' : 'editPurchaseOrderItemDrawer-' . session('openPurchaseOrderItemDrawer'));
                const drawer = document.getElementById(drawerId);
                if (drawer) window.bootstrap.Offcanvas.getOrCreateInstance(drawer).show();
            });
        </script>
    @endif
@endsection
