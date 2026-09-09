@php($expense = $expense ?? null)
<div>
    <label class="mb-1 block text-sm font-medium">{{ __('Title') }}</label>
    <input name="title" required class="lab-input" value="{{ old('title', $expense?->title ?? '') }}">
    @error('title')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
</div>
<div class="grid gap-4 sm:grid-cols-3">
    <div>
        <label class="mb-1 block text-sm font-medium">{{ __('Category') }}</label>
        <select name="category" class="lab-input">
            @foreach (\App\Enums\ExpenseCategory::cases() as $category)
                <option value="{{ $category->value }}" @selected(old('category', $expense?->category?->value ?? 'other') === $category->value)>{{ $category->label() }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium">{{ __('Amount') }}</label>
        <input name="amount" type="number" step="0.01" required class="lab-input" value="{{ old('amount', $expense?->amount ?? '') }}">
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium">{{ __('Date') }}</label>
        <input name="spent_on" type="date" required class="lab-input" value="{{ old('spent_on', $expense?->spent_on?->toDateString() ?? now()->toDateString()) }}">
    </div>
</div>
<div>
    <label class="mb-1 block text-sm font-medium">{{ __('Notes') }}</label>
    <textarea name="notes" rows="2" class="lab-input">{{ old('notes', $expense?->notes ?? '') }}</textarea>
</div>
