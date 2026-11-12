@extends('layouts.app')
@section('title', 'Goods receipts | Stockroom')
@section('breadcrumb', 'Goods receipts')
@section('eyebrow', 'PURCHASING')
@section('heading', 'Goods receipts')
@section('description', 'Record delivered goods and post accepted quantities into inventory.')
@section('content')
    <div id="goods-receipt-results" aria-live="polite" aria-busy="false">@include('goods-receipts.partials.results')</div>
    @include('goods-receipts.partials.drawers')
    @if (session('openGoodsReceiptDrawer'))
        <script>document.addEventListener('DOMContentLoaded',()=>{const id=@json(session('openGoodsReceiptDrawer')==='create'?'createGoodsReceiptDrawer':'editGoodsReceiptDrawer-'.session('openGoodsReceiptDrawer'));const el=document.getElementById(id);if(el)window.bootstrap.Offcanvas.getOrCreateInstance(el).show();});</script>
    @endif
@endsection
