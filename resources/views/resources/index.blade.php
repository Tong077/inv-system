@extends('layouts.app')

@section('title', $title.' | Stockroom')
@section('breadcrumb', $title)
@section('eyebrow', $eyebrow)
@section('heading', $title)
@section('description', $description)

@section('content')
    <x-table
        :rows="[]"
        :caption="$title"
        :empty-title="$emptyTitle"
        :empty-description="$emptyDescription"
    >
        <x-slot:head>
            <tr>
                <th scope="col">Name</th>
                <th scope="col">Details</th>
                <th scope="col" class="text-end">Actions</th>
            </tr>
        </x-slot:head>

        <x-slot:empty>
            <div class="empty-state text-center py-5">
                <span class="empty-state-icon text-success" aria-hidden="true">
                    @if (request()->routeIs('items.*'))
                        <svg viewBox="0 0 24 24" fill="none">
                            <path d="m12 3 8 4.5v9L12 21l-8-4.5v-9L12 3Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/>
                            <path d="m4.5 7.7 7.5 4.4 7.5-4.4M12 12.1V21" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/>
                        </svg>
                    @elseif (request()->routeIs('stock-locations.*'))
                        <svg viewBox="0 0 24 24" fill="none">
                            <path d="M3.5 10 12 4l8.5 6v9.5h-17V10Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/>
                            <path d="M9 19.5v-6h6v6" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/>
                        </svg>
                    @else
                        <svg viewBox="0 0 24 24" fill="none">
                            <path d="M5 7h14M9 7V4h6v3m3 0-.8 13H6.8L6 7m4 3v6m4-6v6" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    @endif
                </span>
                <h2 class="h5 mt-3">{{ $emptyTitle }}</h2>
                <p class="text-secondary mb-0">{{ $emptyDescription }}</p>
            </div>
        </x-slot:empty>
    </x-table>
@endsection
