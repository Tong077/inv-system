@extends('layouts.app')

@section('title', 'Inventory overview | Stockroom')
@section('breadcrumb', 'Overview')
@section('eyebrow', 'WORKSPACE OVERVIEW')
@section('heading', 'Inventory overview')
@section('description', 'A live view of your inventory setup, locations, and posted waste activity.')

@section('content')
    <div class="row g-3">
        <div class="col-12 col-sm-6 col-xl-4">
            <div class="info-box">
                <span class="info-box-icon text-bg-success shadow-sm" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none">
                        <path d="M4 7.5 12 3l8 4.5v9L12 21l-8-4.5v-9Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
                        <path d="m4.5 7.7 7.5 4.4 7.5-4.4M12 12.1V21" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
                    </svg>
                </span>
                <div class="info-box-content">
                    <span class="info-box-text">Ingredients</span>
                    <span class="info-box-number">{{ number_format($ingredientCount) }}</span>
                    <span class="text-secondary small">In your ingredient catalogue</span>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-4">
            <div class="info-box">
                <span class="info-box-icon text-bg-primary shadow-sm" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none">
                        <path d="M3.5 10 12 4l8.5 6v9.5h-17V10Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
                        <path d="M9 19.5v-6h6v6" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
                    </svg>
                </span>
                <div class="info-box-content">
                    <span class="info-box-text">Stock locations</span>
                    <span class="info-box-number">{{ number_format($locationCount) }}</span>
                    <span class="text-secondary small">Active storage locations</span>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-4">
            <div class="info-box">
                <span class="info-box-icon text-bg-warning shadow-sm" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none">
                        <path d="M5 7h14M9 7V4h6v3m3 0-.8 13H6.8L6 7m4 3v6m4-6v6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
                <div class="info-box-content">
                    <span class="info-box-text">Waste records</span>
                    <span class="info-box-number">{{ number_format($postedWasteCount) }}</span>
                    <span class="text-secondary small">Posted waste records</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mt-1">
        <div class="col-12 col-xl-7">
            <section class="card welcome-card h-100" aria-labelledby="welcome-heading">
                <div class="card-body d-flex align-items-center justify-content-between gap-4">
                    <div class="welcome-copy">
                        <span class="welcome-kicker">
                            <span class="welcome-kicker-dot" aria-hidden="true"></span>
                            READY WHEN YOU ARE
                        </span>
                        <h2 id="welcome-heading">Build a reliable inventory foundation.</h2>
                        <p>
                            Start with ingredients and base units. Then receiving, recipe costing,
                            and sales consumption can use consistent stock data.
                        </p>
                    </div>
                    <div class="welcome-art d-none d-sm-block" aria-hidden="true">
                        <div class="art-card art-card-back"></div>
                        <div class="art-card art-card-front">
                            <span class="art-box art-box-one"></span>
                            <span class="art-box art-box-two"></span>
                            <span class="art-box art-box-three"></span>
                        </div>
                        <span class="art-spark art-spark-one"></span>
                        <span class="art-spark art-spark-two"></span>
                    </div>
                </div>
            </section>
        </div>

        <div class="col-12 col-xl-5">
            <section class="card h-100" aria-labelledby="getting-started-heading">
                <div class="card-header">
                    <h3 class="card-title" id="getting-started-heading">Getting started</h3>
                    <div class="card-tools">
                        <span class="badge text-bg-light">Phase 1</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item d-flex gap-3 py-3">
                            <span class="step-number">01</span>
                            <div>
                                <h4 class="step-title">Set up stock locations</h4>
                                <p class="step-description">Active locations available: {{ number_format($locationCount) }}.</p>
                            </div>
                        </div>
                        <div class="list-group-item d-flex gap-3 py-3">
                            <span class="step-number">02</span>
                            <div>
                                <h4 class="step-title">Build your ingredient catalogue</h4>
                                <p class="step-description">{{ number_format($ingredientCount) }} ingredients currently tracked.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
@endsection
