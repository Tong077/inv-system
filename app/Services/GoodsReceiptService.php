<?php

namespace App\Services;

use App\Models\GoodsReceipt;
use App\Models\PurchaseOrder;
use App\Models\StockLocation;
use App\Models\Supplier;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GoodsReceiptService
{
    public function __construct(
        private readonly InventoryService $inventory,
        private readonly DocumentNumberService $documentNumbers,
    ) {}

    public function getAll(?string $search = null, ?string $status = null): LengthAwarePaginator
    {
        return GoodsReceipt::query()
            ->with(['purchaseOrder', 'supplier', 'stockLocation'])
            ->withCount('items')
            ->when($search !== null && $search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('receipt_number', 'like', "%{$search}%")
                        ->orWhere('notes', 'like', "%{$search}%")
                        ->orWhereHas('supplier', function (Builder $suppliers) use ($search): void {
                            $suppliers->where('name', 'like', "%{$search}%")
                                ->orWhere('code', 'like', "%{$search}%");
                        })
                        ->orWhereHas('purchaseOrder', fn (Builder $orders) => $orders->where('po_number', 'like', "%{$search}%"));
                });
            })
            ->when(in_array($status, ['draft', 'posted'], true), fn (Builder $query) => $query->where('status', $status))
            ->orderByDesc('received_date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();
    }

    /** @return array{purchaseOrders: Collection<int, PurchaseOrder>, suppliers: Collection<int, Supplier>, locations: Collection<int, StockLocation>} */
    public function getFormOptions(): array
    {
        return [
            'purchaseOrders' => PurchaseOrder::query()->whereIn('status', ['ordered', 'partially_received'])->with('supplier')->orderByDesc('order_date')->get(),
            'suppliers' => Supplier::query()->orderBy('name')->get(['id', 'code', 'name', 'is_active']),
            'locations' => StockLocation::query()->orderBy('name')->get(['id', 'code', 'name', 'is_active']),
        ];
    }

    public function create(array $data): GoodsReceipt
    {
        return DB::transaction(function () use ($data): GoodsReceipt {
            if ($data['purchase_order_id'] !== null) {
                $order = PurchaseOrder::query()->whereKey($data['purchase_order_id'])->lockForUpdate()->firstOrFail();

                if (! in_array($order->status, ['ordered', 'partially_received'], true)) {
                    throw ValidationException::withMessages([
                        'purchase_order_id' => 'Goods can only be received against an ordered purchase order.',
                    ]);
                }

                if ((int) $data['supplier_id'] !== (int) $order->supplier_id) {
                    throw ValidationException::withMessages([
                        'supplier_id' => 'The supplier must match the selected purchase order.',
                    ]);
                }
            } else {
                $supplier = Supplier::query()->whereKey($data['supplier_id'])->firstOrFail();
                if (! $supplier->is_active) {
                    throw ValidationException::withMessages(['supplier_id' => 'Choose an active supplier for a standalone receipt.']);
                }
            }

            $data['status'] = 'draft';
            $data['received_by'] = null;
            $data['receipt_number'] = $this->documentNumbers->next('GR', 'goods_receipts', 'receipt_number');

            return GoodsReceipt::create($data);
        });
    }

    public function update(GoodsReceipt $receipt, array $data): GoodsReceipt
    {
        return DB::transaction(function () use ($receipt, $data): GoodsReceipt {
            $receipt->refresh();

            if ($receipt->status !== 'draft') {
                throw ValidationException::withMessages([
                    'status' => 'Only draft goods receipts can be edited.',
                ]);
            }

            if ($receipt->items()->exists()
                && ((int) ($data['purchase_order_id'] ?? 0) !== (int) ($receipt->purchase_order_id ?? 0)
                    || (int) $data['supplier_id'] !== (int) $receipt->supplier_id)) {
                throw ValidationException::withMessages([
                    'purchase_order_id' => 'Remove the draft receipt lines before changing its purchase order or supplier.',
                ]);
            }

            if ($data['purchase_order_id'] !== null) {
                $order = PurchaseOrder::query()->whereKey($data['purchase_order_id'])->lockForUpdate()->firstOrFail();

                if (! in_array($order->status, ['ordered', 'partially_received'], true)) {
                    throw ValidationException::withMessages([
                        'purchase_order_id' => 'Goods can only be received against an ordered purchase order.',
                    ]);
                }

                if ((int) $data['supplier_id'] !== (int) $order->supplier_id) {
                    throw ValidationException::withMessages([
                        'supplier_id' => 'The supplier must match the selected purchase order.',
                    ]);
                }
            }

            unset($data['receipt_number']);
            $receipt->update($data);

            return $receipt->refresh();
        });
    }

    public function post(GoodsReceipt $receipt): GoodsReceipt
    {
        return $this->inventory->postGoodsReceipt($receipt);
    }

    public function delete(GoodsReceipt $receipt): bool
    {
        if ($receipt->status !== 'draft') {
            return false;
        }

        return (bool) $receipt->delete();
    }
}
