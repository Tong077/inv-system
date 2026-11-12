<x-table :rows="$categories" caption="Inventory categories"
    :empty-title="$search !== '' || in_array($status, ['active', 'inactive'], true) ? 'No matching categories' : 'No inventory categories yet'"
    :empty-description="$search !== '' || in_array($status, ['active', 'inactive'], true) ? 'Try changing your search or status filter.' : 'Create categories for your inventory items.'">
    <x-slot:toolbar>
        <div class="category-toolbar">
            <button type="button" class="btn btn-sm btn-success text-nowrap" data-bs-toggle="offcanvas"
                data-bs-target="#createCategoryDrawer" aria-label="Add inventory category">
                <i class="bi bi-plus-lg" aria-hidden="true"></i>
            </button>

            <x-search-filter :action="route('ingredient-categories.index')"
                search-label="Search inventory categories"
                search-placeholder="Search by category name or description"
                search-container-class="category-search-field"
                class="category-search-filter"
                data-live-search="true"
                data-live-target="#ingredient-category-results"
                data-live-delay="300"
                submit-label="Filter" :search-value="$search" :filters="[
                    [
                        'name' => 'status',
                        'label' => 'Status',
                        'placeholder' => 'Show all',
                        'value' => 'all',
                        'options' => ['all' => 'Show all', 'active' => 'Active', 'inactive' => 'Inactive'],
                        'class' => 'category-status-filter',
                    ],
                ]" />
        </div>
    </x-slot:toolbar>

    <x-slot:head>
        <tr>
            <th scope="col">Category</th>
            <th scope="col">Parent category</th>
            <th scope="col">Description</th>
            <th scope="col" class="text-end">Inventory items</th>
            <th scope="col">Status</th>
            <th scope="col" class="text-end">Actions</th>
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
                <td class="text-end text-nowrap">
                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="offcanvas"
                        data-bs-target="#editCategoryDrawer-{{ $category->id }}" aria-label="Edit {{ $category->name }}">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <x-confirm-delete :action="route('ingredient-categories.destroy', $category)"
                        :title="'Delete ' . $category->name . '?'"
                        text="This category will be removed from the active list."
                        :button-aria-label="'Delete ' . $category->name"
                        button-class="btn btn-sm btn-outline-danger">
                        <i class="bi bi-trash" aria-hidden="true"></i>
                    </x-confirm-delete>
                </td>
            </tr>
        @endforeach
    </x-slot:body>
</x-table>

<div data-live-edit-drawers>
    @include('ingredient-categories.partials.edit-drawers')
</div>
