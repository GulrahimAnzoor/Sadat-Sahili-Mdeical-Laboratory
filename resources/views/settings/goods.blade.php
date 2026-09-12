<x-layout :title="__('Item names')">
    <x-page-header :description="__('These names appear in the inventory Category field.')">
        <x-slot:actions>
            <x-settings-back />
        </x-slot:actions>
    </x-page-header>

    <div class="grid gap-6 lg:grid-cols-3">
        <x-panel :title="__('New item name')">
            <form method="POST" action="{{ route('settings.goods.store') }}" class="space-y-4 p-5">
                @csrf
                <div>
                    <label for="goods_name" class="mb-1 block text-sm">{{ __('Name') }}</label>
                    <input id="goods_name" name="name" value="{{ old('name') }}" required class="lab-input" placeholder="{{ __('Glucose kit, EDTA tube, ...') }}">
                    @error('name')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                </div>
                <x-btn type="submit" icon="plus">{{ __('Add name') }}</x-btn>
            </form>
        </x-panel>

        <x-panel class="lg:col-span-2" :title="__('Item names')">
            @if ($items->isEmpty())
                <p class="px-5 py-10 text-center text-sm text-slate-500">{{ __('No item names yet.') }}</p>
            @else
                <ul class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach ($items as $item)
                        <li class="flex items-center justify-between gap-3 px-5 py-3">
                            <p class="font-medium text-slate-800 dark:text-slate-100">{{ $item->name }}</p>
                            @can('records.delete')
                                <form method="POST" action="{{ route('settings.goods.destroy', $item) }}">
                                    @csrf
                                    @method('DELETE')
                                    <x-btn type="submit" size="sm" variant="danger" icon="trash">{{ __('Delete') }}</x-btn>
                                </form>
                            @endcan
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-panel>
    </div>
</x-layout>
