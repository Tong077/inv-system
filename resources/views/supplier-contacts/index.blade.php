@extends('layouts.app')

@section('title', 'Supplier contacts | Stockroom')
@section('breadcrumb', 'Supplier contacts')
@section('eyebrow', 'PURCHASING SETUP')
@section('heading', 'Supplier contacts')
@section('description', 'Keep supplier representatives and contact details together.')

@section('content')
    <div id="supplier-contact-results" aria-live="polite" aria-busy="false">
        @include('supplier-contacts.partials.results')
    </div>
    @include('supplier-contacts.partials.drawers')

    @if (session('openSupplierContactDrawer'))
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const drawerId = @json(session('openSupplierContactDrawer') === 'create' ? 'createSupplierContactDrawer' : 'editSupplierContactDrawer-' . session('openSupplierContactDrawer'));
                const drawer = document.getElementById(drawerId);
                if (drawer) window.bootstrap.Offcanvas.getOrCreateInstance(drawer).show();
            });
        </script>
    @endif
@endsection
