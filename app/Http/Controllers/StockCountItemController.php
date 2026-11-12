<?php
namespace App\Http\Controllers;
use App\Models\StockCountItem;
use App\Services\StockCountItemService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;
class StockCountItemController extends Controller
{
    public function __construct(private readonly StockCountItemService $service)
    {
    }
    public function index(Request $r): View|Response
    {
        $s = $r->string('search')->trim()->limit(100)->toString();
        $id = $r->integer('stock_count_id') ?: null;
        $d = [...$this->service->options(), 'items' => $this->service->getAll($s, $id), 'search' => $s, 'countId' => $id];
        return $r->header('X-Live-Search') === 'true' ? response()->view('stock-count-items.partials.results', $d) : view('stock-count-items.index', $d);
    }
    public function store(Request $r): RedirectResponse
    {
        $v = Validator::make($r->all(), $this->rules());
        if ($v->fails())
            return back()->withErrors($v)->withInput()->with('openStockCountItemDrawer', 'create');
        try {
            $this->service->create($v->validated());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput()->with('openStockCountItemDrawer', 'create');
        } catch (Throwable $e) {
            report($e);
            return back()->withInput()->with('error', 'The count line could not be added.');
        }
        return to_route('stock-count-items.index', $r->only('stock_count_id'))->with('success', 'Count line added.');
    }
    public function update(Request $r, StockCountItem $stockCountItem): RedirectResponse
    {
        $v = Validator::make($r->all(), $this->rules());
        if ($v->fails())
            return back()->withErrors($v)->withInput()->with('openStockCountItemDrawer', $stockCountItem->id);
        try {
            $this->service->update($stockCountItem, $v->validated());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput()->with('openStockCountItemDrawer', $stockCountItem->id);
        } catch (Throwable $e) {
            report($e);
            return back()->withInput()->with('error', 'The count line could not be updated.');
        }
        return to_route('stock-count-items.index', $r->only('stock_count_id'))->with('success', 'Count line updated.');
    }
    public function destroy(Request $r, StockCountItem $stockCountItem): RedirectResponse
    {
        try {
            if (!$this->service->delete($stockCountItem))
                return back()->with('warning', 'Only lines on draft counts can be deleted.');
        } catch (Throwable $e) {
            report($e);
            return back()->with('error', 'The count line could not be deleted.');
        }
        return to_route('stock-count-items.index', $r->only('stock_count_id'))->with('success', 'Count line deleted.');
    }
    private function rules(): array
    {
        return ['stock_count_id' => ['required', 'integer', Rule::exists('stock_counts', 'id')->where('status', 'draft')], 'ingredient_id' => ['required', 'integer', 'exists:ingredients,id'], 'counted_quantity' => ['required', 'numeric', 'min:0', 'max:999999999999.999', 'decimal:0,3']];
    }
}
