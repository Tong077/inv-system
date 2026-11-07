@extends('layouts.app')

@section('title', 'Ingredients | Stockroom')
@section('breadcrumb', 'Ingredients')
@section('eyebrow', 'INVENTORY FOUNDATION')
@section('heading', 'Ingredients')
@section('description', 'Manage the ingredients used for purchasing, stock tracking, and recipe costing.')

@section('content')
    <x-table
        :rows="$ingredients"
        caption="Ingredients"
        pagination
        empty-title="{{ $search !== '' || in_array($status, ['active', 'inactive'], true) ? 'No matching ingredients' : 'No items yet' }}"
        empty-description="{{ $search !== '' || in_array($status, ['active', 'inactive'], true) ? 'Try changing your search or status filter.' : 'Ingredients will appear here when they are available.' }}"
    >
        <x-slot:toolbar>
            <x-search-filter
                :action="route('items.index')"
                search-label="Search ingredients"
                search-placeholder="Search by name or code"
                submit-label="Filter"
                :search-value="$search"
                :filters="[
                    [
                        'name' => 'status',
                        'label' => 'Status',
                        'placeholder' => 'All statuses',
                        'options' => ['active' => 'Active', 'inactive' => 'Inactive'],
                        'class' => 'flex-grow-1 flex-sm-grow-0',
                    ],
                ]"
            />
        </x-slot:toolbar>

        <x-slot:head>
            <tr>
                <th scope="col">Ingredient</th>
                <th scope="col">Base unit</th>
                <th scope="col" class="text-end">On hand</th>
                <th scope="col" class="text-end">Reorder at</th>
                <th scope="col">Status</th>
            </tr>
        </x-slot:head>

        <x-slot:body>
            @foreach ($ingredients as $ingredient)
                <tr>
                    <td>
                        <span class="d-block fw-semibold">{{ $ingredient->name }}</span>
                        <span class="small text-secondary">{{ $ingredient->code }}</span>
                    </td>
                    <td>{{ $ingredient->baseUnit->name }} <span class="text-secondary">({{ $ingredient->baseUnit->abbreviation }})</span></td>
                    <td class="text-end">{{ number_format((float) ($ingredient->stock_balances_sum_quantity_on_hand ?? 0), 3) }}</td>
                    <td class="text-end">{{ number_format((float) $ingredient->minimum_stock, 3) }}</td>
                    <td>
                        <span class="badge {{ $ingredient->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">
                            {{ $ingredient->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                </tr>
            @endforeach
        </x-slot:body>

    </x-table>
@endsection
