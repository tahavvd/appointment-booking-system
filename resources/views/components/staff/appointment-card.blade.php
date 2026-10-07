@props(['appointment', 'actions' => true])

@php
$confirmed = $appointment->status === \App\Enums\AppointmentStatus::Confirmed;
$started = $appointment->start_time->lte(now());
$inProgress = $confirmed && $started && $appointment->end_time->gt(now());
$unmarked = $confirmed && $appointment->end_time->lt(now());
$minutes = (int) $appointment->start_time->diffInMinutes($appointment->end_time);
$when = $appointment->start_time->format('D, M j \a\t H:i');
$url = route('staff.appointments.update-status', $appointment);
$bar = match ($appointment->status->value) {
'confirmed' => 'border-l-teal-500',
'completed' => 'border-l-slate-400',
'cancelled' => 'border-l-red-300',
default => 'border-l-amber-400',
};
@endphp

<article class="overflow-hidden rounded-2xl border border-slate-300 border-l-4 {{ $bar }} bg-white shadow-sm
    {{ in_array($appointment->status->value, ['cancelled', 'no_show']) ? 'opacity-80' : '' }}">

    <div class="p-4">
        {{-- Time + status --}}
        <div class="flex items-start justify-between gap-3">
            <p class="text-xl font-semibold leading-tight text-slate-900">
                {{ $appointment->start_time->format('H:i') }}
                <span class="text-sm font-medium text-slate-600">– {{ $appointment->end_time->format('H:i') }}</span>
            </p>

            <div class="flex flex-col items-end gap-1.5">
                <x-admin.status-badge :status="$appointment->status" />

                @if ($inProgress)
                <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-teal-700">
                    <span class="relative flex h-2 w-2">
                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-teal-400 opacity-75"></span>
                        <span class="relative inline-flex h-2 w-2 rounded-full bg-teal-500"></span>
                    </span>
                    In progress
                </span>
                @elseif ($unmarked)
                <span class="rounded-full bg-slate-900 px-2 py-0.5 text-xs font-semibold text-white">Not marked yet</span>
                @endif
            </div>
        </div>

        {{-- Client + service --}}
        <div class="mt-3">
            <p class="text-base font-semibold text-slate-900">{{ $appointment->client->name }}</p>
            <p class="text-sm text-slate-600">{{ $appointment->client->phone }}</p>

            <p class="mt-2 text-sm font-medium text-slate-800">
                {{ $appointment->service->name }} · {{ $minutes }} min · {{ number_format($appointment->total_price, 0) }} DA
            </p>

            @if ($appointment->addons->isNotEmpty())
            <div class="mt-1.5 flex flex-wrap gap-1.5">
                @foreach ($appointment->addons as $addon)
                <span class="rounded-md border border-slate-200 bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-700">+ {{ $addon->name }}</span>
                @endforeach
            </div>
            @endif
        </div>

        @if ($appointment->end_time->isFuture())
        <div class="mt-4">
            <x-staff.contact-buttons :phone="$appointment->client->phone" />
        </div>
        @endif
    </div>

    {{-- Actions --}}
    @if ($actions && $confirmed)
    <div class="border-t border-slate-200 bg-slate-50 px-3 py-3">
        @if ($started)
        <div class="grid grid-cols-2 gap-2">
            <button type="button"
                @click="ask(@js($url), 'completed', @js($appointment->client->name), @js($appointment->service->name), @js($when))"
                class="min-h-[48px] rounded-xl bg-teal-600 text-sm font-semibold text-white transition hover:bg-teal-700">
                Complete
            </button>
            <button type="button"
                @click="ask(@js($url), 'no_show', @js($appointment->client->name), @js($appointment->service->name), @js($when))"
                class="min-h-[48px] rounded-xl border border-slate-300 bg-white text-sm font-semibold text-slate-800 transition hover:bg-slate-100">
                No-show
            </button>
        </div>
        <button type="button"
            @click="ask(@js($url), 'cancelled', @js($appointment->client->name), @js($appointment->service->name), @js($when))"
            class="mt-2 min-h-[44px] w-full rounded-xl text-sm font-medium text-red-600 transition hover:bg-red-50">
            Cancel appointment
        </button>
        @else
        <div class="flex items-center justify-between gap-3">
            <p class="pl-1 text-sm font-medium text-slate-700">Starts {{ $appointment->start_time->diffForHumans() }}</p>
            <button type="button"
                @click="ask(@js($url), 'cancelled', @js($appointment->client->name), @js($appointment->service->name), @js($when))"
                class="min-h-[44px] rounded-xl px-4 text-sm font-medium text-red-600 transition hover:bg-red-50">
                Cancel
            </button>
        </div>
        @endif
    </div>
    @endif
</article>