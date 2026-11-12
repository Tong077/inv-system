@extends('layouts.app')

@section('title', 'Recipe instructions | Stockroom')
@section('breadcrumb', 'Recipe instructions')
@section('eyebrow', 'RECIPES & COSTING')
@section('heading', 'Recipe instructions')
@section('description', 'Write and order the preparation steps for each recipe version.')

@section('content')
    <div id="recipe-instruction-results" aria-live="polite" aria-busy="false">
        @include('recipe-instructions.partials.results')
    </div>
    @include('recipe-instructions.partials.drawers')

    @if (session('openRecipeInstructionDrawer'))
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const drawerId = @json(session('openRecipeInstructionDrawer') === 'create' ? 'createRecipeInstructionDrawer' : 'editRecipeInstructionDrawer-' . session('openRecipeInstructionDrawer'));
                const drawer = document.getElementById(drawerId);
                if (drawer) window.bootstrap.Offcanvas.getOrCreateInstance(drawer).show();
            });
        </script>
    @endif
@endsection
