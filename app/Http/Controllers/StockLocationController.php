<?php

namespace App\Http\Controllers;

use App\Models\StockLocation;
use App\Services\StockLocationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Throwable;

class StockLocationController extends Controller
{
    public function __construct(private readonly StockLocationService $locations) {}

    public function index(Request $request): View|Response
    {
        $search = $request->string('search')->trim()->limit(100)->toString();
        $status = $request->query('status');
        $status = is_string($status) ? $status : null;
        $locations = $this->locations->getAll($search, $status);
        $data = compact('locations', 'search', 'status');

        if ($request->header('X-Live-Search') === 'true') {
            return response()->view('stock-locations.partials.results', $data);
        }

        return view('stock-locations.index', $data);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->normalizeInput($request);
        $validator = Validator::make($request->all(), $this->rules());

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('openStockLocationDrawer', 'create');
        }

        try {
            $this->locations->create($validator->validated());
        } catch (Throwable $exception) {
            report($exception);

            return back()->withInput()->with('error', 'The stock location could not be created. Please try again.');
        }

        return to_route('stock-locations.index')->with('success', 'Stock location created.');
    }

    public function update(Request $request, StockLocation $stockLocation): RedirectResponse
    {
        $this->normalizeInput($request);
        $validator = Validator::make($request->all(), $this->rules($stockLocation));

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('openStockLocationDrawer', $stockLocation->id);
        }

        try {
            $this->locations->update($stockLocation, $validator->validated());
        } catch (Throwable $exception) {
            report($exception);

            return back()->withInput()->with('error', 'The stock location could not be updated. Please try again.');
        }

        return to_route('stock-locations.index')->with('success', 'Stock location updated.');
    }

    public function destroy(StockLocation $stockLocation): RedirectResponse
    {
        try {
            if (! $this->locations->delete($stockLocation)) {
                return to_route('stock-locations.index')->with('warning', 'This location has stock or transaction history and cannot be deleted.');
            }
        } catch (Throwable $exception) {
            report($exception);

            return to_route('stock-locations.index')->with('error', 'The stock location could not be deleted. Please try again.');
        }

        return to_route('stock-locations.index')->with('success', 'Stock location deleted.');
    }

    /** @return array<string, array<int, mixed>> */
    private function rules(?StockLocation $location = null): array
    {
        $codeRule = Rule::unique('stock_locations', 'code');

        if ($location !== null) {
            $codeRule->ignore($location);
        }

        return [
            'code' => ['required', 'string', 'max:50', $codeRule],
            'name' => ['required', 'string', 'max:150'],
            'location_type' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    private function normalizeInput(Request $request): void
    {
        foreach (['code', 'name', 'location_type'] as $field) {
            $value = $request->input($field);

            if (is_string($value)) {
                $request->merge([$field => trim($field === 'code' ? strtoupper($value) : $value)]);
            }
        }
    }
}
