@php
    $success = session('success');
    $error = session('error');
    $failed = $success === null && $error === null && $errors->any();
    $message = $success ?? $error ?? ($failed ? __('The action could not be completed.') : null);
    $tone = $success ? 'success' : (($error || $failed) ? 'error' : 'success');
    $title = $tone === 'error' ? __('Error') : __('Success');
    $panelBase = 'pointer-events-auto lab-toast overflow-hidden rounded-2xl border shadow-xl';
    $successPanel = 'border-teal-200/80 bg-white shadow-teal-700/15 dark:border-teal-800 dark:bg-slate-900 dark:shadow-black/40';
    $errorPanel = 'border-rose-200/80 bg-white shadow-rose-700/15 dark:border-rose-800 dark:bg-slate-900 dark:shadow-black/40';
    $successIcon = 'bg-gradient-to-br from-teal-600 to-sky-600';
    $errorIcon = 'bg-gradient-to-br from-rose-600 to-orange-500';
    $successBar = 'bg-gradient-to-r from-teal-600 to-sky-600';
    $errorBar = 'bg-gradient-to-r from-rose-600 to-orange-500';
    $successTitle = 'text-teal-700 dark:text-teal-300';
    $errorTitle = 'text-rose-700 dark:text-rose-300';
    $isError = $tone === 'error';
@endphp

<div
    id="lab-flash"
    class="pointer-events-none fixed top-5 end-5 z-50 w-80 max-w-[calc(100vw-1.5rem)] print:hidden {{ $message ? '' : 'hidden' }}"
    data-tone="{{ $tone }}"
    data-success-title="{{ __('Success') }}"
    data-error-title="{{ __('Error') }}"
    data-panel-base="{{ $panelBase }}"
    data-success-panel="{{ $successPanel }}"
    data-error-panel="{{ $errorPanel }}"
    data-success-icon="{{ $successIcon }}"
    data-error-icon="{{ $errorIcon }}"
    data-success-bar="{{ $successBar }}"
    data-error-bar="{{ $errorBar }}"
    data-success-title-class="{{ $successTitle }}"
    data-error-title-class="{{ $errorTitle }}"
    role="status"
    aria-live="polite"
>
    <div data-lab-flash-panel class="{{ $panelBase }} {{ $isError ? $errorPanel : $successPanel }}">
        <div class="flex items-start gap-3 px-4 py-3.5">
            <span data-lab-flash-icon class="mt-0.5 inline-flex size-10 shrink-0 items-center justify-center rounded-xl text-white shadow-sm {{ $isError ? $errorIcon : $successIcon }}">
                <svg data-lab-flash-success-icon class="size-5 {{ $isError ? 'hidden' : '' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                <svg data-lab-flash-error-icon class="size-5 {{ $isError ? '' : 'hidden' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M12 3.75a8.25 8.25 0 1 0 0 16.5 8.25 8.25 0 0 0 0-16.5Z" /></svg>
            </span>
            <div class="min-w-0 flex-1 pt-0.5">
                <p data-lab-flash-title class="text-[11px] font-semibold tracking-wide uppercase {{ $isError ? $errorTitle : $successTitle }}">{{ $title }}</p>
                <p data-lab-flash-message class="mt-0.5 text-sm font-medium leading-5 text-slate-800 dark:text-slate-100">{{ $message }}</p>
            </div>
            <button type="button" data-lab-flash-close class="rounded-lg p-1 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-slate-800 dark:hover:text-slate-200" aria-label="{{ __('Close') }}">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
            </button>
        </div>
        <div data-lab-flash-bar class="h-1 {{ $isError ? $errorBar : $successBar }}"></div>
    </div>
</div>

@pushOnce('scripts')
<script>
    window.labFlash = function (message, tone) {
        const root = document.getElementById('lab-flash');
        if (!root || !message) {
            return;
        }

        const panel = root.querySelector('[data-lab-flash-panel]');
        const iconWrap = root.querySelector('[data-lab-flash-icon]');
        const bar = root.querySelector('[data-lab-flash-bar]');
        const title = root.querySelector('[data-lab-flash-title]');
        const text = root.querySelector('[data-lab-flash-message]');
        const successIcon = root.querySelector('[data-lab-flash-success-icon]');
        const errorIcon = root.querySelector('[data-lab-flash-error-icon]');
        const isError = tone === 'error';

        root.dataset.tone = isError ? 'error' : 'success';
        if (text) {
            text.textContent = message;
        }
        if (title) {
            title.textContent = isError ? root.dataset.errorTitle : root.dataset.successTitle;
            title.className = 'text-[11px] font-semibold tracking-wide uppercase ' + (isError ? root.dataset.errorTitleClass : root.dataset.successTitleClass);
        }
        if (panel) {
            panel.className = root.dataset.panelBase + ' ' + (isError ? root.dataset.errorPanel : root.dataset.successPanel);
        }
        if (iconWrap) {
            iconWrap.className = 'mt-0.5 inline-flex size-10 shrink-0 items-center justify-center rounded-xl text-white shadow-sm ' + (isError ? root.dataset.errorIcon : root.dataset.successIcon);
        }
        if (bar) {
            bar.className = 'h-1 ' + (isError ? root.dataset.errorBar : root.dataset.successBar);
        }
        successIcon?.classList.toggle('hidden', isError);
        errorIcon?.classList.toggle('hidden', !isError);
        root.classList.remove('hidden');

        if (window.labFlashTimer) {
            clearTimeout(window.labFlashTimer);
        }
        window.labFlashTimer = setTimeout(() => root.classList.add('hidden'), 5000);
    };

    document.addEventListener('click', (event) => {
        const close = event.target.closest('[data-lab-flash-close]');
        if (!close) {
            return;
        }
        document.getElementById('lab-flash')?.classList.add('hidden');
    });

    const initial = document.querySelector('#lab-flash [data-lab-flash-message]');
    if (initial && initial.textContent.trim() !== '') {
        window.labFlashTimer = setTimeout(() => document.getElementById('lab-flash')?.classList.add('hidden'), 5000);
    }
</script>
@endpushOnce
