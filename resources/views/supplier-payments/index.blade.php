@extends('layouts.app')
@section('title','Supplier payments | Stockroom')
@section('breadcrumb','Supplier payments')
@section('eyebrow','PURCHASING')
@section('heading','Supplier payments')
@section('description','Track pending, completed, and cancelled payments to suppliers.')
@section('content')
<div id="supplier-payment-results" aria-live="polite" aria-busy="false">@include('supplier-payments.partials.results')</div>
@include('supplier-payments.partials.drawers')
@if(session('openSupplierPaymentDrawer'))<script>document.addEventListener('DOMContentLoaded',()=>{const id=@json(session('openSupplierPaymentDrawer')==='create'?'createSupplierPaymentDrawer':'editSupplierPaymentDrawer-'.session('openSupplierPaymentDrawer'));const el=document.getElementById(id);if(el)window.bootstrap.Offcanvas.getOrCreateInstance(el).show();});</script>@endif
@endsection
