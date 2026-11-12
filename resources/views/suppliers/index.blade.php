@extends('layouts.app')

@section('title', 'Suppliers | Stockroom')
@section('breadcrumb', 'Suppliers')
@section('eyebrow', 'PURCHASING SETUP')
@section('heading', 'Suppliers')
@section('description', 'Manage supplier records used for purchasing and ingredient prices.')

@section('content')
    <div id="supplier-results" aria-live="polite" aria-busy="false">
        @include('suppliers.partials.results')
    </div>
    @include('suppliers.partials.drawers')

    @if (session('openSupplierDrawer'))
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const drawerId = @json(session('openSupplierDrawer') === 'create' ? 'createSupplierDrawer' : 'editSupplierDrawer-' . session('openSupplierDrawer'));
                const drawer = document.getElementById(drawerId);
                if (drawer) window.bootstrap.Offcanvas.getOrCreateInstance(drawer).show();
            });
        </script>
    @endif
@endsection
