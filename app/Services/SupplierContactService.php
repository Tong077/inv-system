<?php

namespace App\Services;

use App\Models\Supplier;
use App\Models\SupplierContact;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class SupplierContactService
{
    public function getAll(?string $search = null, ?int $supplierId = null): LengthAwarePaginator
    {
        return SupplierContact::query()
            ->with('supplier')
            ->when($supplierId !== null, fn (Builder $query) => $query->where('supplier_id', $supplierId))
            ->when($search !== null && $search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('position', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhereHas('supplier', function (Builder $suppliers) use ($search): void {
                            $suppliers->where('name', 'like', "%{$search}%")
                                ->orWhere('code', 'like', "%{$search}%");
                        });
                });
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();
    }

    /** @return Collection<int, Supplier> */
    public function getSuppliers(): Collection
    {
        return Supplier::query()->orderBy('name')->get(['id', 'code', 'name', 'is_active']);
    }

    public function create(array $data): SupplierContact
    {
        return SupplierContact::create($data);
    }

    public function update(SupplierContact $contact, array $data): SupplierContact
    {
        $contact->update($data);

        return $contact->refresh();
    }

    public function delete(SupplierContact $contact): bool
    {
        return (bool) $contact->delete();
    }
}
