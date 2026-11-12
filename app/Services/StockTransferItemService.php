<?php

namespace App\Services;

use App\Models\Ingredient;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Models\Unit;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockTransferItemService
{
    public function __construct(private readonly UnitConversionService $units){}
    public function getAll(?string $search=null,?int $transferId=null):LengthAwarePaginator{return StockTransferItem::query()->with(['stockTransfer.fromLocation','stockTransfer.toLocation','ingredient.baseUnit','unit'])->when($transferId,fn(Builder $q)=>$q->where('stock_transfer_id',$transferId))->when($search,fn(Builder $q)=>$q->whereHas('ingredient',fn(Builder $i)=>$i->where('name','like',"%$search%")->orWhere('code','like',"%$search%")))->orderByDesc('stock_transfer_id')->orderBy('id')->paginate(15)->withQueryString();}
    public function options(){return ['transfers'=>StockTransfer::query()->where('status','draft')->with(['fromLocation','toLocation'])->orderByDesc('transfer_date')->get(),'ingredients'=>Ingredient::query()->with('baseUnit')->orderBy('name')->get(['id','code','name','base_unit_id']),'units'=>Unit::query()->orderBy('dimension')->orderBy('name')->get(['id','name','abbreviation','dimension'])];}
    public function create(array $data):StockTransferItem{return DB::transaction(function()use($data):StockTransferItem{$t=StockTransfer::query()->whereKey($data['stock_transfer_id'])->lockForUpdate()->firstOrFail();$this->draft($t);return $t->items()->create($this->normalize($data));});}
    public function update(StockTransferItem $item,array $data):StockTransferItem{return DB::transaction(function()use($item,$data):StockTransferItem{$old=StockTransfer::query()->whereKey($item->stock_transfer_id)->lockForUpdate()->firstOrFail();$new=StockTransfer::query()->whereKey($data['stock_transfer_id'])->lockForUpdate()->firstOrFail();$this->draft($old);$this->draft($new);$item->update($this->normalize($data));return $item->refresh();});}
    public function delete(StockTransferItem $item):bool{return DB::transaction(function()use($item):bool{$t=StockTransfer::query()->whereKey($item->stock_transfer_id)->lockForUpdate()->firstOrFail();return $t->status==='draft'&&(bool)$item->delete();});}
    private function normalize(array $d):array{$i=Ingredient::query()->with('baseUnit')->findOrFail($d['ingredient_id']);$u=Unit::query()->findOrFail($d['unit_id']);$this->units->conversionFactor($u,$i->baseUnit);return $d;}
    private function draft(StockTransfer $t):void{if($t->status!=='draft')throw ValidationException::withMessages(['stock_transfer_id'=>'Lines can only be changed while the transfer is a draft.']);}
}
