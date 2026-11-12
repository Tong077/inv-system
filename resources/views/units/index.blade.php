@extends('layouts.app')

@section('title', 'Units of measure | Stockroom')
@section('breadcrumb', 'Units of measure')
@section('eyebrow', 'INVENTORY SETUP')
@section('heading', 'Units of measure')
@section('description', 'Define measurement units and their conversion factors for inventory and recipes.')

@section('content')
    <div id="unit-results" aria-live="polite" aria-busy="false">
        @include('units.partials.results')
    </div>

    @include('units.partials.drawers')

    @if (session('openUnitDrawer'))
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const drawerId = @json(session('openUnitDrawer') === 'create' ? 'createUnitDrawer' : 'editUnitDrawer-' . session('openUnitDrawer'));
                const drawerElement = document.getElementById(drawerId);
                if (drawerElement) window.bootstrap.Offcanvas.getOrCreateInstance(drawerElement).show();
            });
        </script>
    @endif
@endsection