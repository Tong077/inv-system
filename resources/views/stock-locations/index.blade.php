@extends('layouts.app')

@section('title', 'Stock locations | Stockroom')
@section('breadcrumb', 'Stock locations')
@section('eyebrow', 'INVENTORY SETUP')
@section('heading', 'Stock locations')
@section('description', 'Manage storage locations for inventory and transactions.')

@section('content')
    <div id="stock-location-results" aria-live="polite" aria-busy="false">
        @include('stock-locations.partials.results')
    </div>
    @include('stock-locations.partials.drawers')

    @if (session('openStockLocationDrawer'))
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const drawerId = @json(session('openStockLocationDrawer') === 'create' ? 'createStockLocationDrawer' : 'editStockLocationDrawer-' . session('openStockLocationDrawer'));
                const drawer = document.getElementById(drawerId);
                if (drawer) window.bootstrap.Offcanvas.getOrCreateInstance(drawer).show();
            });
        </script>
    @endif
@endsection
