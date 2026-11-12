<x-table :rows="$suppliers" caption="Suppliers"
    :empty-title="$search !== '' || in_array($status, ['active', 'inactive'], true) ? 'No matching suppliers' : 'No suppliers yet'"
    :empty-description="$search !== '' || in_array($status, ['active', 'inactive'], true) ? 'Try changing your search or status filter.' : 'Add suppliers before recording ingredient prices or purchase orders.'">
    <x-slot:toolbar>
        <div class="category-toolbar">
            <button type="button" class="btn btn-sm btn-success text-nowrap" data-bs-toggle="offcanvas"
                data-bs-target="#createSupplierDrawer" aria-label="Add supplier">
                <i class="bi bi-plus-lg" aria-hidden="true"></i>
            </button>
            <x-search-filter :action="route('suppliers.index')"
                search-label="Search suppliers"
                search-placeholder="Search by supplier code, name, phone, or email"
                search-container-class="category-search-field"
                class="category-search-filter"
                data-live-search="true"
                data-live-target="#supplier-results"
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
            <th scope="col">Supplier</th>
            <th scope="col">Contact details</th>
            <th scope="col">Payment terms</th>
            <th scope="col" class="text-end">Contacts</th>
            <th scope="col">Status</th>
            <th scope="col" class="text-end">Actions</th>
        </tr>
    </x-slot:head>
    <x-slot:body>
        @foreach ($suppliers as $supplier)
            <tr>
                <td>
                    <span class="d-block fw-semibold">{{ $supplier->name }}</span>
                    <span class="small text-secondary">{{ $supplier->code }}</span>
                </td>
                <td>
                    <span class="d-block">{{ $supplier->email ?: '—' }}</span>
                    <span class="small text-secondary">{{ $supplier->phone ?: '—' }}</span>
                </td>
                <td>{{ $supplier->payment_terms ?: '—' }}</td>
                <td class="text-end">{{ $supplier->contacts_count }}</td>
                <td><span class="badge {{ $supplier->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $supplier->is_active ? 'Active' : 'Inactive' }}</span></td>
                <td class="text-end text-nowrap">
                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="offcanvas"
                        data-bs-target="#editSupplierDrawer-{{ $supplier->id }}" aria-label="Edit {{ $supplier->name }}">
                        <i class="bi bi-pencil" aria-hidden="true"></i>
                    </button>
                    <x-confirm-delete :action="route('suppliers.destroy', $supplier)"
                        :title="'Delete ' . $supplier->name . '?'"
                        text="A supplier with contacts, price records, orders, receipts, or payments cannot be deleted."
                        :button-aria-label="'Delete ' . $supplier->name"
                        button-class="btn btn-sm btn-outline-danger">
                        <i class="bi bi-trash" aria-hidden="true"></i>
                    </x-confirm-delete>
                </td>
            </tr>
        @endforeach
    </x-slot:body>
</x-table>
<div data-live-edit-drawers>
    @include('suppliers.partials.edit-drawers')
</div>
