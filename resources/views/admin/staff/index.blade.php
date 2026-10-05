@php
$formKey = old('_form');
$reopen = $formKey && $errors->any();
$editId = ($reopen && str_starts_with($formKey, 'edit-')) ? (int) substr($formKey, 5) : null;
$dayOrder = [6 => 'Sat', 0 => 'Sun', 1 => 'Mon', 2 => 'Tue', 3 => 'Wed', 4 => 'Thu', 5 => 'Fri'];
$activeCount = $staff->where('is_active', true)->count();
$inactiveCount = $staff->count() - $activeCount;
$headers = [
'bg-gradient-to-br from-teal-700 to-teal-500',
'bg-gradient-to-br from-teal-800 to-teal-600',
'bg-gradient-to-br from-teal-700 to-emerald-500',
];
@endphp

<x-admin-layout title="Staff">
    <div
        x-data="{
            filter: 'all',
            errorKey: @js($reopen ? $formKey : null),
            form: {
                open: @js($reopen),
                editing: @js($editId !== null),
                action: @js($editId ? route('admin.staff.update', $editId) : route('admin.staff.store')),
                key: @js($reopen ? $formKey : 'create'),
                name: @js(old('name', '')),
                email: @js(old('email', '')),
                phone: @js(old('phone', '')),
            },
            confirm: { open: false, url: '', name: '', activate: false, upcoming: 0 },
            submitting: false,
            openCreate() {
                this.submitting = false;
                this.form = { open: true, editing: false, action: @js(route('admin.staff.store')), key: 'create', name: '', email: '', phone: '' };
            },
            openEdit(s) {
                this.submitting = false;
                this.form = { open: true, editing: true, action: s.url, key: 'edit-' + s.id, name: s.name, email: s.email ?? '', phone: s.phone ?? '' };
            },
            askToggle(s) {
                this.submitting = false;
                this.confirm = { open: true, url: s.url, name: s.name, activate: s.activate, upcoming: s.upcoming };
            }
        }"
        @keydown.escape.window="form.open = false; confirm.open = false">

        @if (session('status'))
        <div class="mb-4 text-sm font-medium text-slate-800 bg-white border border-slate-300 border-l-4 border-l-teal-500 rounded-lg px-4 py-2.5 shadow-sm">
            {{ session('status') }}
        </div>
        @endif

        {{-- Top bar --}}
        <div class="flex flex-wrap items-center justify-between gap-4 mb-5">

            {{-- Filter tabs --}}
            <div class="inline-flex p-1 rounded-xl bg-slate-200 text-sm font-medium">
                <button type="button" @click="filter = 'all'"
                    :class="filter === 'all' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                    class="px-3.5 py-1.5 rounded-lg transition">
                    All <span class="ml-1 text-slate-500">{{ $staff->count() }}</span>
                </button>
                <button type="button" @click="filter = 'active'"
                    :class="filter === 'active' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                    class="px-3.5 py-1.5 rounded-lg transition">
                    Active <span class="ml-1 text-slate-500">{{ $activeCount }}</span>
                </button>
                <button type="button" @click="filter = 'inactive'"
                    :class="filter === 'inactive' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                    class="px-3.5 py-1.5 rounded-lg transition">
                    Inactive <span class="ml-1 text-slate-500">{{ $inactiveCount }}</span>
                </button>
            </div>

            <button type="button" @click="openCreate()"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-teal-600 text-white text-sm font-medium hover:bg-teal-700 shadow-sm transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14" />
                </svg>
                Add staff member
            </button>
        </div>

        @if ($staff->isEmpty())
        <div class="bg-white border border-slate-300 shadow-sm rounded-2xl p-10 text-center">
            <div class="w-12 h-12 mx-auto rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center mb-4">
                <x-admin-nav-icon name="users" class="w-6 h-6" />
            </div>
            <h3 class="text-base font-semibold text-slate-900">No staff yet</h3>
            <p class="mt-1 text-sm text-slate-600">Add your first stylist so clients can start booking.</p>
        </div>
        @else

        {{-- Soft grey panel behind the cards --}}
        <div class="rounded-3xl bg-slate-200/70 border border-slate-200 p-3 sm:p-5">
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 sm:gap-5">
                @foreach ($staff as $member)
                @php
                $workDays = $member->staffSchedules->pluck('day_of_week')->all();
                $hours = $member->staffSchedules
                ->map(fn ($s) => substr($s->start_time, 0, 5) . ' – ' . substr($s->end_time, 0, 5))
                ->unique()->values();
                $hoursText = $hours->count() === 0 ? null : ($hours->count() === 1 ? $hours[0] : 'Hours vary by day');
                $headerClass = $member->is_active
                ? $headers[$loop->index % count($headers)]
                : 'bg-gradient-to-br from-slate-500 to-slate-400';
                @endphp

                <div x-show="filter === 'all' || filter === '{{ $member->is_active ? 'active' : 'inactive' }}'"
                    class="flex flex-col bg-white rounded-2xl border border-slate-300 shadow-sm overflow-hidden hover:shadow-lg hover:-translate-y-0.5 transition">

                    {{-- Green header (the one big green area) --}}
                    <div class="relative overflow-hidden px-5 py-5 {{ $headerClass }}">
                        <div class="absolute -right-6 -top-6 w-28 h-28 rounded-full bg-white/10"></div>
                        <div class="absolute right-10 -bottom-10 w-24 h-24 rounded-full bg-white/10"></div>

                        <div class="relative flex items-center gap-3.5">
                            <div class="w-14 h-14 rounded-full bg-white/20 ring-2 ring-white/60 text-white flex items-center justify-center text-xl font-semibold shrink-0"
                                style="font-family: 'Fraunces', serif;">
                                {{ strtoupper(substr($member->name, 0, 1)) }}
                            </div>

                            <div class="min-w-0 flex-1">
                                <h3 class="text-lg font-semibold text-white truncate">{{ $member->name }}</h3>
                                <span class="mt-1 inline-flex items-center gap-1.5 rounded-full bg-white/20 px-2.5 py-0.5 text-xs font-medium text-white">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $member->is_active ? 'bg-teal-200' : 'bg-slate-200' }}"></span>
                                    {{ $member->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="p-5 space-y-4">

                        {{-- Contact --}}
                        <div class="space-y-2 text-sm">
                            <div class="flex items-center gap-2.5 text-slate-700 min-w-0">
                                <span class="w-7 h-7 rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <rect x="3" y="5" width="18" height="14" rx="2" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 7l9 6 9-6" />
                                    </svg>
                                </span>
                                <span class="truncate">{{ $member->email }}</span>
                            </div>
                            <div class="flex items-center gap-2.5 min-w-0">
                                <span class="w-7 h-7 rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 4h4l2 5-2.5 1.5a11 11 0 005 5L15 13l5 2v4a2 2 0 01-2 2A16 16 0 013 6a2 2 0 012-2z" />
                                    </svg>
                                </span>
                                @if ($member->phone)
                                <a href="tel:{{ $member->phone }}" class="font-medium text-teal-700 hover:text-teal-900">{{ $member->phone }}</a>
                                @else
                                <span class="text-slate-500">No phone</span>
                                @endif
                            </div>
                        </div>

                        {{-- Stats --}}
                        <div class="grid grid-cols-2 gap-3">
                            <div class="rounded-xl bg-slate-50 border border-slate-200 px-3.5 py-2.5">
                                <p class="text-xs font-medium text-slate-600">Today</p>
                                <p class="text-2xl font-semibold text-teal-700">{{ $member->today_count }}</p>
                            </div>
                            <div class="rounded-xl bg-slate-100 border border-slate-200 px-3.5 py-2.5">
                                <p class="text-xs font-medium text-slate-600">Upcoming</p>
                                <p class="text-2xl font-semibold text-slate-900">{{ $member->upcoming_count }}</p>
                            </div>
                        </div>

                        {{-- Working days --}}
                        <div>
                            <div class="flex gap-1">
                                @foreach ($dayOrder as $num => $label)
                                <span class="flex-1 text-center text-[11px] font-semibold py-1.5 rounded-md border
                                    {{ in_array($num, $workDays)
                                        ? 'bg-teal-600 text-white border-teal-600'
                                        : 'bg-white text-slate-400 border-dashed border-slate-300' }}">
                                    {{ $label }}
                                </span>
                                @endforeach
                            </div>

                            <div class="mt-3 flex items-center gap-2 rounded-lg bg-slate-100 px-3 py-2 text-sm text-slate-800">
                                <svg class="w-4 h-4 shrink-0 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <circle cx="12" cy="12" r="9" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 7v5l3 3" />
                                </svg>
                                @if ($hoursText)
                                <span class="font-medium">{{ $hoursText }}</span>
                                @else
                                <span>No schedule yet ·
                                    <a href="{{ route('admin.schedules.index') }}" class="font-medium text-teal-700 hover:text-teal-900 underline decoration-teal-300">set one</a>
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="mt-auto px-5 py-3 bg-slate-50 border-t border-slate-200 flex flex-wrap items-center gap-2">
                        <button type="button"
                            @click="openEdit(@js(['id' => $member->id, 'name' => $member->name, 'email' => $member->email, 'phone' => $member->phone, 'url' => route('admin.staff.update', $member)]))"
                            class="text-sm font-medium px-3 py-1.5 rounded-lg bg-white border border-slate-300 text-slate-800 hover:bg-slate-100 transition">
                            Edit
                        </button>

                        <a href="{{ route('admin.schedules.index') }}"
                            class="text-sm font-medium px-3 py-1.5 rounded-lg text-slate-700 hover:bg-slate-200 transition">
                            Schedule
                        </a>

                        <button type="button"
                            @click="askToggle(@js(['url' => route('admin.staff.toggle', $member), 'name' => $member->name, 'activate' => ! $member->is_active, 'upcoming' => $member->upcoming_count]))"
                            class="ml-auto text-sm font-medium px-3 py-1.5 rounded-lg transition
                                {{ $member->is_active ? 'text-red-600 hover:bg-red-50' : 'bg-slate-900 text-white hover:bg-slate-800' }}">
                            {{ $member->is_active ? 'Deactivate' : 'Activate' }}
                        </button>
                    </div>
                </div>
                @endforeach
            </div>

            <p x-cloak
                x-show="(filter === 'active' && {{ $activeCount }} === 0) || (filter === 'inactive' && {{ $inactiveCount }} === 0)"
                class="text-center text-sm font-medium text-slate-700 py-10">
                Nobody here.
            </p>
        </div>
        @endif

        {{-- Add / Edit modal --}}
        <div x-show="form.open" x-cloak x-transition.opacity
            class="fixed inset-0 z-[100] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="form.open = false"></div>

            <div x-show="form.open" x-transition @click.stop
                class="relative w-full max-w-md max-h-[90vh] overflow-y-auto bg-white rounded-2xl shadow-xl border border-slate-200 p-6">

                <h2 class="text-base font-semibold text-slate-900"
                    x-text="form.editing ? 'Edit staff member' : 'Add staff member'"></h2>
                <p class="mt-1 text-sm text-slate-600"
                    x-text="form.editing ? 'Update their details. Leave the password empty to keep it.' : 'They will log in with this email and password.'"></p>

                <form method="POST" :action="form.action" @submit="submitting = true" class="mt-5 space-y-4">
                    @csrf
                    <input type="hidden" name="_method" value="PUT" :disabled="!form.editing">
                    <input type="hidden" name="_form" :value="form.key">

                    <div>
                        <label for="s-name" class="block text-sm font-medium text-slate-800 mb-1">Full name</label>
                        <input id="s-name" type="text" name="name" x-model="form.name" required
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 focus:border-teal-500 focus:ring-teal-500/20">
                        @error('name')<p x-show="form.key === errorKey" class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="s-email" class="block text-sm font-medium text-slate-800 mb-1">Email</label>
                        <input id="s-email" type="email" name="email" x-model="form.email" required
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 focus:border-teal-500 focus:ring-teal-500/20">
                        @error('email')<p x-show="form.key === errorKey" class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="s-phone" class="block text-sm font-medium text-slate-800 mb-1">Phone <span class="text-slate-500 font-normal">(optional)</span></label>
                        <input id="s-phone" type="tel" name="phone" x-model="form.phone" placeholder="0550123456"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 focus:border-teal-500 focus:ring-teal-500/20">
                        @error('phone')<p x-show="form.key === errorKey" class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="s-password" class="block text-sm font-medium text-slate-800 mb-1">
                            Password <span x-show="form.editing" class="text-slate-500 font-normal">(optional)</span>
                        </label>
                        <input id="s-password" type="password" name="password" autocomplete="new-password"
                            :required="!form.editing" minlength="8"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 focus:border-teal-500 focus:ring-teal-500/20">
                        @error('password')<p x-show="form.key === errorKey" class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="pt-2 flex justify-end gap-3">
                        <button type="button" @click="form.open = false"
                            class="px-4 py-2 text-sm font-medium text-slate-700 hover:text-slate-900 hover:bg-slate-50 rounded-lg transition">
                            Cancel
                        </button>
                        <button type="submit" :disabled="submitting"
                            class="px-4 py-2 text-sm font-medium text-white bg-teal-600 hover:bg-teal-700 rounded-lg transition disabled:opacity-60"
                            x-text="form.editing ? 'Save changes' : 'Add member'"></button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Activate / deactivate confirmation --}}
        <div x-show="confirm.open" x-cloak x-transition.opacity
            class="fixed inset-0 z-[100] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="confirm.open = false"></div>

            <div x-show="confirm.open" x-transition @click.stop
                class="relative w-full max-w-sm bg-white rounded-2xl shadow-xl border border-slate-200 p-6">

                <h2 class="text-base font-semibold text-slate-900"
                    x-text="confirm.activate ? 'Activate ' + confirm.name + '?' : 'Deactivate ' + confirm.name + '?'"></h2>

                <p class="mt-2 text-sm text-slate-700"
                    x-text="confirm.activate
                        ? 'They will appear for booking again and can log in.'
                        : 'They will no longer appear for booking and cannot log in. Their history is kept.'"></p>

                <p x-show="!confirm.activate && confirm.upcoming > 0"
                    class="mt-3 rounded-lg bg-slate-100 px-3 py-2 text-sm font-medium text-slate-800"
                    x-text="confirm.upcoming + ' upcoming confirmed appointment(s) will stay on the calendar.'"></p>

                <form method="POST" :action="confirm.url" @submit="submitting = true" class="mt-6 flex justify-end gap-3">
                    @csrf
                    @method('PATCH')

                    <button type="button" @click="confirm.open = false"
                        class="px-4 py-2 text-sm font-medium text-slate-700 hover:text-slate-900 hover:bg-slate-50 rounded-lg transition">
                        Go back
                    </button>
                    <button type="submit" :disabled="submitting"
                        class="px-4 py-2 text-sm font-medium text-white rounded-lg transition disabled:opacity-60"
                        :class="confirm.activate ? 'bg-slate-900 hover:bg-slate-800' : 'bg-red-600 hover:bg-red-700'"
                        x-text="confirm.activate ? 'Yes, activate' : 'Yes, deactivate'"></button>
                </form>
            </div>
        </div>
    </div>
</x-admin-layout>