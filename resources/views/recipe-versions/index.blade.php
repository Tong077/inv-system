@extends('layouts.app')

@section('title', 'Recipe versions | Stockroom')
@section('breadcrumb', 'Recipe versions')
@section('eyebrow', 'RECIPES & COSTING')
@section('heading', 'Recipe versions')
@section('description', 'Manage recipe yields, version history, and effective dates.')

@section('content')
    <div id="recipe-version-results" aria-live="polite" aria-busy="false">
        @include('recipe-versions.partials.results')
    </div>
    @include('recipe-versions.partials.drawers')

    @if (session('openRecipeVersionDrawer'))
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const drawerId = @json(session('openRecipeVersionDrawer') === 'create' ? 'createRecipeVersionDrawer' : 'editRecipeVersionDrawer-' . session('openRecipeVersionDrawer'));
                const drawer = document.getElementById(drawerId);
                if (drawer) window.bootstrap.Offcanvas.getOrCreateInstance(drawer).show();
            });
        </script>
    @endif
@endsection
