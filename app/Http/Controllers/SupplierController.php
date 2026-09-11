<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSupplierRequest;
use App\Http\Requests\UpdateSupplierRequest;
use App\Models\Supplier;
use App\Support\RecordGuard;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SupplierController extends Controller
{
    public function index(): View
    {
        return view('suppliers.index', [
            'suppliers' => Supplier::query()
                ->withCount('purchases')
                ->latest()
                ->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('suppliers.create');
    }

    public function store(StoreSupplierRequest $request): RedirectResponse
    {
        $supplier = Supplier::query()->create($request->validated());

        return redirect()
            ->route('suppliers.show', $supplier)
            ->with('success', __('Supplier saved successfully.'));
    }

    public function show(Supplier $supplier): View
    {
        $supplier->load(['purchases' => fn ($query) => $query->latest()->limit(12)]);

        return view('suppliers.show', ['supplier' => $supplier]);
    }

    public function edit(Supplier $supplier): View
    {
        return view('suppliers.edit', ['supplier' => $supplier]);
    }

    public function update(UpdateSupplierRequest $request, Supplier $supplier): RedirectResponse
    {
        $supplier->update($request->validated());

        return redirect()
            ->route('suppliers.show', $supplier)
            ->with('success', __('Supplier updated successfully.'));
    }

    public function destroy(Supplier $supplier, RecordGuard $guard): RedirectResponse
    {
        $guard->ensureSupplierCanBeDeleted($supplier);

        try {
            $supplier->delete();
        } catch (QueryException) {
            throw ValidationException::withMessages([
                'supplier' => __('This supplier has purchase records and cannot be deleted.'),
            ]);
        }

        return redirect()
            ->route('suppliers.index')
            ->with('success', __('Supplier deleted.'));
    }
}
