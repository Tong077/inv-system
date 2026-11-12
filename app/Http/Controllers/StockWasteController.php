<?php
namespace App\Http\Controllers;
use App\Models\StockWaste;
use App\Services\StockWasteService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Throwable;
class StockWasteController extends Controller
{
    public function __construct(private readonly StockWasteService $service)
    {
    }
    public function index(Request $r): View|Response
    {
        $s = $r->string('search')->trim()->limit(100)->toString();
        $st = $r->query('status');
        $d = ['wastes' => $this->service->getAll($s, is_string($st) ? $st : null), 'locations' => $this->service->locations(), 'search' => $s, 'status' => $st];
        return $r->header('X-Live-Search') === 'true' ? response()->view('stock-wastes.partials.results', $d) : view('stock-wastes.index', $d);
    }
    public function store(Request $r): RedirectResponse
    {
        $v = Validator::make($r->all(), $this->rules());
        if ($v->fails())
            return back()->withErrors($v)->withInput()->with('openStockWasteDrawer', 'create');
        try {
            $this->service->create($v->validated());
        } catch (Throwable $e) {
            report($e);
            return back()->withInput()->with('error', 'The waste record could not be created.');
        }
        return to_route('stock-wastes.index')->with('success', 'Waste draft created.');
    }
    public function update(Request $r, StockWaste $stockWaste): RedirectResponse
    {
        $v = Validator::make($r->all(), $this->rules());
        if ($v->fails())
            return back()->withErrors($v)->withInput()->with('openStockWasteDrawer', $stockWaste->id);
        try {
            $this->service->update($stockWaste, $v->validated());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput()->with('openStockWasteDrawer', $stockWaste->id);
        } catch (Throwable $e) {
            report($e);
            return back()->withInput()->with('error', 'The waste record could not be updated.');
        }
        return to_route('stock-wastes.index')->with('success', 'Waste record updated.');
    }
    public function post(StockWaste $stockWaste): RedirectResponse
    {
        try {
            $this->service->post($stockWaste);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->with('error', 'Waste could not be posted; confirm stock and lines.');
        } catch (Throwable $e) {
            report($e);
            return back()->with('error', 'Waste could not be posted.');
        }
        return to_route('stock-wastes.index')->with('success', 'Waste posted and stock reduced.');
    }
    public function destroy(StockWaste $stockWaste): RedirectResponse
    {
        try {
            if (!$this->service->delete($stockWaste))
                return back()->with('warning', 'Only draft waste records can be deleted.');
        } catch (Throwable $e) {
            report($e);
            return back()->with('error', 'Waste record could not be deleted.');
        }
        return to_route('stock-wastes.index')->with('success', 'Waste record deleted.');
    }
    private function rules(): array
    {
        return ['stock_location_id' => ['required', 'integer', 'exists:stock_locations,id'], 'waste_date' => ['required', 'date'], 'reason' => ['required', 'string', 'max:200']];
    }
}
