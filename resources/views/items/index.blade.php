@extends('layouts.app')

@section('title', 'Inventory items | Stockroom')
@section('breadcrumb', 'Inventory items')
@section('eyebrow', 'INVENTORY FOUNDATION')
@section('heading', 'Inventory items')
@section('description', 'Manage stock used in recipes and products sold directly, with purchasing and reorder tracking.')

@section('content')
    <div id="ingredient-results" aria-live="polite" aria-busy="false">
        @include('items.partials.results')
    </div>

    @include('items.partials.drawers')

    @if (session('openIngredientDrawer'))
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const drawerId = @json(session('openIngredientDrawer') === 'create' ? 'createIngredientDrawer' : 'editIngredientDrawer-' . session('openIngredientDrawer'));
                const drawerElement = document.getElementById(drawerId);
                if (drawerElement) window.bootstrap.Offcanvas.getOrCreateInstance(drawerElement).show();
            });
        </script>
    @endif

@endsection
