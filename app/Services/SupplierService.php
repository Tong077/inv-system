<?php

namespace App\Services;

use App\Models\Supplier;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class SupplierService
{
    public function getAll(?string $search = null, ?string $status = null): LengthAwarePaginator
    {
        return Supplier::query()
            ->withCount('contacts')
            ->when($search !== null && $search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->when(in_array($status, ['active', 'inactive'], true), function (Builder $query) use ($status): void {
                $query->where('is_active', $status === 'active');
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();
    }

    public function create(array $data): Supplier
    {
        return Supplier::create($data);
    }

    public function update(Supplier $supplier, array $data): Supplier
    {
        $supplier->update($data);

        return $supplier->refresh();
    }

    public function delete(Supplier $supplier): bool
    {
        if ($supplier->contacts()->exists()
            || $supplier->purchaseOrders()->exists()
            || $supplier->priceHistories()->exists()
            || $supplier->goodsReceipts()->exists()
            || $supplier->payments()->exists()) {
            return false;
        }

        return (bool) $supplier->delete();
    }
}
