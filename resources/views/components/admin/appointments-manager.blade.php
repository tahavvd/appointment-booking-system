@props(['appointments', 'date', 'showDateNav' => false, 'fullPageHref' => null])

<div
    x-data="{
        modal: { open: false, url: '', status: '', client: '', service: '', time: '' },
        submitting: false,
        actions: {
            completed: {
                title: 'Mark as completed?',
                text: 'This appointment will be recorded as done.',
                button: 'Yes, complete',
                iconWrap: 'bg-teal-50 text-teal-600',
                btn: 'bg-teal-600 hover:bg-teal-700',
                path: 'M5 13l4 4L19 7'
            },
            no_show: {
                title: 'Mark as no-show?',
                text: 'The client did not come to this appointment.',
                button: 'Yes, no-show',
                iconWrap: 'bg-amber-50 text-amber-600',
                btn: 'bg-amber-600 hover:bg-amber-700',
                path: 'M6 18L18 6M6 6l12 12'
            },
            cancelled: {
                title: 'Cancel this appointment?',
                text: 'The time slot will become available again.',
                button: 'Yes, cancel it',
                iconWrap: 'bg-red-50 text-red-600',
                btn: 'bg-red-600 hover:bg-red-700',
                path: 'M12 9v3.5M12 16h.01M10.3 4.5h3.4L21 18.2a1.5 1.5 0 01-1.3 2.2H4.3A1.5 1.5 0 013 18.2L10.3 4.5z'
            }
        },
        get current() { return this.actions[this.modal.status] ?? this.actions.completed },
        ask(url, status, client, service, time) {
            this.modal = { open: true, url, status, client, service, time };
            this.submitting = false;
        },
        close() { this.modal.open = false }
    }"
    @keydown.escape.window="close()">

    <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden">

        {{-- Header --}}
        <div class="flex flex-wrap items-center justify-between gap-3 px-5 sm:px-6 py-4 border-b border-slate-100">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg bg-teal-50 text-teal-700 flex items-center justify-center shrink-0">
                    <x-admin-nav-icon name="clock" class="w-5 h-5" />
                </div>
                <div>
                    <h2 class="text-base font-semibold text-slate-900">
                        {{ $date->isToday() ? "Today's appointments" : $date->format('l, M j') . ' appointments' }}
                    </h2>
                    <p class="text-sm font-medium text-teal-700">
                        {{ $appointments->count() }} appointment{{ $appointments->count() === 1 ? '' : 's' }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                @if ($showDateNav)
                <a href="{{ route('admin.appointments.index', ['date' => $date->copy()->subDay()->toDateString()]) }}"
                    class="p-2 rounded-lg border border-slate-200 text-slate-600 hover:text-slate-900 hover:border-slate-300 transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>

                <form method="GET" action="{{ route('admin.appointments.index') }}">
                    <input type="date" name="date" value="{{ $date->toDateString() }}"
                        onchange="this.form.submit()"
                        class="text-sm border border-slate-200 rounded-lg px-2.5 py-1.5 text-slate-700 focus:border-teal-500 focus:ring-teal-500/10">
                </form>

                <a href="{{ route('admin.appointments.index', ['date' => $date->copy()->addDay()->toDateString()]) }}"
                    class="p-2 rounded-lg border border-slate-200 text-slate-600 hover:text-slate-900 hover:border-slate-300 transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                </a>

                @unless ($date->isToday())
                <a href="{{ route('admin.appointments.index') }}" class="text-sm font-medium text-teal-700 hover:text-teal-800 px-2">
                    Today
                </a>
                @endunless
                @elseif ($fullPageHref)
                <a href="{{ $fullPageHref }}" class="text-sm font-medium text-teal-700 hover:text-teal-800">
                    Browse all dates →
                </a>
                @endif
            </div>
        </div>

        @if ($appointments->isEmpty())
        <div class="px-6 py-12 text-center">
            <p class="text-slate-500 text-sm font-medium">No appointments {{ $date->isToday() ? 'today' : 'on this day' }}.</p>
        </div>
        @else
        {{-- Scrolls sideways on phones; the table keeps its full set of columns --}}
        <div class="overflow-x-auto">
            <table class="w-full min-w-[960px] text-left text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-xs font-semibold uppercase tracking-wide text-slate-600">
                        <th class="sticky left-0 z-10 bg-slate-50 px-5 sm:px-6 py-3 whitespace-nowrap">Time</th>
                        <th class="px-4 py-3">Client</th>
                        <th class="px-4 py-3">Service</th>
                        <th class="px-4 py-3">Stylist</th>
                        <th class="px-4 py-3 whitespace-nowrap">Duration</th>
                        <th class="px-4 py-3 whitespace-nowrap">Price</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 pr-5 sm:pr-6 text-right">Actions</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    @foreach ($appointments as $appointment)
                    @php
                    $isCancelled = $appointment->status === \App\Enums\AppointmentStatus::Cancelled;
                    $isConfirmed = $appointment->status === \App\Enums\AppointmentStatus::Confirmed;
                    $minutes = $appointment->start_time->diffInMinutes($appointment->end_time);
                    $when = $appointment->start_time->format('D, M j \a\t H:i');
                    $statusUrl = route('admin.appointments.update-status', $appointment);
                    @endphp

                    <tr class="group hover:bg-slate-50 transition {{ $isCancelled ? 'opacity-60' : '' }}">

                        {{-- Time (stays pinned while scrolling sideways) --}}
                        <td class="sticky left-0 z-[1] bg-white group-hover:bg-slate-50 px-5 sm:px-6 py-4 whitespace-nowrap">
                            <p class="font-semibold text-slate-900">{{ $appointment->start_time->format('H:i') }}</p>
                            <p class="text-xs font-medium text-slate-600">to {{ $appointment->end_time->format('H:i') }}</p>
                        </td>

                        {{-- Client --}}
                        <td class="px-4 py-4">
                            <p class="font-medium text-slate-900 whitespace-nowrap">{{ $appointment->client->name }}</p>
                            @if ($appointment->client->phone)
                            <a href="tel:{{ $appointment->client->phone }}" class="text-xs font-medium text-teal-700 hover:text-teal-800">
                                {{ $appointment->client->phone }}
                            </a>
                            @endif
                        </td>

                        {{-- Service + add-ons --}}
                        <td class="px-4 py-4">
                            <p class="font-medium text-slate-900 whitespace-nowrap">{{ $appointment->service->name }}</p>
                            @if ($appointment->addons->isNotEmpty())
                            <div class="mt-1 flex flex-wrap gap-1">
                                @foreach ($appointment->addons as $addon)
                                <span class="inline-flex px-2 py-0.5 rounded-md bg-violet-50 text-violet-700 text-xs font-medium whitespace-nowrap">
                                    + {{ $addon->name }}
                                </span>
                                @endforeach
                            </div>
                            @endif
                        </td>

                        {{-- Stylist --}}
                        <td class="px-4 py-4">
                            <div class="flex items-center gap-2 whitespace-nowrap">
                                <span class="w-7 h-7 rounded-full bg-sky-50 text-sky-700 text-xs font-semibold flex items-center justify-center">
                                    {{ strtoupper(substr($appointment->staff->name, 0, 1)) }}
                                </span>
                                <span class="font-medium text-slate-800">{{ $appointment->staff->name }}</span>
                            </div>
                        </td>

                        {{-- Duration --}}
                        <td class="px-4 py-4 whitespace-nowrap font-medium text-slate-800">
                            {{ $minutes }} min
                        </td>

                        {{-- Price --}}
                        <td class="px-4 py-4 whitespace-nowrap font-semibold text-slate-900">
                            {{ number_format($appointment->total_price, 0) }} DA
                        </td>

                        {{-- Status --}}
                        <td class="px-4 py-4">
                            <x-admin.status-badge :status="$appointment->status" />
                        </td>

                        {{-- Actions --}}
                        <td class="px-4 py-4 pr-5 sm:pr-6">
                            @if ($isConfirmed)
                            <div class="flex items-center justify-end gap-1.5">
                                <button type="button"
                                    @click="ask(@js($statusUrl), 'completed', @js($appointment->client->name), @js($appointment->service->name), @js($when))"
                                    class="text-xs font-medium px-2.5 py-1.5 rounded-lg bg-teal-600 text-white hover:bg-teal-700 transition">
                                    Complete
                                </button>
                                <button type="button"
                                    @click="ask(@js($statusUrl), 'no_show', @js($appointment->client->name), @js($appointment->service->name), @js($when))"
                                    class="text-xs font-medium px-2.5 py-1.5 rounded-lg border border-amber-300 text-amber-700 hover:bg-amber-50 transition">
                                    No-show
                                </button>
                                <button type="button"
                                    @click="ask(@js($statusUrl), 'cancelled', @js($appointment->client->name), @js($appointment->service->name), @js($when))"
                                    class="text-xs font-medium px-2.5 py-1.5 rounded-lg text-red-600 hover:bg-red-50 transition">
                                    Cancel
                                </button>
                            </div>
                            @else
                            <p class="text-right text-slate-400">—</p>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

    {{-- Confirmation modal (one shared modal for every row) --}}
    <div x-show="modal.open" x-cloak x-transition.opacity
        class="fixed inset-0 z-[100] flex items-center justify-center p-4">

        <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="close()"></div>

        <div x-show="modal.open" x-transition @click.stop
            class="relative w-full max-w-sm bg-white rounded-2xl shadow-xl border border-slate-200 p-6">

            <div class="flex items-start gap-4">
                <div class="shrink-0 w-10 h-10 rounded-full flex items-center justify-center" :class="current.iconWrap">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" :d="current.path" />
                    </svg>
                </div>

                <div class="min-w-0">
                    <h2 class="text-base font-semibold text-slate-900" x-text="current.title"></h2>
                    <p class="mt-1 text-sm text-slate-600" x-text="current.text"></p>
                </div>
            </div>

            <div class="mt-4 rounded-xl bg-slate-50 border border-slate-200 px-4 py-3 text-sm">
                <p class="font-semibold text-slate-900" x-text="modal.client"></p>
                <p class="font-medium text-slate-700">
                    <span x-text="modal.service"></span> · <span x-text="modal.time"></span>
                </p>
            </div>

            <form method="POST" :action="modal.url" @submit="submitting = true" class="mt-6 flex justify-end gap-3">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" :value="modal.status">

                <button type="button" @click="close()"
                    class="px-4 py-2 text-sm font-medium text-slate-700 hover:text-slate-900 hover:bg-slate-50 rounded-lg transition">
                    Go back
                </button>

                <button type="submit" :disabled="submitting"
                    class="px-4 py-2 text-sm font-medium text-white rounded-lg transition disabled:opacity-60"
                    :class="current.btn" x-text="current.button"></button>
            </form>
        </div>
    </div>
</div>