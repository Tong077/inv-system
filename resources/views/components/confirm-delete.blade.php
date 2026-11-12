@props([
    'action',
    'title' => 'Delete this record?',
    'text' => 'This action cannot be undone.',
    'confirmText' => 'Yes, delete it',
    'cancelText' => 'Cancel',
    'buttonClass' => 'btn btn-sm btn-outline-danger',
    'buttonLabel' => 'Delete',
    'buttonAriaLabel' => null,
    'formClass' => 'd-inline',
    'method' => 'DELETE',
])

<form method="POST" action="{{ $action }}" @class([$formClass])
    data-swal-confirm
    data-swal-title="{{ $title }}"
    data-swal-text="{{ $text }}"
    data-swal-confirm-text="{{ $confirmText }}"
    data-swal-cancel-text="{{ $cancelText }}">
    @csrf
    @method($method)
    <button type="submit" class="{{ $buttonClass }}" aria-label="{{ $buttonAriaLabel ?? $buttonLabel }}">
        @if ($slot->isEmpty())
            {{ $buttonLabel }}
        @else
            {{ $slot }}
        @endif
    </button>
</form>
