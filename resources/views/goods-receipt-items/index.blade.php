@extends('layouts.app')
@section('title', 'Goods receipt lines | Stockroom')
@section('breadcrumb', 'Goods receipt lines')
@section('eyebrow', 'PURCHASING')
@section('heading', 'Goods receipt lines')
@section('description', 'Record delivered quantities, unit prices, batches, and expiry dates.')
@section('content')
    <div id="goods-receipt-item-results" aria-live="polite" aria-busy="false">@include('goods-receipt-items.partials.results')</div>
    @include('goods-receipt-items.partials.drawers')
    @if (session('openGoodsReceiptItemDrawer'))
        <script>document.addEventListener('DOMContentLoaded',()=>{const id=@json(session('openGoodsReceiptItemDrawer')==='create'?'createGoodsReceiptItemDrawer':'editGoodsReceiptItemDrawer-'.session('openGoodsReceiptItemDrawer'));const el=document.getElementById(id);if(el)window.bootstrap.Offcanvas.getOrCreateInstance(el).show();});</script>
    @endif
@endsection
