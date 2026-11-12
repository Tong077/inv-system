<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use App\Services\UnitService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class UnitController extends Controller
{
    public function __construct(private readonly UnitService $units) {}

    public function index(Request $request): View|Response
    {
        $search = $request->string('search')->trim()->limit(100)->toString();
        $status = $request->query('status');
        $status = is_string($status) ? $status : null;
        $units = $this->units->getAllUnits($search, $status);

        if ($request->header('X-Live-Search') === 'true') {
            return response()->view('units.partials.results', compact('units', 'search', 'status'));
        }

        return view('units.index', compact('units', 'search', 'status'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->normalizeInput($request);
        $validator = Validator::make($request->all(), $this->rules());

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('openUnitDrawer', 'create');
        }

        try {
            $this->units->createUnit($validator->validated());
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors())->withInput()->with('openUnitDrawer', 'create');
        } catch (Throwable $exception) {
            report($exception);

            return back()->withInput()->with('error', 'The unit could not be created. Please try again.');
        }

        return to_route('units.index')->with('success', 'Unit created.');
    }

    public function update(Request $request, Unit $unit): RedirectResponse
    {
        $this->normalizeInput($request);
        $validator = Validator::make($request->all(), $this->rules($unit));

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('openUnitDrawer', $unit->id);
        }

        try {
            $this->units->updateUnit($unit, $validator->validated());
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors())->withInput()->with('openUnitDrawer', $unit->id);
        } catch (Throwable $exception) {
            report($exception);

            return back()->withInput()->with('error', 'The unit could not be updated. Please try again.');
        }

        return to_route('units.index')->with('success', 'Unit updated.');
    }

    public function destroy(Unit $unit): RedirectResponse
    {
        try {
            if (! $this->units->deleteUnit($unit)) {
                return to_route('units.index')->with('warning', 'This unit is in use and cannot be deleted.');
            }
        } catch (Throwable $exception) {
            report($exception);

            return to_route('units.index')->with('error', 'The unit could not be deleted. Please try again.');
        }

        return to_route('units.index')->with('success', 'Unit deleted.');
    }

    /** @return array<string, array<int, mixed>> */
    private function rules(?Unit $unit = null): array
    {
        $abbreviationRule = Rule::unique('units', 'abbreviation');

        if ($unit !== null) {
            $abbreviationRule->ignore($unit);
        }

        return [
            'name' => ['required', 'string', 'max:100'],
            'abbreviation' => ['required', 'string', 'max:20', $abbreviationRule],
            'dimension' => ['required', 'string', 'max:30'],
            'conversion_factor_to_base' => ['required', 'numeric', 'gt:0', 'max:999999999.999999', 'decimal:0,6'],
            'is_base_unit' => ['required', 'boolean'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    private function normalizeInput(Request $request): void
    {
        foreach (['name', 'abbreviation', 'dimension'] as $field) {
            $value = $request->input($field);

            if (is_string($value)) {
                $request->merge([$field => trim($field === 'dimension' ? strtolower($value) : $value)]);
            }
        }
    }
}
