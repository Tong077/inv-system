@extends('layouts.app')

@section('title', 'Menu categories | Stockroom')
@section('breadcrumb', 'Menu categories')
@section('eyebrow', 'RECIPE SETUP')
@section('heading', 'Menu categories')
@section('description', 'Organize menu items into categories.')

@section('content')
    <div id="menu-category-results" aria-live="polite" aria-busy="false">
        @include('menu-categories.partials.results')
    </div>
    @include('menu-categories.partials.drawers')

    @if (session('openMenuCategoryDrawer'))
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const drawerId = @json(session('openMenuCategoryDrawer') === 'create' ? 'createMenuCategoryDrawer' : 'editMenuCategoryDrawer-' . session('openMenuCategoryDrawer'));
                const drawer = document.getElementById(drawerId);
                if (drawer) window.bootstrap.Offcanvas.getOrCreateInstance(drawer).show();
            });
        </script>
    @endif
@endsection
