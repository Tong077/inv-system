<?php
namespace App\Services;
use App\Models\StockCount;use App\Models\StockLocation;use Illuminate\Contracts\Pagination\LengthAwarePaginator;use Illuminate\Database\Eloquent\Builder;use Illuminate\Support\Facades\DB;use Illuminate\Validation\ValidationException;
class StockCountService
{
 public function __construct(private readonly InventoryService $inventory,private readonly DocumentNumberService $documentNumbers){}
 public function getAll(?string $search=null,?string $status=null):LengthAwarePaginator{return StockCount::query()->with('stockLocation')->withCount('items')->when($search,fn(Builder $q)=>$q->where(fn(Builder $q)=>$q->where('count_number','like',"%$search%")->orWhere('notes','like',"%$search%")))->when(in_array($status,['draft','posted','cancelled'],true),fn(Builder $q)=>$q->where('status',$status))->orderByDesc('count_date')->orderByDesc('id')->paginate(15)->withQueryString();}
 public function locations(){return StockLocation::query()->orderBy('name')->get(['id','code','name','is_active']);}
 public function create(array $data):StockCount{return DB::transaction(function()use($data):StockCount{$data['count_number']=$this->documentNumbers->next('CNT','stock_counts','count_number');$data['status']='draft';$data['counted_by']=auth()->id();return StockCount::create($data);});}
 public function update(StockCount $count,array $data):StockCount{$count->refresh();if($count->status!=='draft')throw ValidationException::withMessages(['status'=>'Only draft counts can be edited.']);unset($data['count_number']);$count->update($data);return $count->refresh();}
 public function post(StockCount $count):StockCount{return $this->inventory->postStockCount($count);}
 public function delete(StockCount $count):bool{return DB::transaction(function()use($count):bool{$row=StockCount::query()->whereKey($count->id)->lockForUpdate()->firstOrFail();return $row->status==='draft'&&(bool)$row->delete();});}
}
