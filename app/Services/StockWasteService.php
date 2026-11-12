<?php
namespace App\Services;
use App\Models\StockLocation;
use App\Models\StockWaste;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
class StockWasteService
{
    public function __construct(private readonly InventoryService $inventory, private readonly DocumentNumberService $documentNumbers)
    {
    }
    public function getAll(?string $s = null, ?string $st = null): LengthAwarePaginator
    {
        return StockWaste::query()->with('stockLocation')->withCount('items')->when($s, fn(Builder $q) => $q->where(fn(Builder $q) => $q->where('waste_number', 'like', "%$s%")->orWhere('reason', 'like', "%$s%")))->when(in_array($st, ['draft', 'posted', 'cancelled'], true), fn(Builder $q) => $q->where('status', $st))->orderByDesc('waste_date')->orderByDesc('id')->paginate(15)->withQueryString();
    }
    public function locations()
    {
        return StockLocation::query()->orderBy('name')->get(['id', 'code', 'name', 'is_active']);
    }
    public function create(array $d): StockWaste
    {
        return DB::transaction(function () use ($d): StockWaste {
            $d['waste_number'] = $this->documentNumbers->next('WST', 'stock_wastes', 'waste_number');
            $d['status'] = 'draft';
            $d['created_by'] = auth()->id();

            return StockWaste::create($d);
        });
    }
    public function update(StockWaste $w, array $d): StockWaste
    {
        $w->refresh();
        if ($w->status !== 'draft')
            throw ValidationException::withMessages(['status' => 'Only draft waste records can be edited.']);
        unset($d['waste_number']);
        $w->update($d);
        return $w->refresh();
    }
    public function post(StockWaste $w): StockWaste
    {
        return $this->inventory->postStockWaste($w);
    }
    public function delete(StockWaste $w): bool
    {
        return DB::transaction(function () use ($w): bool {
            $x = StockWaste::query()->whereKey($w->id)->lockForUpdate()->firstOrFail();
            return $x->status === 'draft' && (bool) $x->delete(); });
    }
}
