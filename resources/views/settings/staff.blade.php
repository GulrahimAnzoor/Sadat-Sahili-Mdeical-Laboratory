<x-layout :title="__('Staff and roles')">
    <x-page-header :description="__('Choose a role. Email and password are created automatically for login.')">
        <x-slot:actions>
            <x-settings-back />
        </x-slot:actions>
    </x-page-header>

    @if (session('generated_login'))
        <section class="mb-6 overflow-hidden rounded-2xl border border-teal-200 bg-gradient-to-br from-teal-50 to-sky-50 shadow-sm dark:border-teal-800 dark:from-teal-950/40 dark:to-sky-950/30">
            <div class="border-b border-teal-200/70 px-5 py-3 dark:border-teal-800">
                <p class="text-sm font-semibold text-teal-900 dark:text-teal-100">{{ __('Login created. Copy these details now.') }}</p>
                <p class="text-xs text-teal-700 dark:text-teal-300">{{ __('The password is shown only this once.') }}</p>
            </div>
            <dl class="grid gap-3 p-5 sm:grid-cols-3">
                <div>
                    <dt class="text-xs text-slate-500">{{ __('Name') }}</dt>
                    <dd class="mt-1 font-medium text-slate-900 dark:text-white">{{ session('generated_login.name') }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-slate-500">{{ __('Email') }}</dt>
                    <dd class="mt-1 flex items-center gap-2 font-medium text-slate-900 dark:text-white">
                        <span>{{ session('generated_login.email') }}</span>
                        <button type="button" data-copy="{{ session('generated_login.email') }}" class="rounded-lg px-2 py-1 text-xs font-semibold text-teal-700 hover:bg-white dark:text-teal-300">{{ __('Copy') }}</button>
                    </dd>
                </div>
                <div>
                    <dt class="text-xs text-slate-500">{{ __('Password') }}</dt>
                    <dd class="mt-1 flex items-center gap-2 font-medium text-slate-900 dark:text-white">
                        <span>{{ session('generated_login.password') }}</span>
                        <button type="button" data-copy="{{ session('generated_login.password') }}" class="rounded-lg px-2 py-1 text-xs font-semibold text-teal-700 hover:bg-white dark:text-teal-300">{{ __('Copy') }}</button>
                    </dd>
                </div>
            </dl>
        </section>
    @endif

    <div class="grid gap-6 xl:grid-cols-5">
        <x-panel class="xl:col-span-2" :title="__('Roles')">
            <x-slot:subtitle>{{ __('Select every laboratory page this role may use, and whether it may edit or delete records.') }}</x-slot:subtitle>
            <ul class="divide-y divide-slate-100 dark:divide-slate-800">
                @foreach ($roles as $role)
                    <li class="px-5 py-3">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="font-medium text-slate-900 dark:text-white">{{ $role->name }}</p>
                                <p class="text-xs text-slate-500">{{ $role->description }}</p>
                                <p class="mt-1 text-xs text-teal-700 dark:text-teal-300">{{ $role->staff_count }} {{ __('Staff') }}</p>
                            </div>
                            @can('records.delete')
                                <form method="POST" action="{{ route('settings.roles.destroy', $role) }}">
                                    @csrf
                                    @method('DELETE')
                                    <x-btn type="submit" size="sm" variant="danger" icon="trash">{{ __('Delete') }}</x-btn>
                                </form>
                            @endcan
                        </div>
                        <details class="mt-3">
                            <summary class="cursor-pointer text-xs font-semibold text-sky-700 dark:text-sky-300">{{ __('Permissions') }}</summary>
                            <form method="POST" action="{{ route('settings.roles.update', $role) }}" class="mt-3 grid gap-3">
                                @csrf
                                @method('PUT')
                                <x-permission-checklist :groups="$permissionGroups" :selected="old('permissions', $role->permissions ?? [])" />
                                <x-btn type="submit" size="sm" icon="save">{{ __('Save permissions') }}</x-btn>
                            </form>
                        </details>
                    </li>
                @endforeach
            </ul>
            <form method="POST" action="{{ route('settings.roles.store') }}" class="grid gap-3 border-t border-slate-100 p-5 dark:border-slate-800">
                @csrf
                <div>
                    <label for="role_name" class="mb-1 block text-sm">{{ __('Role name') }}</label>
                    <input id="role_name" name="name" value="{{ old('name') }}" required class="lab-input">
                    @error('name')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="role_description" class="mb-1 block text-sm">{{ __('Description') }}</label>
                    <input id="role_description" name="description" value="{{ old('description') }}" class="lab-input">
                </div>
                <p class="text-sm font-medium text-slate-700 dark:text-slate-200">{{ __('Permissions') }}</p>
                <x-permission-checklist :groups="$permissionGroups" :selected="old('permissions', [])" />
                <x-btn type="submit" icon="plus">{{ __('Add role') }}</x-btn>
            </form>
        </x-panel>

        <div class="space-y-6 xl:col-span-3">
            <x-panel :title="__('New staff member')">
                <form method="POST" action="{{ route('settings.staff.store') }}" class="grid gap-4 p-5 sm:grid-cols-2">
                    @csrf
                    <div>
                        <label for="staff_name" class="mb-1 block text-sm">{{ __('Name') }}</label>
                        <input id="staff_name" name="name" value="{{ old('name') }}" required class="lab-input">
                        @error('name')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="staff_phone" class="mb-1 block text-sm">{{ __('Phone') }}</label>
                        <input id="staff_phone" name="phone" value="{{ old('phone') }}" class="lab-input">
                    </div>
                    <div>
                        <label for="staff_role_id" class="mb-1 block text-sm">{{ __('Role') }}</label>
                        <select id="staff_role_id" name="role_id" required class="lab-input">
                            <option value="">{{ __('Select') }}</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role->id }}" @selected((string) old('role_id') === (string) $role->id)>{{ $role->name }}</option>
                            @endforeach
                        </select>
                        @error('role_id')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="staff_account_id" class="mb-1 block text-sm">{{ __('Cash account') }}</label>
                        <select id="staff_account_id" name="account_id" class="lab-input">
                            <option value="">{{ __('None') }}</option>
                            @foreach ($accounts as $account)
                                <option value="{{ $account->id }}" @selected((string) old('account_id') === (string) $account->id)>{{ $account->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sm:col-span-2 rounded-2xl border border-dashed border-teal-200 bg-teal-50/70 p-4 text-sm dark:border-teal-800 dark:bg-teal-950/30">
                        <p class="font-medium text-teal-900 dark:text-teal-100">{{ __('Automatic login') }}</p>
                        <p class="mt-1 text-teal-800 dark:text-teal-200">
                            {{ __('Email') }}:
                            <span id="staff-email-preview" class="font-semibold">staff@{{ $loginDomain }}</span>
                        </p>
                        <p class="mt-1 text-xs text-teal-700 dark:text-teal-300">{{ __('The password is created when you save.') }}</p>
                        <ul id="staff-role-sections" class="mt-3 hidden flex-wrap gap-1.5"></ul>
                    </div>
                    <div class="sm:col-span-2">
                        <x-btn type="submit" icon="save">{{ __('Save staff') }}</x-btn>
                    </div>
                </form>
            </x-panel>

            <x-panel :title="__('Staff')">
                @error('staff')
                    <p class="px-5 pt-4 text-sm text-red-700">{{ $message }}</p>
                @enderror
                @if ($staff->isEmpty())
                    <p class="px-5 py-10 text-center text-sm text-slate-500">{{ __('No staff members have been registered.') }}</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-slate-50 text-slate-500 dark:bg-slate-800">
                                <tr>
                                    <th class="px-5 py-3 text-start font-medium">{{ __('Name') }}</th>
                                    <th class="px-5 py-3 text-start font-medium">{{ __('Email') }}</th>
                                    <th class="px-5 py-3 text-start font-medium">{{ __('Role') }}</th>
                                    <th class="px-5 py-3 text-start font-medium">{{ __('Account') }}</th>
                                    <th class="px-5 py-3 text-start font-medium">{{ __('Phone') }}</th>
                                    <th class="px-5 py-3"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                @foreach ($staff as $member)
                                    @php
                                        $isLastAdministrator = $member->user?->is_admin && $administratorCount <= 1;
                                        $isOwnLogin = $member->user_id !== null && $member->user_id === auth()->id();
                                    @endphp
                                    <tr>
                                        <td class="px-5 py-3 font-medium">{{ $member->name }}</td>
                                        <td class="px-5 py-3 text-slate-500">{{ $member->user?->email ?? '—' }}</td>
                                        <td class="px-5 py-3">{{ $member->role?->name ?? '—' }}</td>
                                        <td class="px-5 py-3">{{ $member->account?->name ?? '—' }}</td>
                                        <td class="px-5 py-3 text-slate-500">{{ $member->phone ?? '—' }}</td>
                                        <td class="px-5 py-3">
                                            <div class="flex flex-wrap justify-end gap-2">
                                                @can('records.edit')
                                                    <details class="w-full min-w-64">
                                                        <summary class="cursor-pointer text-end text-xs font-semibold text-sky-700 dark:text-sky-300">{{ __('Edit') }}</summary>
                                                        <form method="POST" action="{{ route('settings.staff.update', $member) }}" class="mt-3 grid gap-3 rounded-2xl border border-slate-100 bg-slate-50/80 p-3 dark:border-slate-800 dark:bg-slate-950/40">
                                                            @csrf
                                                            @method('PUT')
                                                            <div>
                                                                <label class="mb-1 block text-xs text-slate-500" for="staff_name_{{ $member->id }}">{{ __('Name') }}</label>
                                                                <input id="staff_name_{{ $member->id }}" name="name" value="{{ old('name', $member->name) }}" required class="lab-input">
                                                            </div>
                                                            <div>
                                                                <label class="mb-1 block text-xs text-slate-500" for="staff_email_{{ $member->id }}">{{ __('Email') }}</label>
                                                                <input id="staff_email_{{ $member->id }}" name="email" type="email" value="{{ old('email', $member->user?->email) }}" class="lab-input">
                                                            </div>
                                                            <div>
                                                                <label class="mb-1 block text-xs text-slate-500" for="staff_phone_{{ $member->id }}">{{ __('Phone') }}</label>
                                                                <input id="staff_phone_{{ $member->id }}" name="phone" value="{{ old('phone', $member->phone) }}" class="lab-input">
                                                            </div>
                                                            <div>
                                                                <label class="mb-1 block text-xs text-slate-500" for="staff_role_{{ $member->id }}">{{ __('Role') }}</label>
                                                                <select id="staff_role_{{ $member->id }}" name="role_id" class="lab-input">
                                                                    <option value="">{{ __('Select') }}</option>
                                                                    @foreach ($roles as $role)
                                                                        <option value="{{ $role->id }}" @selected((string) old('role_id', $member->role_id) === (string) $role->id)>{{ $role->name }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <div>
                                                                <label class="mb-1 block text-xs text-slate-500" for="staff_account_{{ $member->id }}">{{ __('Cash account') }}</label>
                                                                <select id="staff_account_{{ $member->id }}" name="account_id" class="lab-input">
                                                                    <option value="">{{ __('None') }}</option>
                                                                    @foreach ($accounts as $account)
                                                                        <option value="{{ $account->id }}" @selected((string) old('account_id', $member->account_id) === (string) $account->id)>{{ $account->name }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <x-btn type="submit" size="sm" icon="save">{{ __('Save') }}</x-btn>
                                                        </form>
                                                    </details>
                                                @endcan
                                                @can('records.delete')
                                                    @unless ($isOwnLogin || $isLastAdministrator)
                                                        <form method="POST" action="{{ route('settings.staff.destroy', $member) }}">
                                                            @csrf
                                                            @method('DELETE')
                                                            <x-btn type="submit" size="sm" variant="danger" icon="trash">{{ __('Delete') }}</x-btn>
                                                        </form>
                                                    @endunless
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </x-panel>
        </div>
    </div>
</x-layout>

@pushOnce('scripts')
<script>
    const loginDomain = {{ Illuminate\Support\Js::from($loginDomain) }};
    const rolePermissionLabels = {{ Illuminate\Support\Js::from($rolePermissionLabels) }};
    const nameInput = document.getElementById('staff_name');
    const roleSelect = document.getElementById('staff_role_id');
    const emailPreview = document.getElementById('staff-email-preview');
    const sectionsList = document.getElementById('staff-role-sections');

    function slugify(value) {
        const slug = value
            .toLowerCase()
            .normalize('NFKD')
            .replace(/[^\w\s-]/g, '')
            .trim()
            .replace(/[\s_]+/g, '-');

        return slug === '' ? 'staff' : slug;
    }

    function updateEmailPreview() {
        if (!emailPreview || !nameInput) {
            return;
        }

        emailPreview.textContent = slugify(nameInput.value) + '@' + loginDomain;
    }

    function updateRoleSections() {
        if (!sectionsList || !roleSelect) {
            return;
        }

        const labels = rolePermissionLabels[roleSelect.value] ?? [];
        sectionsList.innerHTML = '';

        if (labels.length === 0) {
            sectionsList.classList.add('hidden');
            sectionsList.classList.remove('flex');
            return;
        }

        sectionsList.classList.remove('hidden');
        sectionsList.classList.add('flex');
        labels.forEach((label) => {
            const item = document.createElement('li');
            item.className = 'rounded-full bg-white px-2.5 py-1 text-xs font-medium text-teal-800 dark:bg-slate-900 dark:text-teal-200';
            item.textContent = label;
            sectionsList.appendChild(item);
        });
    }

    nameInput?.addEventListener('input', updateEmailPreview);
    roleSelect?.addEventListener('change', updateRoleSections);
    updateEmailPreview();
    updateRoleSections();

    document.addEventListener('click', async (event) => {
        const button = event.target.closest('[data-copy]');
        if (!button) {
            return;
        }

        const value = button.getAttribute('data-copy');
        if (!value || !navigator.clipboard) {
            return;
        }

        await navigator.clipboard.writeText(value);
        window.labFlash?.(@json(__('Copied.')), 'success');
    });
</script>
@endpushOnce
