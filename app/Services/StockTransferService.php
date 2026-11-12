<?php

namespace App\Services;

use App\Models\StockLocation;
use App\Models\StockTransfer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockTransferService
{
    public function __construct(private readonly InventoryService $inventory,private readonly DocumentNumberService $documentNumbers) {}
    public function getAll(?string $search=null,?string $status=null):LengthAwarePaginator{return StockTransfer::query()->with(['fromLocation','toLocation'])->withCount('items')->when($search,fn(Builder $q)=>$q->where(fn(Builder $q)=>$q->where('transfer_number','like',"%$search%")->orWhere('notes','like',"%$search%")))->when(in_array($status,['draft','posted','cancelled'],true),fn(Builder $q)=>$q->where('status',$status))->orderByDesc('transfer_date')->orderByDesc('id')->paginate(15)->withQueryString();}
    public function locations(){return StockLocation::query()->orderBy('name')->get(['id','code','name','is_active']);}
    public function create(array $data):StockTransfer{return DB::transaction(function()use($data):StockTransfer{$data['transfer_number']=$this->documentNumbers->next('TRF','stock_transfers','transfer_number');$data['status']='draft';$data['created_by']=auth()->id();return StockTransfer::create($data);});}
    public function update(StockTransfer $transfer,array $data):StockTransfer{$transfer->refresh();if($transfer->status!=='draft')throw ValidationException::withMessages(['status'=>'Only draft transfers can be edited.']);unset($data['transfer_number']);$transfer->update($data);return $transfer->refresh();}
    public function post(StockTransfer $transfer):StockTransfer{return $this->inventory->postStockTransfer($transfer);}
    public function delete(StockTransfer $transfer):bool{return DB::transaction(function()use($transfer):bool{$locked=StockTransfer::query()->whereKey($transfer->id)->lockForUpdate()->firstOrFail();if($locked->status!=='draft')return false;return (bool)$locked->delete();});}
}
