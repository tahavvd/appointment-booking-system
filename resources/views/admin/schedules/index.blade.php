@php
$todayNum = now()->dayOfWeek;
$others = $selected ? $staffList->where('id', '!=', $selected->id) : collect();

// How many active stylists work each day (for the overview footer).
$coverage = [];
if ($staffList->isNotEmpty()) {
foreach ($weeks[$staffList->first()->id] as $i => $day) {
$coverage[$i] = $staffList->where('is_active', true)
->filter(fn ($s) => $weeks[$s->id][$i]['on'])
->count();
}
}
@endphp

<x-admin-layout title="Schedules">

    @if (session('status'))
    <div x-data="{ show: true }" x-show="show" x-transition.opacity
        class="mb-4 flex items-start justify-between gap-3 text-sm font-medium text-slate-800 bg-white border border-slate-300 border-l-4 border-l-teal-500 rounded-lg pl-4 pr-2 py-2.5 shadow-sm">
        <p class="py-0.5">{{ session('status') }}</p>
        <button type="button" @click="show = false" aria-label="Dismiss"
            class="shrink-0 p-1 rounded-md text-slate-500 hover:text-slate-900 hover:bg-slate-100 transition">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
    @endif

    @if (session('warning'))
    <div x-data="{ show: true }" x-show="show" x-transition.opacity
        class="mb-4 flex items-start justify-between gap-3 text-sm font-medium text-slate-800 bg-white border border-slate-300 border-l-4 border-l-red-500 rounded-lg pl-4 pr-2 py-2.5 shadow-sm">
        <p class="py-0.5">{{ session('warning') }}</p>
        <button type="button" @click="show = false" aria-label="Dismiss"
            class="shrink-0 p-1 rounded-md text-slate-500 hover:text-slate-900 hover:bg-slate-100 transition">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
    @endif

    @if ($staffList->isEmpty())
    <div class="bg-white border border-slate-300 shadow-sm rounded-2xl p-10 text-center">
        <div class="w-12 h-12 mx-auto rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center mb-4">
            <x-admin-nav-icon name="calendar" class="w-6 h-6" />
        </div>
        <h2 class="text-base font-semibold text-slate-900">No staff to schedule yet</h2>
        <p class="mt-1 text-sm text-slate-600">Add a stylist first, then set their weekly hours here.</p>
        <a href="{{ route('admin.staff.index') }}"
            class="mt-5 inline-flex px-4 py-2 rounded-lg bg-teal-600 text-white text-sm font-medium hover:bg-teal-700 transition">
            Go to staff
        </a>
    </div>
    @else

    <div
        x-data="{
            days: @js($week),
            original: @js($weeks[$selected->id]),
            weeks: @js($weeks),
            bulk: { start: '09:00', end: '18:00' },
            copyFrom: '',
            submitting: false,
            leave: { open: false, url: '' },

            toMin(t) {
                if (!t) return 0;
                const parts = t.split(':');
                return (+parts[0]) * 60 + (+parts[1]);
            },
            dayMinutes(d) {
                if (!d.on) return 0;
                let m = this.toMin(d.end) - this.toMin(d.start);
                if (d.has_break) m -= Math.max(0, this.toMin(d.break_end) - this.toMin(d.break_start));
                return Math.max(0, m);
            },
            fmt(min) {
                return (Math.round((min / 60) * 10) / 10).toString();
            },
            invalid(d) {
                if (!d.on) return null;
                if (!d.start || !d.end) return 'Set opening and closing times.';
                if (this.toMin(d.end) <= this.toMin(d.start)) return 'Closing must be after opening.';
                if (d.has_break) {
                    if (!d.break_start || !d.break_end) return 'Set the break times.';
                    const bs = this.toMin(d.break_start);
                    const be = this.toMin(d.break_end);
                    if (bs <= this.toMin(d.start) || be >= this.toMin(d.end) || be <= bs) {
                        return 'The break must sit inside working hours.';
                    }
                }
                return null;
            },
            get workingDays() { return this.days.filter(d => d.on).length; },
            get totalHours() { return this.fmt(this.days.reduce((sum, d) => sum + this.dayMinutes(d), 0)); },
            get hasInvalid() { return this.days.some(d => this.invalid(d)); },
            get dirty() { return JSON.stringify(this.days) !== JSON.stringify(this.original); },

            setDays(list) { this.days.forEach(d => { d.on = list.includes(d.day); }); },
            applyAll() {
                this.days.forEach(d => {
                    if (d.on) { d.start = this.bulk.start; d.end = this.bulk.end; }
                });
            },
            copySchedule() {
                if (!this.copyFrom) return;
                this.days = JSON.parse(JSON.stringify(this.weeks[this.copyFrom]));
            },
            resetForm() { this.days = JSON.parse(JSON.stringify(this.original)); },
            tryLeave(e, url) {
                if (this.dirty) {
                    e.preventDefault();
                    this.leave = { open: true, url: url };
                }
            }
        }"
        @keydown.escape.window="leave.open = false">

        {{-- Staff picker --}}
        <div class="flex gap-2 overflow-x-auto pb-2 mb-4 -mx-4 px-4 sm:mx-0 sm:px-0">
            @foreach ($staffList as $s)
            @php $isSel = $s->id === $selected->id; @endphp
            <a href="{{ route('admin.schedules.index', ['staff' => $s->id]) }}"
                @click="tryLeave($event, @js(route('admin.schedules.index', ['staff' => $s->id])))"
                class="shrink-0 inline-flex items-center gap-2 pl-1.5 pr-4 py-1.5 rounded-full border text-sm font-medium transition
                    {{ $isSel
                        ? 'bg-teal-700 border-teal-700 text-white shadow-sm'
                        : 'bg-white border-slate-300 text-slate-800 hover:border-slate-400 hover:shadow-sm' }}
                    {{ $s->is_active ? '' : 'opacity-70' }}">
                <span class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-semibold
                    {{ $isSel ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700' }}"
                    style="font-family: 'Fraunces', serif;">
                    {{ strtoupper(substr($s->name, 0, 1)) }}
                </span>
                {{ $s->name }}
                @unless ($s->is_active)
                <span class="text-xs {{ $isSel ? 'text-white/80' : 'text-slate-500' }}">inactive</span>
                @endunless
            </a>
            @endforeach
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 items-start">

            {{-- Editor --}}
            <div class="order-2 lg:order-1 lg:col-span-2">
                <form method="POST" action="{{ route('admin.schedules.update', $selected) }}"
                    @submit="submitting = true"
                    class="bg-white border border-slate-300 rounded-2xl shadow-sm">
                    @csrf
                    @method('PUT')

                    <div class="px-4 sm:px-5 py-4 bg-slate-50 border-b border-slate-200 rounded-t-2xl">
                        <h2 class="text-base font-semibold text-slate-900">Weekly hours</h2>
                        <p class="text-sm text-slate-600">Switch a day on, then set when it opens and closes.</p>
                    </div>

                    @if ($errors->any())
                    <div class="px-4 sm:px-5 py-3 bg-white border-b border-slate-200 border-l-4 border-l-red-500">
                        <p class="text-sm font-semibold text-slate-900">The schedule wasn't saved:</p>
                        <ul class="mt-1 text-sm text-red-600 list-disc list-inside">
                            @foreach ($errors->all() as $message)
                            <li>{{ $message }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <div>
                        <template x-for="d in days" :key="d.day">
                            <div class="px-4 sm:px-5 py-4 border-b border-slate-200 transition"
                                :class="d.on ? 'bg-white' : 'bg-slate-50'">

                                <input type="hidden" :name="`days[${d.day}][enabled]`" :value="d.on ? 1 : 0">
                                <input type="hidden" :name="`days[${d.day}][has_break]`" :value="d.has_break ? 1 : 0">
                                <input type="hidden" :name="`days[${d.day}][start]`" :value="d.start">
                                <input type="hidden" :name="`days[${d.day}][end]`" :value="d.end">
                                <input type="hidden" :name="`days[${d.day}][break_start]`" :value="d.break_start">
                                <input type="hidden" :name="`days[${d.day}][break_end]`" :value="d.break_end">

                                <div class="flex flex-col gap-3 lg:flex-row lg:items-center">

                                    {{-- Day + switch --}}
                                    <div class="flex items-center justify-between lg:justify-start gap-3 lg:w-44 shrink-0">
                                        <div class="flex items-center gap-3">
                                            <button type="button" role="switch" :aria-checked="d.on" @click="d.on = !d.on"
                                                class="relative inline-flex h-6 w-11 shrink-0 rounded-full transition-colors"
                                                :class="d.on ? 'bg-teal-600' : 'bg-slate-300'">
                                                <span class="absolute top-0.5 left-0.5 h-5 w-5 rounded-full bg-white shadow transition-transform"
                                                    :class="d.on ? 'translate-x-5' : 'translate-x-0'"></span>
                                            </button>
                                            <span class="text-sm font-semibold"
                                                :class="d.on ? 'text-slate-900' : 'text-slate-500'"
                                                x-text="d.label"></span>
                                        </div>

                                        <span class="lg:hidden text-sm font-medium text-slate-700"
                                            x-text="d.on ? fmt(dayMinutes(d)) + ' h' : 'Day off'"></span>
                                    </div>

                                    {{-- Times --}}
                                    <div class="flex-1 min-w-0">
                                        <div x-show="d.on" class="flex flex-wrap items-center gap-x-4 gap-y-2">
                                            <div class="flex items-center gap-2">
                                                <input type="time" step="900" x-model="d.start"
                                                    class="w-32 rounded-lg border px-2.5 py-1.5 text-sm text-slate-900 focus:border-teal-500 focus:ring-teal-500/20"
                                                    :class="invalid(d) ? 'border-red-400' : 'border-slate-300'">
                                                <span class="text-sm text-slate-600">to</span>
                                                <input type="time" step="900" x-model="d.end"
                                                    class="w-32 rounded-lg border px-2.5 py-1.5 text-sm text-slate-900 focus:border-teal-500 focus:ring-teal-500/20"
                                                    :class="invalid(d) ? 'border-red-400' : 'border-slate-300'">
                                            </div>

                                            <button type="button" x-show="!d.has_break" @click="d.has_break = true"
                                                class="text-sm font-medium text-teal-700 hover:text-teal-900">
                                                + Add break
                                            </button>

                                            <div x-show="d.has_break"
                                                class="flex items-center gap-2 rounded-lg bg-slate-100 border border-slate-200 pl-3 pr-1.5 py-1">
                                                <span class="text-xs font-semibold text-slate-700">Break</span>
                                                <input type="time" step="900" x-model="d.break_start"
                                                    class="w-28 rounded-md border border-slate-300 px-2 py-1 text-sm text-slate-900 focus:border-teal-500 focus:ring-teal-500/20">
                                                <span class="text-sm text-slate-600">–</span>
                                                <input type="time" step="900" x-model="d.break_end"
                                                    class="w-28 rounded-md border border-slate-300 px-2 py-1 text-sm text-slate-900 focus:border-teal-500 focus:ring-teal-500/20">
                                                <button type="button" @click="d.has_break = false" aria-label="Remove break"
                                                    class="p-1 rounded-md text-slate-500 hover:text-slate-900 hover:bg-slate-200 transition">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>

                                        <p x-show="!d.on" class="text-sm text-slate-500">Day off. Not bookable.</p>
                                        <p x-show="invalid(d)" x-text="invalid(d)" class="mt-1.5 text-sm text-red-600"></p>
                                    </div>

                                    {{-- Hours for the day (desktop) --}}
                                    <div class="hidden lg:block w-20 text-right text-sm font-semibold"
                                        :class="d.on ? 'text-slate-900' : 'text-slate-400'"
                                        x-text="d.on ? fmt(dayMinutes(d)) + ' h' : '—'"></div>
                                </div>
                            </div>
                        </template>
                    </div>

                    {{-- Save bar --}}
                    <div class="sticky bottom-0 z-10 flex items-center justify-between gap-3 px-4 sm:px-5 py-3 bg-slate-50/95 backdrop-blur border-t border-slate-200 rounded-b-2xl">
                        <p class="text-sm font-medium">
                            <span x-show="dirty" class="inline-flex items-center gap-1.5 text-slate-900">
                                <span class="w-2 h-2 rounded-full bg-teal-500"></span> Unsaved changes
                            </span>
                            <span x-show="!dirty" class="text-slate-500">All changes saved</span>
                        </p>

                        <div class="flex items-center gap-2">
                            <button type="button" x-show="dirty" @click="resetForm()"
                                class="px-3.5 py-2 text-sm font-medium text-slate-700 hover:bg-slate-200 rounded-lg transition">
                                Reset
                            </button>
                            <button type="submit" :disabled="!dirty || hasInvalid || submitting"
                                class="px-4 py-2 text-sm font-medium text-white bg-teal-600 hover:bg-teal-700 rounded-lg shadow-sm transition disabled:opacity-50 disabled:cursor-not-allowed">
                                Save schedule
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            {{-- Side column --}}
            <div class="order-1 lg:order-2 space-y-5">

                {{-- Summary --}}
                <div class="bg-white border border-slate-300 rounded-2xl shadow-sm overflow-hidden">
                    <div class="relative overflow-hidden px-5 py-5 {{ $selected->is_active ? 'bg-gradient-to-br from-teal-700 to-teal-500' : 'bg-gradient-to-br from-slate-500 to-slate-400' }}">
                        <div class="absolute -right-6 -top-6 w-28 h-28 rounded-full bg-white/10"></div>
                        <div class="absolute right-10 -bottom-10 w-24 h-24 rounded-full bg-white/10"></div>

                        <div class="relative flex items-center gap-3.5">
                            <div class="w-12 h-12 rounded-full bg-white/20 ring-2 ring-white/60 text-white flex items-center justify-center text-lg font-semibold shrink-0"
                                style="font-family: 'Fraunces', serif;">
                                {{ strtoupper(substr($selected->name, 0, 1)) }}
                            </div>
                            <div class="min-w-0">
                                <h2 class="text-lg font-semibold text-white truncate">{{ $selected->name }}</h2>
                                <p class="text-sm text-white/90">{{ $selected->is_active ? 'Active' : 'Inactive' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 divide-x divide-slate-200">
                        <div class="px-5 py-4">
                            <p class="text-xs font-medium text-slate-600">Working days</p>
                            <p class="text-2xl font-semibold text-teal-700" x-text="workingDays + ' / 7'"></p>
                        </div>
                        <div class="px-5 py-4">
                            <p class="text-xs font-medium text-slate-600">Hours per week</p>
                            <p class="text-2xl font-semibold text-slate-900" x-text="totalHours + ' h'"></p>
                        </div>
                    </div>
                </div>

                {{-- Quick tools --}}
                <div class="bg-white border border-slate-300 rounded-2xl shadow-sm">
                    <div class="px-5 py-3.5 bg-slate-50 border-b border-slate-200 rounded-t-2xl">
                        <h3 class="text-sm font-semibold text-slate-900">Quick tools</h3>
                        <p class="text-xs text-slate-600">Nothing is saved until you press Save schedule.</p>
                    </div>

                    <div class="p-5 space-y-5">

                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-600 mb-2">Working days</p>
                            <div class="flex flex-wrap gap-2">
                                <button type="button" @click="setDays([6, 0, 1, 2, 3, 4])"
                                    class="px-3 py-1.5 text-sm font-medium rounded-lg border border-slate-300 text-slate-800 hover:bg-slate-100 transition">Sat – Thu</button>
                                <button type="button" @click="setDays([0, 1, 2, 3, 4])"
                                    class="px-3 py-1.5 text-sm font-medium rounded-lg border border-slate-300 text-slate-800 hover:bg-slate-100 transition">Sun – Thu</button>
                                <button type="button" @click="setDays([6, 0, 1, 2, 3, 4, 5])"
                                    class="px-3 py-1.5 text-sm font-medium rounded-lg border border-slate-300 text-slate-800 hover:bg-slate-100 transition">Every day</button>
                                <button type="button" @click="setDays([])"
                                    class="px-3 py-1.5 text-sm font-medium rounded-lg text-slate-600 hover:bg-slate-100 transition">Clear</button>
                            </div>
                        </div>

                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-600 mb-2">Same hours for working days</p>
                            <div class="flex flex-wrap items-center gap-2">
                                <input type="time" step="900" x-model="bulk.start"
                                    class="w-28 rounded-lg border border-slate-300 px-2.5 py-1.5 text-sm text-slate-900 focus:border-teal-500 focus:ring-teal-500/20">
                                <span class="text-sm text-slate-600">to</span>
                                <input type="time" step="900" x-model="bulk.end"
                                    class="w-28 rounded-lg border border-slate-300 px-2.5 py-1.5 text-sm text-slate-900 focus:border-teal-500 focus:ring-teal-500/20">
                                <button type="button" @click="applyAll()"
                                    class="px-3 py-1.5 text-sm font-medium rounded-lg bg-slate-900 text-white hover:bg-slate-800 transition">Apply</button>
                            </div>
                        </div>

                        @if ($others->isNotEmpty())
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-600 mb-2">Copy from another stylist</p>
                            <div class="flex items-center gap-2">
                                <select x-model="copyFrom"
                                    class="flex-1 min-w-0 rounded-lg border border-slate-300 px-2.5 py-1.5 text-sm text-slate-900 focus:border-teal-500 focus:ring-teal-500/20">
                                    <option value="">Choose…</option>
                                    @foreach ($others as $o)
                                    <option value="{{ $o->id }}">{{ $o->name }}</option>
                                    @endforeach
                                </select>
                                <button type="button" @click="copySchedule()" :disabled="!copyFrom"
                                    class="px-3 py-1.5 text-sm font-medium rounded-lg bg-slate-900 text-white hover:bg-slate-800 transition disabled:opacity-50 disabled:cursor-not-allowed">Copy</button>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Team overview --}}
        <div class="mt-6 bg-white border border-slate-300 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-4 sm:px-5 py-4 bg-slate-50 border-b border-slate-200">
                <h2 class="text-base font-semibold text-slate-900">Team week at a glance</h2>
                <p class="text-sm text-slate-600">Saved schedules for everyone. Click a name to edit.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[820px] text-sm">
                    <thead>
                        <tr class="bg-white border-b border-slate-200 text-xs font-semibold uppercase tracking-wide text-slate-600">
                            <th class="sticky left-0 z-10 bg-white px-4 sm:px-5 py-3 text-left">Staff</th>
                            @foreach ($weeks[$staffList->first()->id] as $day)
                            <th class="px-2 py-3 text-center">
                                <span class="inline-block px-2.5 py-1 rounded-full {{ $day['day'] === $todayNum ? 'bg-teal-600 text-white' : '' }}">
                                    {{ substr($day['label'], 0, 3) }}
                                </span>
                            </th>
                            @endforeach
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        @foreach ($staffList as $s)
                        <tr class="group hover:bg-slate-50 transition {{ $s->is_active ? '' : 'opacity-60' }}">
                            <td class="sticky left-0 z-[1] bg-white group-hover:bg-slate-50 px-4 sm:px-5 py-3 whitespace-nowrap">
                                <a href="{{ route('admin.schedules.index', ['staff' => $s->id]) }}"
                                    @click="tryLeave($event, @js(route('admin.schedules.index', ['staff' => $s->id])))"
                                    class="flex items-center gap-2.5 font-medium text-slate-900 hover:text-teal-700">
                                    <span class="w-7 h-7 rounded-full bg-slate-200 text-slate-700 text-xs font-semibold flex items-center justify-center"
                                        style="font-family: 'Fraunces', serif;">
                                        {{ strtoupper(substr($s->name, 0, 1)) }}
                                    </span>
                                    {{ $s->name }}
                                </a>
                            </td>

                            @foreach ($weeks[$s->id] as $d)
                            <td class="px-2 py-3 text-center align-middle">
                                @if ($d['on'])
                                <span class="inline-block rounded-md bg-slate-100 border border-slate-200 px-2 py-1 text-xs font-semibold text-slate-800 whitespace-nowrap">
                                    {{ $d['start'] }} – {{ $d['end'] }}
                                </span>
                                @if ($d['has_break'])
                                <p class="mt-0.5 text-[10px] font-medium text-slate-500">break {{ $d['break_start'] }}–{{ $d['break_end'] }}</p>
                                @endif
                                @else
                                <span class="text-slate-400">—</span>
                                @endif
                            </td>
                            @endforeach
                        </tr>
                        @endforeach
                    </tbody>

                    <tfoot>
                        <tr class="bg-slate-50 border-t border-slate-200">
                            <td class="sticky left-0 z-[1] bg-slate-50 px-4 sm:px-5 py-3 text-xs font-semibold uppercase tracking-wide text-slate-600">On duty</td>
                            @foreach ($coverage as $count)
                            <td class="px-2 py-3 text-center font-semibold {{ $count === 0 ? 'text-red-600' : 'text-teal-700' }}">
                                {{ $count }}
                            </td>
                            @endforeach
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- Unsaved-changes modal --}}
        <div x-show="leave.open" x-cloak x-transition.opacity
            class="fixed inset-0 z-[100] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="leave.open = false"></div>

            <div x-show="leave.open" x-transition @click.stop
                class="relative w-full max-w-sm bg-white rounded-2xl shadow-xl border border-slate-200 p-6">
                <h2 class="text-base font-semibold text-slate-900">Discard unsaved changes?</h2>
                <p class="mt-2 text-sm text-slate-700">You changed {{ $selected->name }}'s hours but haven't saved them yet.</p>

                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" @click="leave.open = false"
                        class="px-4 py-2 text-sm font-medium text-slate-700 hover:text-slate-900 hover:bg-slate-50 rounded-lg transition">
                        Keep editing
                    </button>
                    <a :href="leave.url"
                        class="px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-lg transition">
                        Discard changes
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endif
</x-admin-layout>