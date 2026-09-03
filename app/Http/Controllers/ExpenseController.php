<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreExpenseRequest;
use App\Http\Requests\UpdateExpenseRequest;
use App\Models\Expense;
use App\Support\CashLedger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ExpenseController extends Controller
{
    public function index(): View
    {
        return view('expenses.index', [
            'expenses' => Expense::query()
                ->latest('spent_on')
                ->latest('id')
                ->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('expenses.create');
    }

    public function store(StoreExpenseRequest $request, CashLedger $ledger): RedirectResponse
    {
        $expense = DB::transaction(function () use ($request, $ledger): Expense {
            $expense = Expense::query()->create($request->validated());
            $ledger->syncExpense($expense);

            return $expense;
        });

        return redirect()
            ->route('expenses.show', $expense)
            ->with('success', __('Expense saved successfully.'));
    }

    public function show(Expense $expense): View
    {
        return view('expenses.show', ['expense' => $expense]);
    }

    public function edit(Expense $expense): View
    {
        return view('expenses.edit', ['expense' => $expense]);
    }

    public function update(UpdateExpenseRequest $request, Expense $expense, CashLedger $ledger): RedirectResponse
    {
        DB::transaction(function () use ($request, $expense, $ledger): void {
            $expense->update($request->validated());
            $ledger->syncExpense($expense->refresh());
        });

        return redirect()
            ->route('expenses.show', $expense)
            ->with('success', __('Expense updated successfully.'));
    }

    public function destroy(Expense $expense, CashLedger $ledger): RedirectResponse
    {
        DB::transaction(function () use ($expense, $ledger): void {
            $ledger->forgetExpense($expense);
            $expense->delete();
        });

        return redirect()
            ->route('expenses.index')
            ->with('success', __('Expense deleted.'));
    }
}
