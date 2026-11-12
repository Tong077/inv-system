<?php
namespace App\Services;
use App\Models\StockAdjustment;use App\Models\StockLocation;use Illuminate\Contracts\Pagination\LengthAwarePaginator;use Illuminate\Database\Eloquent\Builder;use Illuminate\Support\Facades\DB;use Illuminate\Validation\ValidationException;
class StockAdjustmentService
{
 public function __construct(private readonly InventoryService $inventory,private readonly DocumentNumberService $documentNumbers){}
 public function getAll(?string $s=null,?string $st=null):LengthAwarePaginator{return StockAdjustment::query()->with('stockLocation')->withCount('items')->when($s,fn(Builder $q)=>$q->where(fn(Builder $q)=>$q->where('adjustment_number','like',"%$s%")->orWhere('reason','like',"%$s%")))->when(in_array($st,['draft','posted','cancelled'],true),fn(Builder $q)=>$q->where('status',$st))->orderByDesc('adjustment_date')->orderByDesc('id')->paginate(15)->withQueryString();}
 public function locations(){return StockLocation::query()->orderBy('name')->get(['id','code','name','is_active']);}
 public function create(array $d):StockAdjustment{return DB::transaction(function()use($d):StockAdjustment{$d['adjustment_number']=$this->documentNumbers->next('ADJ','stock_adjustments','adjustment_number');$d['status']='draft';$d['created_by']=auth()->id();return StockAdjustment::create($d);});}
 public function update(StockAdjustment $a,array $d):StockAdjustment{$a->refresh();if($a->status!=='draft')throw ValidationException::withMessages(['status'=>'Only draft adjustments can be edited.']);unset($d['adjustment_number']);$a->update($d);return $a->refresh();}
 public function post(StockAdjustment $a):StockAdjustment{return $this->inventory->postStockAdjustment($a);}
 public function delete(StockAdjustment $a):bool{return DB::transaction(function()use($a):bool{$x=StockAdjustment::query()->whereKey($a->id)->lockForUpdate()->firstOrFail();return $x->status==='draft'&&(bool)$x->delete();});}
}
