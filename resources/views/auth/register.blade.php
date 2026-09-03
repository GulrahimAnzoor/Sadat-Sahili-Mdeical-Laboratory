<x-guest-layout :title="__('Sign up')">
    <x-auth-split>
        <p class="text-3xl font-semibold tracking-tight text-slate-900 dark:text-white">{{ __('Create administrator') }}</p>
        <p class="mt-1 text-sm text-slate-500">{{ __('The first account becomes the laboratory manager and is stored in the database.') }}</p>

        <form method="POST" action="{{ route('register.store') }}" class="mt-8 grid gap-4">
            @csrf
            <div>
                <label for="name" class="mb-1.5 block text-sm font-medium text-slate-600 dark:text-slate-300">{{ __('Name') }}</label>
                <input id="name" name="name" value="{{ old('name') }}" required autocomplete="name" class="lab-input">
                @error('name')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="email" class="mb-1.5 block text-sm font-medium text-slate-600 dark:text-slate-300">{{ __('Email') }}</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="username" class="lab-input">
                @error('email')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="password" class="mb-1.5 block text-sm font-medium text-slate-600 dark:text-slate-300">{{ __('Password') }}</label>
                <x-auth-password autocomplete="new-password" />
                @error('password')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="password_confirmation" class="mb-1.5 block text-sm font-medium text-slate-600 dark:text-slate-300">{{ __('Confirm password') }}</label>
                <x-auth-password id="password_confirmation" name="password_confirmation" autocomplete="new-password" />
            </div>
            <button type="submit" class="auth-submit">
                {{ __('Sign up') }}
                <svg class="size-4 rtl:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12l-7.5 7.5M21 12H3" /></svg>
            </button>
        </form>

        <p class="mt-6 text-center text-sm text-slate-500">
            {{ __('Already have an account?') }}
            <a href="{{ route('login') }}" class="font-semibold text-orange-700 hover:underline dark:text-orange-400">{{ __('Log in') }}</a>
        </p>
    </x-auth-split>
</x-guest-layout>
