<?php

namespace App\Services;

use App\Models\StockLocation;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class StockLocationService
{
    public function getAll(?string $search = null, ?string $status = null): LengthAwarePaginator
    {
        return StockLocation::query()
            ->withCount('stockBalances')
            ->when($search !== null && $search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('location_type', 'like', "%{$search}%");
                });
            })
            ->when(in_array($status, ['active', 'inactive'], true), function (Builder $query) use ($status): void {
                $query->where('is_active', $status === 'active');
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();
    }

    public function create(array $data): StockLocation
    {
        return StockLocation::create($data);
    }

    public function update(StockLocation $location, array $data): StockLocation
    {
        $location->update($data);

        return $location->refresh();
    }

    public function delete(StockLocation $location): bool
    {
        if ($location->stockBalances()->exists()
            || $location->stockMovements()->exists()
            || $location->goodsReceipts()->exists()
            || $location->stockCounts()->exists()
            || $location->stockWastes()->exists()
            || $location->stockAdjustments()->exists()
            || $location->outgoingTransfers()->exists()
            || $location->incomingTransfers()->exists()) {
            return false;
        }

        return (bool) $location->delete();
    }
}
