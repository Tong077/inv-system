<?php
namespace App\Http\Controllers;
use App\Models\StockWasteItem;
use App\Services\StockWasteItemService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;
class StockWasteItemController extends Controller
{
    public function __construct(private readonly StockWasteItemService $service)
    {
    }
    public function index(Request $r): View|Response
    {
        $s = $r->string('search')->trim()->limit(100)->toString();
        $id = $r->integer('stock_waste_id') ?: null;
        $d = [...$this->service->options(), 'items' => $this->service->getAll($s, $id), 'search' => $s, 'wasteId' => $id];
        return $r->header('X-Live-Search') === 'true' ? response()->view('stock-waste-items.partials.results', $d) : view('stock-waste-items.index', $d);
    }
    public function store(Request $r): RedirectResponse
    {
        $v = Validator::make($r->all(), $this->rules());
        if ($v->fails())
            return back()->withErrors($v)->withInput()->with('openStockWasteItemDrawer', 'create');
        try {
            $this->service->create($v->validated());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput()->with('openStockWasteItemDrawer', 'create');
        } catch (Throwable $e) {
            report($e);
            return back()->withInput()->with('error', 'Waste line could not be added.');
        }
        return to_route('stock-waste-items.index', $r->only('stock_waste_id'))->with('success', 'Waste line added.');
    }
    public function update(Request $r, StockWasteItem $stockWasteItem): RedirectResponse
    {
        $v = Validator::make($r->all(), $this->rules());
        if ($v->fails())
            return back()->withErrors($v)->withInput()->with('openStockWasteItemDrawer', $stockWasteItem->id);
        try {
            $this->service->update($stockWasteItem, $v->validated());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput()->with('openStockWasteItemDrawer', $stockWasteItem->id);
        } catch (Throwable $e) {
            report($e);
            return back()->withInput()->with('error', 'Waste line could not be updated.');
        }
        return to_route('stock-waste-items.index', $r->only('stock_waste_id'))->with('success', 'Waste line updated.');
    }
    public function destroy(Request $r, StockWasteItem $stockWasteItem): RedirectResponse
    {
        try {
            if (!$this->service->delete($stockWasteItem))
                return back()->with('warning', 'Only lines on draft waste records can be deleted.');
        } catch (Throwable $e) {
            report($e);
            return back()->with('error', 'Waste line could not be deleted.');
        }
        return to_route('stock-waste-items.index', $r->only('stock_waste_id'))->with('success', 'Waste line deleted.');
    }
    private function rules(): array
    {
        return ['stock_waste_id' => ['required', 'integer', Rule::exists('stock_wastes', 'id')->where('status', 'draft')], 'ingredient_id' => ['required', 'integer', 'exists:ingredients,id'], 'quantity' => ['required', 'numeric', 'gt:0', 'max:999999999999.999', 'decimal:0,3'], 'unit_id' => ['required', 'integer', 'exists:units,id'], 'notes' => ['nullable', 'string']];
    }
}
