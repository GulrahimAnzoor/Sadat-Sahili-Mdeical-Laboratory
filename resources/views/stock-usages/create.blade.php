<x-layout :title="__('Lab usage')">
    <x-page-header :description="__('Record laboratory materials used without a patient test. This deducts stock. Cash costs stay in Expenses.')">
        <x-slot:actions>
            <x-btn :href="route('inventory-items.index')" variant="ghost" icon="back">{{ __('Inventory') }}</x-btn>
        </x-slot:actions>
    </x-page-header>

    <form method="POST" action="{{ route('stock-usages.store') }}" class="space-y-6">
        @csrf
        <x-panel :title="__('Usage details')">
            <div class="grid gap-4 p-5 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium">{{ __('Date') }}</label>
                    <input name="used_on" type="date" required class="lab-input" value="{{ old('used_on', now()->toDateString()) }}">
                    @error('used_on')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium">{{ __('Reason / notes') }}</label>
                    <input name="notes" class="lab-input" value="{{ old('notes') }}" placeholder="{{ __('Quality control, waste, cleaning…') }}">
                    @error('notes')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                </div>
            </div>
            @error('items')<p class="px-5 pb-3 text-sm text-red-700">{{ $message }}</p>@enderror
            @error('quantity')<p class="px-5 pb-3 text-sm text-red-700">{{ $message }}</p>@enderror
        </x-panel>

        <x-panel :title="__('Materials used')">
            <x-slot:subtitle>{{ __('Search, select an item, then enter the quantity on each row.') }}</x-slot:subtitle>
            @include('stock._picker', [
                'namePrefix' => 'items',
                'lots' => $lots,
                'rows' => $rows ?? old('items', []),
            ])
        </x-panel>

        <div class="flex justify-end">
            <x-btn type="submit" icon="save">{{ __('Record usage') }}</x-btn>
        </div>
    </form>
</x-layout>
