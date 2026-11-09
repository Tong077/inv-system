@extends('layouts.app')

@section('title', 'Ingredient categories | Stockroom')
@section('breadcrumb', 'Ingredient categories')
@section('eyebrow', 'INVENTORY SETUP')
@section('heading', 'Ingredient categories')
{{-- @section('description', 'Browse ingredient categories, parent groups, and the ingredients assigned to each
category.') --}}

@section('content')
    <x-table :rows="$categories" caption="Ingredient categories"
        empty-title="{{ $search !== '' || in_array($status, ['active', 'inactive'], true) ? 'No matching categories' : 'No ingredient categories yet' }}"
        empty-description="{{ $search !== '' || in_array($status, ['active', 'inactive'], true) ? 'Try changing your search or status filter.' : 'Ingredient categories will appear here when they are available.' }}">
        <x-slot:toolbar>
            <a href="#" class="btn btn-sm btn-success">
                <i class="bi bi-plus-lg me-1"></i>

            </a>
            <x-search-filter :action="route('ingredient-categories.index')" search-label="Search ingredient categories"
                search-placeholder="Search by category name or description" search-container-class="w-50"
                submit-label="Filter" :search-value="$search" :filters="[
            [
                'name' => 'status',
                'label' => 'Status',
                'placeholder' => 'All statuses',
                'options' => ['active' => 'Active', 'inactive' => 'Inactive'],
                'class' => 'w-25',
            ],
        ]" />


        </x-slot:toolbar>


        <x-slot:head>
            <tr>
                <th class="text-center" scope="col">Category</th>
                <th scope="col">Parent category</th>
                <th scope="col">Description</th>
                <th scope="col" class="text-end">Ingredients</th>
                <th scope="col">Status</th>
            </tr>
        </x-slot:head>

        <x-slot:body>
            @foreach ($categories as $category)
                <tr>
                    <td class="fw-semibold">{{ $category->name }}</td>
                    <td>{{ $category->parent?->name ?? '—' }}</td>
                    <td>{{ $category->description ?: '—' }}</td>
                    <td class="text-end">{{ $category->ingredients_count }}</td>
                    <td>
                        <span class="badge {{ $category->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">
                            {{ $category->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                </tr>
            @endforeach
        </x-slot:body>
    </x-table>
@endsection