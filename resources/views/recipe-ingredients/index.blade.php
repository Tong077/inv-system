@extends('layouts.app')

@section('title', 'Recipe ingredients | Stockroom')
@section('breadcrumb', 'Recipe ingredients')
@section('eyebrow', 'RECIPES & COSTING')
@section('heading', 'Recipe ingredients')
@section('description', 'Manage ingredient quantities, units, waste, and recipe cost snapshots.')

@section('content')
    <div id="recipe-ingredient-results" aria-live="polite" aria-busy="false">
        @include('recipe-ingredients.partials.results')
    </div>
    @include('recipe-ingredients.partials.drawers')

    @if (session('openRecipeIngredientDrawer'))
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const drawerId = @json(session('openRecipeIngredientDrawer') === 'create' ? 'createRecipeIngredientDrawer' : 'editRecipeIngredientDrawer-' . session('openRecipeIngredientDrawer'));
                const drawer = document.getElementById(drawerId);
                if (drawer) window.bootstrap.Offcanvas.getOrCreateInstance(drawer).show();
            });
        </script>
    @endif
@endsection
