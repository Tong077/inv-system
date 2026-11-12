<?php

namespace App\Http\Controllers;

use App\Models\SupplierContact;
use App\Services\SupplierContactService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Throwable;

class SupplierContactController extends Controller
{
    public function __construct(private readonly SupplierContactService $contacts) {}

    public function index(Request $request): View|Response
    {
        $search = $request->string('search')->trim()->limit(100)->toString();
        $supplierId = $request->integer('supplier_id');
        $supplierId = $supplierId > 0 ? $supplierId : null;
        $contacts = $this->contacts->getAll($search, $supplierId);
        $suppliers = $this->contacts->getSuppliers();
        $data = compact('contacts', 'suppliers', 'search', 'supplierId');

        if ($request->header('X-Live-Search') === 'true') {
            return response()->view('supplier-contacts.partials.results', $data);
        }

        return view('supplier-contacts.index', $data);
    }

    public function store(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), $this->rules());

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('openSupplierContactDrawer', 'create');
        }

        try {
            $this->contacts->create($validator->validated());
        } catch (Throwable $exception) {
            report($exception);

            return back()->withInput()->with('error', 'The supplier contact could not be created. Please try again.');
        }

        return to_route('supplier-contacts.index')->with('success', 'Supplier contact created.');
    }

    public function update(Request $request, SupplierContact $supplierContact): RedirectResponse
    {
        $validator = Validator::make($request->all(), $this->rules());

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('openSupplierContactDrawer', $supplierContact->id);
        }

        try {
            $this->contacts->update($supplierContact, $validator->validated());
        } catch (Throwable $exception) {
            report($exception);

            return back()->withInput()->with('error', 'The supplier contact could not be updated. Please try again.');
        }

        return to_route('supplier-contacts.index')->with('success', 'Supplier contact updated.');
    }

    public function destroy(SupplierContact $supplierContact): RedirectResponse
    {
        try {
            $this->contacts->delete($supplierContact);
        } catch (Throwable $exception) {
            report($exception);

            return to_route('supplier-contacts.index')->with('error', 'The supplier contact could not be deleted. Please try again.');
        }

        return to_route('supplier-contacts.index')->with('success', 'Supplier contact deleted.');
    }

    /** @return array<string, array<int, mixed>> */
    private function rules(): array
    {
        return [
            'supplier_id' => ['required', 'integer', Rule::exists('suppliers', 'id')->whereNull('deleted_at')],
            'name' => ['required', 'string', 'max:150'],
            'position' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:150'],
        ];
    }
}
