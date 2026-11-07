<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="theme-color" content="#f4f6f9">

        <title>@yield('title', config('app.name', 'Stockroom'))</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet">

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <body class="layout-fixed sidebar-expand-lg bg-body-tertiary" data-bs-theme="light">
        <div class="app-wrapper">
            @include('layouts.partials.navbar')
            @include('layouts.partials.sidebar')

            <main class="app-main">
                <div class="app-content-header">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-sm-8">
                                @hasSection('eyebrow')
                                    <p class="page-eyebrow">@yield('eyebrow')</p>
                                @endif
                                <h1 class="page-title">@yield('heading', 'Overview')</h1>
                                @hasSection('description')
                                    <p class="page-description mb-0">@yield('description')</p>
                                @endif
                            </div>
                            <div class="col-sm-4">
                                <ol class="breadcrumb float-sm-end mb-0">
                                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">@yield('breadcrumb', 'Overview')</li>
                                </ol>
                            </div>
                        </div>
                        @hasSection('actions')
                            <div class="page-actions mt-3">@yield('actions')</div>
                        @endif
                    </div>
                </div>

                <div class="app-content">
                    <div class="container-fluid">
                        @yield('content')
                    </div>
                </div>
            </main>

            <footer class="app-footer">
                <strong>Stockroom</strong>
                <span class="float-end d-none d-sm-inline">Inventory, made clear.</span>
            </footer>
        </div>
        @include('layouts.partials.toasts')
    </body>
</html>
