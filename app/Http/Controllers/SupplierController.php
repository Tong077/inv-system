<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Services\SupplierService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Throwable;

class SupplierController extends Controller
{
    public function __construct(private readonly SupplierService $suppliers) {}

    public function index(Request $request): View|Response
    {
        $search = $request->string('search')->trim()->limit(100)->toString();
        $status = $request->query('status');
        $status = is_string($status) ? $status : null;
        $suppliers = $this->suppliers->getAll($search, $status);
        $data = compact('suppliers', 'search', 'status');

        if ($request->header('X-Live-Search') === 'true') {
            return response()->view('suppliers.partials.results', $data);
        }

        return view('suppliers.index', $data);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->normalizeInput($request);
        $validator = Validator::make($request->all(), $this->rules());

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('openSupplierDrawer', 'create');
        }

        try {
            $this->suppliers->create($validator->validated());
        } catch (Throwable $exception) {
            report($exception);

            return back()->withInput()->with('error', 'The supplier could not be created. Please try again.');
        }

        return to_route('suppliers.index')->with('success', 'Supplier created.');
    }

    public function update(Request $request, Supplier $supplier): RedirectResponse
    {
        $this->normalizeInput($request);
        $validator = Validator::make($request->all(), $this->rules($supplier));

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('openSupplierDrawer', $supplier->id);
        }

        try {
            $this->suppliers->update($supplier, $validator->validated());
        } catch (Throwable $exception) {
            report($exception);

            return back()->withInput()->with('error', 'The supplier could not be updated. Please try again.');
        }

        return to_route('suppliers.index')->with('success', 'Supplier updated.');
    }

    public function destroy(Supplier $supplier): RedirectResponse
    {
        try {
            if (! $this->suppliers->delete($supplier)) {
                return to_route('suppliers.index')->with('warning', 'This supplier has contacts, prices, orders, receipts, or payments and cannot be deleted.');
            }
        } catch (Throwable $exception) {
            report($exception);

            return to_route('suppliers.index')->with('error', 'The supplier could not be deleted. Please try again.');
        }

        return to_route('suppliers.index')->with('success', 'Supplier deleted.');
    }

    /** @return array<string, array<int, mixed>> */
    private function rules(?Supplier $supplier = null): array
    {
        $codeRule = Rule::unique('suppliers', 'code');

        if ($supplier !== null) {
            $codeRule->ignore($supplier);
        }

        return [
            'code' => ['required', 'string', 'max:50', $codeRule],
            'name' => ['required', 'string', 'max:200'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:150'],
            'address' => ['nullable', 'string'],
            'tax_number' => ['nullable', 'string', 'max:100'],
            'payment_terms' => ['nullable', 'string', 'max:100'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    private function normalizeInput(Request $request): void
    {
        foreach (['code', 'name'] as $field) {
            $value = $request->input($field);

            if (is_string($value)) {
                $request->merge([$field => trim($field === 'code' ? strtoupper($value) : $value)]);
            }
        }
    }
}
