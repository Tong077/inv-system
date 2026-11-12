@extends('layouts.app')

@section('title', 'Other recipe costs | Stockroom')
@section('breadcrumb', 'Other recipe costs')
@section('eyebrow', 'RECIPES & COSTING')
@section('heading', 'Other recipe costs')
@section('description', 'Record packaging, labor, and other costs for each recipe version.')

@section('content')
    <div id="recipe-other-cost-results" aria-live="polite" aria-busy="false">
        @include('recipe-other-costs.partials.results')
    </div>
    @include('recipe-other-costs.partials.drawers')

    @if (session('openRecipeOtherCostDrawer'))
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const drawerId = @json(session('openRecipeOtherCostDrawer') === 'create' ? 'createRecipeOtherCostDrawer' : 'editRecipeOtherCostDrawer-' . session('openRecipeOtherCostDrawer'));
                const drawer = document.getElementById(drawerId);
                if (drawer) window.bootstrap.Offcanvas.getOrCreateInstance(drawer).show();
            });
        </script>
    @endif
@endsection
