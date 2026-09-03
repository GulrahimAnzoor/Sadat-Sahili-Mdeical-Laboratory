<x-layout :title="__('Change password')">
    <x-page-header :description="__('Update the password used to sign in to this laboratory.')">
        <x-slot:actions>
            <x-btn :href="route('dashboard')" variant="ghost" size="sm" icon="back">{{ __('Dashboard') }}</x-btn>
        </x-slot:actions>
    </x-page-header>

    <x-panel :title="__('Change password')" class="max-w-xl">
        <form method="POST" action="{{ route('password.update') }}" class="grid gap-4 p-5">
            @csrf
            @method('PUT')
            <div>
                <label for="current_password" class="mb-1 block text-sm">{{ __('Current password') }}</label>
                <input id="current_password" name="current_password" type="password" required autocomplete="current-password" class="lab-input">
                @error('current_password')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="password" class="mb-1 block text-sm">{{ __('New password') }}</label>
                <input id="password" name="password" type="password" required autocomplete="new-password" class="lab-input">
                @error('password')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="password_confirmation" class="mb-1 block text-sm">{{ __('Confirm password') }}</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" class="lab-input">
            </div>
            <x-btn type="submit" icon="save">{{ __('Save') }}</x-btn>
        </form>
    </x-panel>
</x-layout>
