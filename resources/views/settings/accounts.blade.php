<x-layout :title="__('Cash accounts')">
    <x-page-header :description="__('These are named cash tills, not login accounts. Paid visits post to the default till.')">
        <x-slot:actions>
            <x-settings-back />
        </x-slot:actions>
    </x-page-header>

    <div class="grid gap-6 lg:grid-cols-3">
        <x-panel :title="__('New account')">
            <form method="POST" action="{{ route('accounts.store') }}" class="space-y-4 p-5">
                @csrf
                <div>
                    <label for="account_name" class="mb-1 block text-sm">{{ __('Account name') }}</label>
                    <input id="account_name" name="name" value="{{ old('name') }}" required class="lab-input" placeholder="{{ __('Reception, Manager, ...') }}">
                    @error('name')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                </div>
                <x-btn type="submit" icon="plus">{{ __('Add account') }}</x-btn>
            </form>
        </x-panel>

        <x-panel class="lg:col-span-2" :title="__('Accounts')">
            @error('account')
                <p class="px-5 pt-4 text-sm text-red-700">{{ $message }}</p>
            @enderror
            <ul class="divide-y divide-slate-100 dark:divide-slate-800">
                @foreach ($accounts as $account)
                    <li class="px-5 py-4">
                        <form method="POST" action="{{ route('accounts.update', $account) }}" class="flex flex-wrap items-end gap-3">
                            @csrf
                            @method('PUT')
                            <div class="min-w-48 flex-1">
                                <label class="mb-1 block text-xs text-slate-500">{{ __('Name') }}</label>
                                <input name="name" value="{{ old('name', $account->name) }}" required class="lab-input">
                            </div>
                            <label class="flex items-center gap-2 pb-2 text-sm">
                                <input type="checkbox" name="is_default" value="1" @checked($account->is_default) class="rounded border-slate-300 text-teal-700 focus:ring-teal-600">
                                {{ __('Default') }}
                            </label>
                            <p class="pb-2 text-xs text-slate-400">{{ $account->staff_count }} {{ __('staff') }} · {{ $account->cash_transactions_count }} {{ __('Transactions') }}</p>
                            <x-btn type="submit" variant="secondary" icon="save" size="sm">{{ __('Save') }}</x-btn>
                        </form>
                        <form method="POST" action="{{ route('accounts.destroy', $account) }}" class="mt-2">
                            @csrf
                            @method('DELETE')
                            <x-btn type="submit" size="sm" variant="danger" icon="trash">{{ __('Delete') }}</x-btn>
                        </form>
                    </li>
                @endforeach
            </ul>
        </x-panel>
    </div>
</x-layout>
