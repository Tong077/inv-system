<?php

namespace App\Http\Controllers;

use App\Models\StockCount;
use App\Services\StockCountService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Throwable;

class StockCountController extends Controller
{
    public function __construct(private readonly StockCountService $service) {}
    public function index(Request $request): View|Response
    {
        $search = $request->string('search')->trim()->limit(100)->toString(); $status = $request->query('status');
        $data = ['counts' => $this->service->getAll($search, is_string($status) ? $status : null), 'locations' => $this->service->locations(), 'search' => $search, 'status' => $status];
        return $request->header('X-Live-Search') === 'true' ? response()->view('stock-counts.partials.results', $data) : view('stock-counts.index', $data);
    }
    public function store(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), $this->rules());
        if ($validator->fails()) return back()->withErrors($validator)->withInput()->with('openStockCountDrawer', 'create');
        try { $this->service->create($validator->validated()); }
        catch (Throwable $exception) { report($exception); return back()->withInput()->with('error', 'The stock count could not be created.'); }
        return to_route('stock-counts.index')->with('success', 'Stock count draft created.');
    }
    public function update(Request $request, StockCount $stockCount): RedirectResponse
    {
        $validator = Validator::make($request->all(), $this->rules());
        if ($validator->fails()) return back()->withErrors($validator)->withInput()->with('openStockCountDrawer', $stockCount->id);
        try { $this->service->update($stockCount, $validator->validated()); }
        catch (ValidationException $exception) { return back()->withErrors($exception->errors())->withInput()->with('openStockCountDrawer', $stockCount->id); }
        catch (Throwable $exception) { report($exception); return back()->withInput()->with('error', 'The stock count could not be updated.'); }
        return to_route('stock-counts.index')->with('success', 'Stock count updated.');
    }
    public function post(StockCount $stockCount): RedirectResponse
    {
        try { $this->service->post($stockCount); }
        catch (ValidationException $exception) { return back()->withErrors($exception->errors())->with('error', 'The count could not be posted. Check its lines.'); }
        catch (Throwable $exception) { report($exception); return back()->with('error', 'The count could not be posted.'); }
        return to_route('stock-counts.index')->with('success', 'Stock count posted and balances reconciled.');
    }
    public function destroy(StockCount $stockCount): RedirectResponse
    {
        try { if (! $this->service->delete($stockCount)) return back()->with('warning', 'Only draft counts can be deleted.'); }
        catch (Throwable $exception) { report($exception); return back()->with('error', 'The stock count could not be deleted.'); }
        return to_route('stock-counts.index')->with('success', 'Stock count deleted.');
    }
    private function rules(): array
    {
        return ['stock_location_id' => ['required', 'integer', 'exists:stock_locations,id'], 'count_date' => ['required', 'date'], 'notes' => ['nullable', 'string']];
    }
}
