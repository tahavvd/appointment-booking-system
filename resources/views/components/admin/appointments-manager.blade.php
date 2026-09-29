@props(['appointments', 'date', 'showDateNav' => false, 'fullPageHref' => null])

<div class="bg-white border border-slate-200 rounded-2xl overflow-hidden">

    <div class="flex flex-wrap items-center justify-between gap-3 px-5 sm:px-6 py-4 border-b border-slate-100">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-lg bg-teal-50 text-teal-700 flex items-center justify-center shrink-0">
                <x-admin-nav-icon name="clock" class="w-5 h-5" />
            </div>
            <div>
                <h2 class="text-base font-semibold text-slate-900">
                    {{ $date->isToday() ? "Today's appointments" : $date->format('l, M j') . ' appointments' }}
                </h2>
                <p class="text-sm text-slate-500">
                    {{ $appointments->count() }} appointment{{ $appointments->count() === 1 ? '' : 's' }}
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            @if ($showDateNav)
            <a href="{{ route('admin.appointments.index', ['date' => $date->copy()->subDay()->toDateString()]) }}"
                class="p-2 rounded-lg border border-slate-200 text-slate-500 hover:text-slate-900 hover:border-slate-300 transition">
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
                class="p-2 rounded-lg border border-slate-200 text-slate-500 hover:text-slate-900 hover:border-slate-300 transition">
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
        <p class="text-slate-400 text-sm">No appointments {{ $date->isToday() ? 'today' : 'on this day' }}.</p>
    </div>
    @else
    <ul class="divide-y divide-slate-100">
        @foreach ($appointments as $appointment)
        <li class="flex flex-wrap items-center gap-4 px-5 sm:px-6 py-4">
            <div class="w-16 shrink-0 text-sm font-medium text-slate-900">
                {{ $appointment->start_time->format('H:i') }}
            </div>

            <div class="min-w-0 flex-1">
                <p class="text-sm font-medium text-slate-900 truncate">{{ $appointment->client->name }}</p>
                <p class="text-xs text-slate-500 truncate">
                    {{ $appointment->service->name }} · with {{ $appointment->staff->name }}
                </p>
            </div>

            <x-admin.status-badge :status="$appointment->status" />

            @if ($appointment->status === \App\Enums\AppointmentStatus::Confirmed)
            <div class="flex items-center gap-1.5 w-full sm:w-auto">
                <form method="POST" action="{{ route('admin.appointments.update-status', $appointment) }}">
                    @csrf @method('PATCH')
                    <input type="hidden" name="status" value="completed">
                    <button class="text-xs font-medium px-2.5 py-1.5 rounded-lg bg-teal-600 text-white hover:bg-teal-700 transition">
                        Complete
                    </button>
                </form>
                <form method="POST" action="{{ route('admin.appointments.update-status', $appointment) }}">
                    @csrf @method('PATCH')
                    <input type="hidden" name="status" value="no_show">
                    <button class="text-xs font-medium px-2.5 py-1.5 rounded-lg border border-amber-300 text-amber-700 hover:bg-amber-50 transition">
                        No-show
                    </button>
                </form>
                <form method="POST" action="{{ route('admin.appointments.update-status', $appointment) }}">
                    @csrf @method('PATCH')
                    <input type="hidden" name="status" value="cancelled">
                    <button class="text-xs font-medium px-2.5 py-1.5 rounded-lg text-red-600 hover:bg-red-50 transition">
                        Cancel
                    </button>
                </form>
            </div>
            @endif
        </li>
        @endforeach
    </ul>
    @endif
</div>