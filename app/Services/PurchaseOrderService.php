<?php

namespace App\Services;

use App\Models\PurchaseOrder;
use App\Models\Supplier;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PurchaseOrderService
{
    public function __construct(private readonly DocumentNumberService $documentNumbers) {}

    public function getAll(?string $search = null, ?string $status = null): LengthAwarePaginator
    {
        return PurchaseOrder::query()
            ->with('supplier')
            ->withCount('items')
            ->when($search !== null && $search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('po_number', 'like', "%{$search}%")
                        ->orWhere('notes', 'like', "%{$search}%")
                        ->orWhereHas('supplier', function (Builder $suppliers) use ($search): void {
                            $suppliers->where('name', 'like', "%{$search}%")
                                ->orWhere('code', 'like', "%{$search}%");
                        });
                });
            })
            ->when(in_array($status, ['draft', 'ordered', 'partially_received', 'received', 'cancelled'], true), fn (Builder $query) => $query->where('status', $status))
            ->orderByDesc('order_date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();
    }

    /** @return Collection<int, Supplier> */
    public function getSuppliers(): Collection
    {
        return Supplier::query()->orderBy('name')->get(['id', 'code', 'name', 'is_active']);
    }

    public function create(array $data): PurchaseOrder
    {
        return DB::transaction(function () use ($data): PurchaseOrder {
            $data['tax_amount'] = (float) ($data['tax_amount'] ?? 0);
            $data['discount_amount'] = (float) ($data['discount_amount'] ?? 0);
            $data['subtotal'] = 0;
            $data['total_amount'] = 0;
            $data['status'] = 'draft';
            $data['created_by'] = auth()->id();
            $data['po_number'] = $this->documentNumbers->next('PO', 'purchase_orders', 'po_number');

            return PurchaseOrder::create($data);
        });
    }

    public function update(PurchaseOrder $order, array $data): PurchaseOrder
    {
        return DB::transaction(function () use ($order, $data): PurchaseOrder {
            $order->refresh();

            if ($order->status !== 'draft') {
                throw ValidationException::withMessages([
                    'status' => 'Only draft purchase orders can be edited. Create a receipt for an ordered purchase order.',
                ]);
            }

            if ($data['status'] === 'ordered' && ! $order->items()->exists()) {
                throw ValidationException::withMessages([
                    'status' => 'Add at least one purchase order line before ordering this purchase order.',
                ]);
            }

            unset($data['po_number']);
            $data['tax_amount'] = (float) ($data['tax_amount'] ?? 0);
            $data['discount_amount'] = (float) ($data['discount_amount'] ?? 0);

            $order->update($data);
            $this->recalculateTotals($order);

            return $order->refresh();
        });
    }

    public function delete(PurchaseOrder $order): bool
    {
        if ($order->status !== 'draft' || $order->goodsReceipts()->exists() || $order->payments()->exists()) {
            return false;
        }

        return (bool) $order->delete();
    }

    public function recalculateTotals(PurchaseOrder $order): void
    {
        $order->refresh();
        $subtotal = (float) $order->items()->sum('total_price');
        $total = round($subtotal + (float) $order->tax_amount - (float) $order->discount_amount, 2);

        if ($subtotal > 9999999999.99 || $total > 9999999999.99) {
            throw ValidationException::withMessages([
                'discount_amount' => 'The purchase order total is above the maximum allowed amount.',
            ]);
        }

        if ($total < 0) {
            throw ValidationException::withMessages([
                'discount_amount' => 'The discount cannot be greater than the subtotal plus tax.',
            ]);
        }

        $order->update([
            'subtotal' => round($subtotal, 2),
            'total_amount' => $total,
        ]);
    }
}
