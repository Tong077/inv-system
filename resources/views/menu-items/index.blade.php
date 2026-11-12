@extends('layouts.app')

@section('title', 'Menu items | Stockroom')
@section('breadcrumb', 'Menu items')
@section('eyebrow', 'RECIPE SETUP')
@section('heading', 'Menu items')
@section('description', 'Manage prepared menu items and packaged products sold directly from inventory.')

@section('content')
    <div id="menu-item-results" aria-live="polite" aria-busy="false">
        @include('menu-items.partials.results')
    </div>
    @include('menu-items.partials.drawers')

    @if (session('openMenuItemDrawer'))
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const drawerId = @json(session('openMenuItemDrawer') === 'create' ? 'createMenuItemDrawer' : 'editMenuItemDrawer-' . session('openMenuItemDrawer'));
                const drawer = document.getElementById(drawerId);
                if (drawer) window.bootstrap.Offcanvas.getOrCreateInstance(drawer).show();
            });
        </script>
    @endif

@endsection
