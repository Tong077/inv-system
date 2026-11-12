<x-table :rows="$categories" caption="Menu categories"
    :empty-title="$search !== '' || in_array($status, ['active', 'inactive'], true) ? 'No matching menu categories' : 'No menu categories yet'"
    :empty-description="$search !== '' || in_array($status, ['active', 'inactive'], true) ? 'Try changing your search or status filter.' : 'Create a category to organize menu items.'">
    <x-slot:toolbar>
        <div class="category-toolbar">
            <button type="button" class="btn btn-sm btn-success text-nowrap" data-bs-toggle="offcanvas"
                data-bs-target="#createMenuCategoryDrawer" aria-label="Add menu category">
                <i class="bi bi-plus-lg" aria-hidden="true"></i>
            </button>

            <x-search-filter :action="route('menu-categories.index')"
                search-label="Search menu categories"
                search-placeholder="Search by category name or description"
                search-container-class="category-search-field"
                class="category-search-filter"
                data-live-search="true"
                data-live-target="#menu-category-results"
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
            <th scope="col">Category</th>
            <th scope="col">Description</th>
            <th scope="col" class="text-end">Menu items</th>
            <th scope="col">Status</th>
            <th scope="col" class="text-end">Actions</th>
        </tr>
    </x-slot:head>

    <x-slot:body>
        @foreach ($categories as $category)
            <tr>
                <td class="fw-semibold">{{ $category->name }}</td>
                <td>{{ $category->description ?: '—' }}</td>
                <td class="text-end">{{ $category->menu_items_count }}</td>
                <td>
                    <span class="badge {{ $category->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">
                        {{ $category->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </td>
                <td class="text-end text-nowrap">
                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="offcanvas"
                        data-bs-target="#editMenuCategoryDrawer-{{ $category->id }}" aria-label="Edit {{ $category->name }}">
                        <i class="bi bi-pencil" aria-hidden="true"></i>
                    </button>
                    <x-confirm-delete :action="route('menu-categories.destroy', $category)"
                        :title="'Delete ' . $category->name . '?'"
                        text="This category cannot be deleted while it contains menu items."
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
    @include('menu-categories.partials.edit-drawers')
</div>
