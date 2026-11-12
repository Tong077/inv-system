@extends('layouts.app')

@section('title', 'Inventory categories | Stockroom')
@section('breadcrumb', 'Inventory categories')
@section('eyebrow', 'INVENTORY SETUP')
@section('heading', 'Inventory categories')

@section('content')
    <div id="ingredient-category-results" aria-live="polite" aria-busy="false">
        @include('ingredient-categories.partials.results')
    </div>
    @include('ingredient-categories.partials.drawers')

    @if (session('openCategoryDrawer'))
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const drawerId = @json(session('openCategoryDrawer') === 'create' ? 'createCategoryDrawer' : 'editCategoryDrawer-' . session('openCategoryDrawer'));
                const drawerElement = document.getElementById(drawerId);
                if (drawerElement) window.bootstrap.Offcanvas.getOrCreateInstance(drawerElement).show();
            });
        </script>
    @endif
@endsection
