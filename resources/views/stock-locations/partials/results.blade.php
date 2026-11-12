<x-table :rows="$locations" caption="Stock locations"
    :empty-title="$search !== '' || in_array($status, ['active', 'inactive'], true) ? 'No matching stock locations' : 'No stock locations yet'"
    :empty-description="$search !== '' || in_array($status, ['active', 'inactive'], true) ? 'Try changing your search or status filter.' : 'Add a storage location before recording inventory.'">
    <x-slot:toolbar>
        <div class="category-toolbar">
            <button type="button" class="btn btn-sm btn-success text-nowrap" data-bs-toggle="offcanvas"
                data-bs-target="#createStockLocationDrawer" aria-label="Add stock location">
                <i class="bi bi-plus-lg" aria-hidden="true"></i>
            </button>
            <x-search-filter :action="route('stock-locations.index')"
                search-label="Search stock locations"
                search-placeholder="Search by code, name, or location type"
                search-container-class="category-search-field"
                class="category-search-filter"
                data-live-search="true"
                data-live-target="#stock-location-results"
                data-live-delay="300"
                submit-label="Filter"
                :search-value="$search"
                :filters="[
                    [
                        'name' => 'status',
                        'label' => 'Status',
                        'placeholder' => 'Show all',
                        'value' => 'all',
                        'resetValue' => 'all',
                        'showPlaceholderOption' => false,
                        'options' => ['all' => 'Show all', 'active' => 'Active', 'inactive' => 'Inactive'],
                        'class' => 'category-status-filter',
                    ],
                ]" />
        </div>
    </x-slot:toolbar>
    <x-slot:head>
        <tr>
            <th scope="col">Location</th>
            <th scope="col">Type</th>
            <th scope="col">Address</th>
            <th scope="col" class="text-end">Stock balances</th>
            <th scope="col">Status</th>
            <th scope="col" class="text-end">Actions</th>
        </tr>
    </x-slot:head>
    <x-slot:body>
        @foreach ($locations as $location)
            <tr>
                <td>
                    <span class="d-block fw-semibold">{{ $location->name }}</span>
                    <span class="small text-secondary">{{ $location->code }}</span>
                </td>
                <td>{{ $location->location_type ?: '—' }}</td>
                <td>{{ $location->address ?: '—' }}</td>
                <td class="text-end">{{ $location->stock_balances_count }}</td>
                <td><span class="badge {{ $location->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $location->is_active ? 'Active' : 'Inactive' }}</span></td>
                <td class="text-end text-nowrap">
                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="offcanvas"
                        data-bs-target="#editStockLocationDrawer-{{ $location->id }}" aria-label="Edit {{ $location->name }}">
                        <i class="bi bi-pencil" aria-hidden="true"></i>
                    </button>
                    <x-confirm-delete :action="route('stock-locations.destroy', $location)"
                        :title="'Delete ' . $location->name . '?'"
                        text="A location with inventory or transaction history cannot be deleted."
                        :button-aria-label="'Delete ' . $location->name"
                        button-class="btn btn-sm btn-outline-danger">
                        <i class="bi bi-trash" aria-hidden="true"></i>
                    </x-confirm-delete>
                </td>
            </tr>
        @endforeach
    </x-slot:body>
</x-table>
<div data-live-edit-drawers>
    @include('stock-locations.partials.edit-drawers')
</div>
