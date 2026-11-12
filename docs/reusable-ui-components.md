# Reusable Blade UI components

The shared toast container is included by `layouts.app`. Redirect with one of these session keys to show a notification:

```php
return to_route('items.index')->with('success', 'Ingredient saved.');
return back()->with('warning', 'This record is still in use.');
return back()->with('error', 'The record could not be saved. Please try again.');
```

For an unexpected exception, report the exception for server logs and show a safe message to the user:

```php
try {
    $service->save($data);
} catch (Throwable $exception) {
    report($exception);

    return back()->withInput()->with('error', 'The record could not be saved. Please try again.');
}
```

Do not put exception details in the toast. Validation errors are also shown by the shared toast and can be rendered inline with Blade's `@error` directive.

## Delete confirmation

Use the reusable `x-confirm-delete` form for destructive actions. It sends the normal CSRF-protected DELETE request only after SweetAlert2 confirmation:

```blade
<x-confirm-delete
    :action="route('units.destroy', $unit)"
    :title="'Delete ' . $unit->name . '?'"
    text="This unit will be permanently deleted if it is not in use."
    :button-aria-label="'Delete ' . $unit->name"
    button-class="btn btn-sm btn-outline-danger"
>
    <i class="bi bi-trash" aria-hidden="true"></i>
</x-confirm-delete>
```

The component accepts `title`, `text`, `confirm-text`, `cancel-text`, `button-class`, `button-label`, `button-aria-label`, `form-class`, and `method` props.

## Modal

Use Bootstrap's normal `data-bs-toggle="modal"` trigger. Put a form or other content inside the component:

```blade
<button type="button" data-bs-toggle="modal" data-bs-target="#editItemModal">Edit</button>

<x-modal id="editItemModal" size="lg" centered scrollable>
    <form class="modal-content" method="POST" action="{{ route('items.update', $item) }}">
        @csrf
        @method('PUT')
        <div class="modal-header">
            <h2 class="modal-title fs-5" id="editItemModal-label">Edit item</h2>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">...</div>
        <div class="modal-footer">
            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
            <button class="btn btn-primary">Save</button>
        </div>
    </form>
</x-modal>
```

Available props: `id` (required), `size` (`sm`, `lg`, `xl`, or `fullscreen`), `centered`, `scrollable`, and `dialog-class`. Extra HTML attributes and classes are passed to the modal root.

## Drawer

```blade
<button type="button" data-bs-toggle="offcanvas" data-bs-target="#itemDetails">Details</button>

<x-drawer id="itemDetails" title="Item details" placement="end" width="32rem" backdrop="static">
    <p>Drawer content goes here.</p>

    <x-slot:footer>
        <button type="button" class="btn btn-light" data-bs-dismiss="offcanvas">Close</button>
    </x-slot:footer>
</x-drawer>
```

Drawer props: `id` (required), `placement` (`start`, `end`, `top`, or `bottom`), `title`, `width`, `height`, `backdrop` (`true`, `false`, or `static`), `scroll`, and `keyboard`. Use the `header` and `footer` slots to replace or add to the default header and footer. Extra HTML attributes and classes are passed to the offcanvas root.

For a page with several drawers, keep their markup in a view partial and include it from the index page. The ingredient category screen follows this pattern with `@include('ingredient-categories.partials.drawers')`; its forms and action buttons are separate from the table markup.
