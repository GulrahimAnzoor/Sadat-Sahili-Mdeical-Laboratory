<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAccountRequest;
use App\Http\Requests\UpdateAccountRequest;
use App\Models\Account;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function index(): View
    {
        return view('settings.accounts', [
            'accounts' => Account::query()
                ->withCount(['cashTransactions', 'staff'])
                ->orderByDesc('is_default')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function store(StoreAccountRequest $request): RedirectResponse
    {
        Account::query()->create([
            'name' => $request->validated('name'),
            'is_default' => Account::query()->doesntExist(),
        ]);

        return back()->with('success', __('Account saved.'));
    }

    public function update(UpdateAccountRequest $request, Account $account): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($account, $validated): void {
            $makeDefault = (bool) ($validated['is_default'] ?? false);

            if (! $makeDefault && $account->is_default && Account::query()->whereKeyNot($account->id)->where('is_default', true)->doesntExist()) {
                $makeDefault = true;
            }

            if ($makeDefault) {
                Account::query()->whereKeyNot($account->id)->update(['is_default' => false]);
            }

            $account->update([
                'name' => $validated['name'],
                'is_default' => $makeDefault,
            ]);
        });

        return redirect()
            ->route('settings.accounts.index')
            ->with('success', __('Account updated.'));
    }

    public function destroy(Account $account): RedirectResponse
    {
        if ($account->is_default && Account::query()->count() === 1) {
            return back()->withErrors(['account' => __('Keep at least one cash account.')]);
        }

        if ($account->is_default) {
            Account::query()
                ->whereKeyNot($account->id)
                ->orderBy('id')
                ->first()
                ?->update(['is_default' => true]);
        }

        $account->delete();

        return redirect()
            ->route('settings.accounts.index')
            ->with('success', __('Account deleted.'));
    }
}
