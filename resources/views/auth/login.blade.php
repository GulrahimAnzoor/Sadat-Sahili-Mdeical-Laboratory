<x-guest-layout :title="__('Log in')">
    <x-auth-split>
        <p class="text-3xl font-semibold tracking-tight text-slate-900 dark:text-white">{{ __('Welcome back') }}</p>
        <p class="mt-1 text-sm text-slate-500">{{ __('Sign in to your account to continue') }}</p>

        <form method="POST" action="{{ route('login.store') }}" class="mt-8 grid gap-5">
            @csrf
            <div>
                <label for="email" class="mb-1.5 block text-sm font-medium text-slate-600 dark:text-slate-300">{{ __('Email') }}</label>
                <div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 start-0 flex w-11 items-center justify-center text-slate-400">
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 20.12a7.5 7.5 0 0 1 15 0" /></svg>
                    </span>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="username@gmail.com" class="auth-input">
                </div>
                @error('email')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="password" class="mb-1.5 block text-sm font-medium text-slate-600 dark:text-slate-300">{{ __('Password') }}</label>
                <x-auth-password />
            </div>

            <div class="flex items-center justify-between gap-3">
                <label class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
                    <input type="checkbox" name="remember" value="1" class="size-4 rounded border-slate-300 text-blue-600 focus:ring-blue-600/30">
                    {{ __('Remember me') }}
                </label>
            </div>

            <button type="submit" class="auth-submit">
                {{ __('Sign in to Dashboard') }}
                <svg class="size-4 rtl:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12l-7.5 7.5M21 12H3" /></svg>
            </button>
        </form>

        @if ($canRegister)
            <p class="mt-6 text-center text-sm text-slate-500">
                {{ __('Don\'t have an account yet?') }}
                <a href="{{ route('register') }}" class="font-semibold text-orange-700 hover:underline dark:text-orange-400">{{ __('Create administrator') }}</a>
                ·
                <a href="{{ route('register') }}" class="font-semibold text-orange-700 hover:underline dark:text-orange-400">{{ __('Sign up') }}</a>
            </p>
        @endif

        <p class="mt-8 text-center text-xs text-slate-400">
            {{ __('For any issues in the system, contact:') }}
            <a href="{{ config('lab.builder.whatsapp') }}" class="mt-1 inline-flex items-center gap-1 font-medium text-blue-700 dark:text-blue-300">
                <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" /></svg>
                {{ config('lab.builder.phone') }}
            </a>
        </p>
    </x-auth-split>
</x-guest-layout>
