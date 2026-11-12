@extends('layouts.app')

@section('title', 'Ingredient price history | Stockroom')
@section('breadcrumb', 'Ingredient price history')
@section('eyebrow', 'PURCHASING')
@section('heading', 'Ingredient price history')
@section('description', 'Record package prices and calculate normalized cost per ingredient base unit.')

@section('content')
    <div id="ingredient-price-results" aria-live="polite" aria-busy="false">
        @include('ingredient-price-histories.partials.results')
    </div>
    @include('ingredient-price-histories.partials.drawers')

    @if (session('openIngredientPriceDrawer'))
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const drawerId = @json(session('openIngredientPriceDrawer') === 'create' ? 'createIngredientPriceDrawer' : 'editIngredientPriceDrawer-' . session('openIngredientPriceDrawer'));
                const drawer = document.getElementById(drawerId);
                if (drawer) window.bootstrap.Offcanvas.getOrCreateInstance(drawer).show();
            });
        </script>
    @endif
@endsection
