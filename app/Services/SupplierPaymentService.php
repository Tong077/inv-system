<?php

namespace App\Services;

use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SupplierPaymentService
{
    public function getAll(?string $search = null, ?string $status = null): LengthAwarePaginator
    {
        return SupplierPayment::query()->with(['supplier', 'purchaseOrder'])
            ->when(in_array($status, ['pending', 'paid', 'cancelled'], true), fn(Builder $q) => $q->where('status', $status))
            ->when($search !== null && $search !== '', function (Builder $q) use ($search): void {
                $q->where(function (Builder $q) use ($search): void {
                    $q->where('payment_method', 'like', "%{$search}%")->orWhere('reference_number', 'like', "%{$search}%")->orWhereHas('supplier', fn(Builder $s) => $s->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%"))->orWhereHas('purchaseOrder', fn(Builder $o) => $o->where('po_number', 'like', "%{$search}%")); }); })
            ->orderByDesc('payment_date')->orderByDesc('id')->paginate(15)->withQueryString();
    }

    public function create(array $data): SupplierPayment
    {
        return DB::transaction(function () use ($data): SupplierPayment {
            $this->validateOrderSupplier($data);
            $data['status'] = $data['status'] ?? 'pending';
            $payment = SupplierPayment::create($data);
            $this->assertOrderPaidTotal($payment);
            return $payment; });
    }

    public function update(SupplierPayment $payment, array $data): SupplierPayment
    {
        return DB::transaction(function () use ($payment, $data): SupplierPayment {
            $payment->refresh();
            $this->validateOrderSupplier($data);
            $payment->update($data);
            $this->assertOrderPaidTotal($payment);
            return $payment->refresh(); });
    }

    public function delete(SupplierPayment $payment): bool
    {
        return (bool) $payment->delete();
    }

    private function validateOrderSupplier(array $data): void
    {
        if (empty($data['purchase_order_id']))
            return;
        $order = PurchaseOrder::query()->findOrFail($data['purchase_order_id']);
        if ((int) $order->supplier_id !== (int) $data['supplier_id'])
            throw ValidationException::withMessages(['supplier_id' => 'The supplier must match the selected purchase order.']);
        if ($order->status === 'draft' || $order->status === 'cancelled')
            throw ValidationException::withMessages(['purchase_order_id' => 'Payments can only be linked to an ordered purchase order.']);
    }

    private function assertOrderPaidTotal(SupplierPayment $payment): void
    {
        if ($payment->purchase_order_id === null || $payment->status !== 'paid')
            return;
        $order = PurchaseOrder::query()->findOrFail($payment->purchase_order_id);
        $paid = (float) SupplierPayment::query()->where('purchase_order_id', $order->id)->where('status', 'paid')->sum('amount');
        if ($paid > (float) $order->total_amount + 0.005)
            throw ValidationException::withMessages(['amount' => 'Paid amounts cannot exceed the purchase order total.']);
    }
}
