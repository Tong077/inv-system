@php
    $notifications = [];

    foreach ([
        'success' => ['title' => 'Success', 'variant' => 'success'],
        'status' => ['title' => 'Success', 'variant' => 'success'],
        'error' => ['title' => 'Error', 'variant' => 'danger'],
        'warning' => ['title' => 'Warning', 'variant' => 'warning'],
        'info' => ['title' => 'Information', 'variant' => 'info'],
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
        $notifications[] = ['title' => 'Please check your input', 'variant' => 'danger', 'message' => $message];
    }
@endphp

@if ($notifications !== [])
    <div class="toast-container position-fixed top-0 end-0 p-3" aria-live="assertive" aria-atomic="true">
        @foreach ($notifications as $notification)
            <div
                class="toast border-0 text-bg-{{ $notification['variant'] }}"
                role="alert"
                aria-live="assertive"
                aria-atomic="true"
                data-bs-autohide="{{ $notification['variant'] === 'danger' ? 'false' : 'true' }}"
            >
                <div class="d-flex">
                    <div class="toast-body">
                        <span class="fw-semibold">{{ $notification['title'] }}:</span>
                        {{ $notification['message'] }}
                    </div>
                    <button type="button" class="btn-close {{ $notification['variant'] === 'warning' || $notification['variant'] === 'info' ? '' : 'btn-close-white' }} me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        @endforeach
    </div>
@endif
