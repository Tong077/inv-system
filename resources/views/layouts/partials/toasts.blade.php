@php
    $notifications = [];

    foreach ([
        'success' => ['title' => 'Success', 'variant' => 'success', 'icon' => 'bi-check-lg'],
        'status' => ['title' => 'Success', 'variant' => 'success', 'icon' => 'bi-check-lg'],
        'error' => ['title' => 'Error', 'variant' => 'danger', 'icon' => 'bi-exclamation-lg'],
        'warning' => ['title' => 'Warning', 'variant' => 'warning', 'icon' => 'bi-exclamation-triangle'],
        'info' => ['title' => 'Information', 'variant' => 'info', 'icon' => 'bi-info-lg'],
    ] as $key => $style) {
        $messages = session($key);
        $messages = is_array($messages) ? $messages : [$messages];

        foreach ($messages as $message) {
            if (is_string($message) && filled($message)) {
                $notifications[] = [...$style, 'message' => $message];
            }
        }
    }

    foreach ($errors->all() as $message) {
        $notifications[] = [
            'title' => 'Please check your input',
            'variant' => 'danger',
            'icon' => 'bi-exclamation-lg',
            'message' => $message,
        ];
    }
@endphp

@if ($notifications !== [])
    <div class="toast-container position-fixed top-0 end-0 p-3" aria-live="assertive" aria-atomic="true">
        @foreach ($notifications as $notification)
            <div
                class="toast toast-{{ $notification['variant'] }} border-0"
                role="alert"
                aria-live="assertive"
                aria-atomic="true"
                data-bs-autohide="{{ $notification['variant'] === 'danger' ? 'false' : 'true' }}"
            >
                <div class="d-flex">
                    <div class="toast-body">
                        <span class="toast-icon" aria-hidden="true">
                            <i class="bi {{ $notification['icon'] }}"></i>
                        </span>
                        <div class="toast-copy">
                            <span class="toast-title">{{ $notification['title'] }}</span>
                            <span class="toast-message">{{ $notification['message'] }}</span>
                        </div>
                    </div>
                    <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        @endforeach
    </div>
@endif
