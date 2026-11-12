<x-table :rows="$contacts" caption="Supplier contacts"
    :empty-title="$search !== '' || $supplierId !== null ? 'No matching supplier contacts' : 'No supplier contacts yet'"
    :empty-description="$search !== '' || $supplierId !== null ? 'Try changing the search or supplier filter.' : 'Add a contact person for a supplier.'">
    <x-slot:toolbar>
        <div class="category-toolbar">
            <button type="button" class="btn btn-sm btn-success text-nowrap" data-bs-toggle="offcanvas"
                data-bs-target="#createSupplierContactDrawer" aria-label="Add supplier contact">
                <i class="bi bi-plus-lg" aria-hidden="true"></i>
            </button>
            <x-search-filter :action="route('supplier-contacts.index')"
                search-label="Search supplier contacts"
                search-placeholder="Search by supplier, contact, phone, or email"
                search-container-class="category-search-field"
                class="category-search-filter"
                data-live-search="true"
                data-live-target="#supplier-contact-results"
                data-live-delay="300"
                submit-label="Filter"
                :search-value="$search"
                :filters="[
                    [
                        'name' => 'supplier_id',
                        'label' => 'Supplier',
                        'placeholder' => 'All suppliers',
                        'value' => '',
                        'options' => $suppliers->mapWithKeys(fn ($supplier) => [
                            $supplier->id => $supplier->code . ' — ' . $supplier->name,
                        ])->all(),
                        'class' => 'category-status-filter',
                    ],
                ]" />
        </div>
    </x-slot:toolbar>
    <x-slot:head>
        <tr>
            <th scope="col">Supplier</th>
            <th scope="col">Contact</th>
            <th scope="col">Position</th>
            <th scope="col">Phone</th>
            <th scope="col">Email</th>
            <th scope="col" class="text-end">Actions</th>
        </tr>
    </x-slot:head>
    <x-slot:body>
        @foreach ($contacts as $contact)
            <tr>
                <td>{{ $contact->supplier?->name ?? 'Deleted supplier' }}</td>
                <td class="fw-semibold">{{ $contact->name }}</td>
                <td>{{ $contact->position ?: '—' }}</td>
                <td>{{ $contact->phone ?: '—' }}</td>
                <td>{{ $contact->email ?: '—' }}</td>
                <td class="text-end text-nowrap">
                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="offcanvas"
                        data-bs-target="#editSupplierContactDrawer-{{ $contact->id }}" aria-label="Edit {{ $contact->name }}">
                        <i class="bi bi-pencil" aria-hidden="true"></i>
                    </button>
                    <x-confirm-delete :action="route('supplier-contacts.destroy', $contact)"
                        :title="'Delete contact ' . $contact->name . '?'"
                        text="This supplier contact will be permanently removed."
                        :button-aria-label="'Delete ' . $contact->name"
                        button-class="btn btn-sm btn-outline-danger">
                        <i class="bi bi-trash" aria-hidden="true"></i>
                    </x-confirm-delete>
                </td>
            </tr>
        @endforeach
    </x-slot:body>
</x-table>
<div data-live-edit-drawers>
    @include('supplier-contacts.partials.edit-drawers')
</div>
