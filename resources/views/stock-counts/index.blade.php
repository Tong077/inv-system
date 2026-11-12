@extends('layouts.app')
@section('title','Stock counts | Stockroom')
@section('breadcrumb','Stock counts')
@section('eyebrow','INVENTORY')
@section('heading','Stock counts')
@section('description','Reconcile system balances against physical counts.')
@section('content')
<div id="stock-count-results" aria-live="polite">@include('stock-counts.partials.results')</div>
@include('stock-counts.partials.drawers')
@if(session('openStockCountDrawer'))<script>document.addEventListener('DOMContentLoaded',()=>{let id=@json(session('openStockCountDrawer')==='create'?'createStockCountDrawer':'editStockCountDrawer-'.session('openStockCountDrawer'));let e=document.getElementById(id);if(e)bootstrap.Offcanvas.getOrCreateInstance(e).show()});</script>@endif
@endsection
