@php
$filters = [
'all' => 'All',
'unmarked' => 'Not marked',
'completed' => 'Completed',
'no_show' => 'No-show',
'cancelled' => 'Cancelled',
];
$groups = $appointments->getCollection()->groupBy(fn ($a) => $a->start_time->toDateString());
@endphp

<x-staff-layout title="History">

    <h1 class="text-2xl font-semibold text-slate-900" style="font-family: 'Fraunces', serif;">History</h1>
    <p class="text-sm font-medium text-slate-600">{{ now()->format('F Y') }}</p>

    {{-- Month totals --}}
    <div class="mt-4 grid grid-cols-3 gap-3">
        <div class="rounded-2xl border border-slate-300 bg-white px-4 py-3 shadow-sm">
            <p class="text-xs font-medium text-slate-600">Done</p>
            <p class="text-2xl font-semibold text-teal-700">{{ $done }}</p>
        </div>
        <div class="rounded-2xl border border-slate-300 bg-white px-4 py-3 shadow-sm">
            <p class="text-xs font-medium text-slate-600">No-shows</p>
            <p class="text-2xl font-semibold text-slate-900">{{ $noShows }}</p>
        </div>
        <div class="rounded-2xl border border-slate-300 bg-white px-4 py-3 shadow-sm">
            <p class="text-xs font-medium text-slate-600">Value</p>
            <p class="truncate text-2xl font-semibold text-slate-900">{{ number_format($value, 0) }}<span class="ml-0.5 text-xs font-medium text-slate-500">DA</span></p>
        </div>
    </div>

    {{-- Filters (swipe sideways) --}}
    <div class="-mx-4 mt-4 flex gap-2 overflow-x-auto px-4 pb-1">
        @foreach ($filters as $key => $label)
        <a href="{{ route('staff.history', $key === 'all' ? [] : ['filter' => $key]) }}"
            class="shrink-0 rounded-full border px-4 py-2 text-sm font-semibold transition
                {{ $filter === $key ? 'border-teal-700 bg-teal-700 text-white shadow-sm' : 'border-slate-300 bg-white text-slate-800 hover:border-slate-400' }}">
            {{ $label }}
        </a>
        @endforeach
    </div>

    {{-- List --}}
    @forelse ($groups as $day => $items)
    <h2 class="mb-2 mt-5 text-xs font-semibold uppercase tracking-wide text-slate-600">
        {{ \Illuminate\Support\Carbon::parse($day)->format('l, M j') }}
    </h2>
    <div class="space-y-3">
        @foreach ($items as $appointment)
        <x-staff.appointment-card :appointment="$appointment" />
        @endforeach
    </div>
    @empty
    <div class="mt-5 rounded-2xl border border-slate-300 bg-white p-8 text-center shadow-sm">
        <h2 class="text-base font-semibold text-slate-900">Nothing here</h2>
        <p class="mt-1 text-sm text-slate-600">No appointments match this filter.</p>
    </div>
    @endforelse

    {{-- Pagination --}}
    @if ($appointments->hasPages())
    <div class="mt-6 grid grid-cols-2 gap-3">
        @if ($appointments->previousPageUrl())
        <a href="{{ $appointments->previousPageUrl() }}"
            class="flex min-h-[48px] items-center justify-center rounded-xl border border-slate-300 bg-white text-sm font-semibold text-slate-800 hover:bg-slate-50">
            ← Newer
        </a>
        @else
        <span></span>
        @endif

        @if ($appointments->hasMorePages())
        <a href="{{ $appointments->nextPageUrl() }}"
            class="flex min-h-[48px] items-center justify-center rounded-xl border border-slate-300 bg-white text-sm font-semibold text-slate-800 hover:bg-slate-50">
            Older →
        </a>
        @endif
    </div>
    @endif
</x-staff-layout>