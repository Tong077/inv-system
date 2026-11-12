<?php

namespace App\Http\Controllers;

use App\Models\StockTransfer;
use App\Services\StockTransferService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Throwable;

class StockTransferController extends Controller
{
    public function __construct(private readonly StockTransferService $service) {}

    public function index(Request $request): View|Response
    {
        $search = $request->string('search')->trim()->limit(100)->toString();
        $status = $request->query('status');
        $data = ['transfers' => $this->service->getAll($search, is_string($status) ? $status : null), 'locations' => $this->service->locations(), 'search' => $search, 'status' => $status];
        return $request->header('X-Live-Search') === 'true' ? response()->view('stock-transfers.partials.results', $data) : view('stock-transfers.index', $data);
    }

    public function store(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), $this->rules());
        if ($validator->fails()) return back()->withErrors($validator)->withInput()->with('openStockTransferDrawer', 'create');
        try { $this->service->create($validator->validated()); }
        catch (Throwable $exception) { report($exception); return back()->withInput()->with('error', 'The transfer could not be created.'); }
        return to_route('stock-transfers.index')->with('success', 'Stock transfer draft created.');
    }

    public function update(Request $request, StockTransfer $stockTransfer): RedirectResponse
    {
        $validator = Validator::make($request->all(), $this->rules());
        if ($validator->fails()) return back()->withErrors($validator)->withInput()->with('openStockTransferDrawer', $stockTransfer->id);
        try { $this->service->update($stockTransfer, $validator->validated()); }
        catch (ValidationException $exception) { return back()->withErrors($exception->errors())->withInput()->with('openStockTransferDrawer', $stockTransfer->id); }
        catch (Throwable $exception) { report($exception); return back()->withInput()->with('error', 'The transfer could not be updated.'); }
        return to_route('stock-transfers.index')->with('success', 'Stock transfer updated.');
    }

    public function post(StockTransfer $stockTransfer): RedirectResponse
    {
        try { $this->service->post($stockTransfer); }
        catch (ValidationException $exception) { return back()->withErrors($exception->errors())->with('error', 'The transfer could not be posted. Check available stock and its lines.'); }
        catch (Throwable $exception) { report($exception); return back()->with('error', 'The transfer could not be posted.'); }
        return to_route('stock-transfers.index')->with('success', 'Transfer posted and stock balances updated.');
    }

    public function destroy(StockTransfer $stockTransfer): RedirectResponse
    {
        try { if (! $this->service->delete($stockTransfer)) return back()->with('warning', 'Only draft transfers can be deleted.'); }
        catch (Throwable $exception) { report($exception); return back()->with('error', 'The transfer could not be deleted.'); }
        return to_route('stock-transfers.index')->with('success', 'Transfer deleted.');
    }

    private function rules(): array
    {
        return ['from_location_id' => ['required', 'integer', 'exists:stock_locations,id'], 'to_location_id' => ['required', 'integer', 'different:from_location_id', 'exists:stock_locations,id'], 'transfer_date' => ['required', 'date'], 'notes' => ['nullable', 'string']];
    }
}
