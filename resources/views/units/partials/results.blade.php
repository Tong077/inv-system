<x-table :rows="$units" caption="Units of measure"
    :empty-title="$search !== '' || in_array($status, ['active', 'inactive'], true) ? 'No matching units' : 'No units yet'"
    :empty-description="$search !== '' || in_array($status, ['active', 'inactive'], true) ? 'Try changing your search or status filter.' : 'Create base units before adding ingredients.'">
    <x-slot:toolbar>
        <div class="unit-toolbar">
            <button type="button" class="btn btn-sm btn-success text-nowrap" data-bs-toggle="offcanvas"
                data-bs-target="#createUnitDrawer" aria-label="Add unit">
                <i class="bi bi-plus-lg" aria-hidden="true"></i>
            </button>

            <x-search-filter :action="route('units.index')"
                search-label="Search units"
                search-placeholder="Search by unit, abbreviation, or dimension"
                search-container-class="unit-search-field"
                class="unit-search-filter"
                data-live-search="true"
                data-live-target="#unit-results"
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
                        'class' => 'unit-status-filter',
                    ],
                ]" />
        </div>
    </x-slot:toolbar>

    <x-slot:head>
        <tr>
            <th scope="col">Unit</th>
            <th scope="col">Abbreviation</th>
            <th scope="col">Dimension</th>
            <th scope="col" class="text-end">Factor to base</th>
            <th scope="col">Base unit</th>
            <th scope="col">Status</th>
            <th scope="col" class="text-end">Actions</th>
        </tr>
    </x-slot:head>

    <x-slot:body>
        @foreach ($units as $unit)
            <tr>
                <td class="fw-semibold">{{ $unit->name }}</td>
                <td>{{ $unit->abbreviation }}</td>
                <td class="text-capitalize">{{ $unit->dimension }}</td>
                <td class="text-end">{{ number_format((float) $unit->conversion_factor_to_base, 6) }}</td>
                <td>
                    <span class="badge {{ $unit->is_base_unit ? 'text-bg-primary' : 'text-bg-light border' }}">
                        {{ $unit->is_base_unit ? 'Yes' : 'No' }}
                    </span>
                </td>
                <td>
                    <span class="badge {{ $unit->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">
                        {{ $unit->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </td>
                <td class="text-end text-nowrap">
                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="offcanvas"
                        data-bs-target="#editUnitDrawer-{{ $unit->id }}" aria-label="Edit {{ $unit->name }}">
                        <i class="bi bi-pencil" aria-hidden="true"></i>
                    </button>
                    <x-confirm-delete :action="route('units.destroy', $unit)"
                        :title="'Delete ' . $unit->name . '?'"
                        text="This unit will be permanently deleted if it is not in use."
                        :button-aria-label="'Delete ' . $unit->name"
                        button-class="btn btn-sm btn-outline-danger">
                        <i class="bi bi-trash" aria-hidden="true"></i>
                    </x-confirm-delete>
                </td>
            </tr>
        @endforeach
    </x-slot:body>
</x-table>

<div data-live-edit-drawers>
    @include('units.partials.edit-drawers')
</div>
