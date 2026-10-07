@php
$user = auth()->user();
$hour = now()->hour;
$greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening' );
    $first=\Illuminate\Support\Str::before($user->name, ' ');
    @endphp

    <x-staff-layout title="Today">

        {{-- Heading --}}
        <div class="flex items-end justify-between gap-3">
            <div>
                <p class="text-sm font-medium text-slate-600">
                    {{ $date->isToday() ? $greeting . ', ' . $first : 'Viewing another day' }}
                </p>
                <h1 class="text-2xl font-semibold text-slate-900" style="font-family: 'Fraunces', serif;">
                    {{ $date->isToday() ? 'Today' : $date->format('l') }}, {{ $date->format('M j') }}
                </h1>
            </div>

            @unless ($date->isToday())
            <a href="{{ route('staff.dashboard') }}" class="shrink-0 text-sm font-semibold text-teal-700 hover:text-teal-900">
                Back to today
            </a>
            @endunless
        </div>

        {{-- Date strip (swipe sideways) --}}
        <div class="-mx-4 mt-4 flex gap-2 overflow-x-auto px-4 pb-2"
            x-data x-init="$nextTick(() => $el.querySelector('[aria-current]')?.scrollIntoView({ inline: 'center', block: 'nearest' }))">
            @foreach ($days as $day)
            @php
            $isSel = $day->isSameDay($date);
            $count = $counts[$day->toDateString()] ?? 0;
            @endphp
            <a href="{{ route('staff.dashboard', ['date' => $day->toDateString()]) }}"
                @if ($isSel) aria-current="date" @endif
                class="shrink-0 w-14 rounded-2xl border py-2 text-center transition
                {{ $isSel ? 'bg-teal-700 border-teal-700 text-white shadow-sm' : 'bg-white border-slate-300 text-slate-800 hover:border-slate-400' }}">
                <span class="block text-[11px] font-semibold uppercase {{ $isSel ? 'text-white/80' : 'text-slate-500' }}">{{ $day->format('D') }}</span>
                <span class="block text-lg font-semibold leading-tight">{{ $day->format('j') }}</span>
                <span class="mt-0.5 inline-flex h-5 min-w-[1.25rem] items-center justify-center rounded-full px-1 text-[11px] font-semibold
                {{ $count > 0 ? ($isSel ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-800') : 'text-transparent' }}">
                    {{ $count }}
                </span>
            </a>
            @endforeach
        </div>

        {{-- Unmarked banner --}}
        @if ($unmarkedCount > 0)
        <a href="{{ route('staff.history', ['filter' => 'unmarked']) }}"
            class="mt-2 flex items-center justify-between gap-3 rounded-xl border border-slate-300 border-l-4 border-l-slate-900 bg-white px-4 py-3 shadow-sm">
            <p class="text-sm font-medium text-slate-800">
                {{ $unmarkedCount }} past appointment{{ $unmarkedCount === 1 ? '' : 's' }} not marked yet
            </p>
            <span class="shrink-0 text-sm font-semibold text-teal-700">Review →</span>
        </a>
        @endif

        {{-- Working hours --}}
        <p class="mt-3 inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm font-medium text-slate-800">
            <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <circle cx="12" cy="12" r="9" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 7v5l3 3" />
            </svg>
            {{ $hours->isEmpty() ? 'Day off' : 'Working ' . $hours->implode('  ·  ') }}
        </p>

        {{-- Next up --}}
        @if ($next)
        <div class="relative mt-4 overflow-hidden rounded-2xl bg-gradient-to-br from-teal-700 to-teal-500 p-5 text-white shadow-md">
            <div class="absolute -right-8 -top-8 h-32 w-32 rounded-full bg-white/10"></div>
            <div class="absolute right-12 -bottom-12 h-28 w-28 rounded-full bg-white/10"></div>

            <div class="relative">
                <p class="text-xs font-semibold uppercase tracking-wide text-white/80">
                    {{ $next->start_time->isPast() ? 'In progress' : 'Next up · ' . $next->start_time->diffForHumans() }}
                </p>
                <p class="mt-1 text-3xl font-semibold" style="font-family: 'Fraunces', serif;">
                    {{ $next->start_time->format('H:i') }}
                    <span class="text-lg font-medium text-white/80">– {{ $next->end_time->format('H:i') }}</span>
                </p>
                <p class="mt-3 text-lg font-semibold">{{ $next->client->name }}</p>
                <p class="text-sm text-white/90">
                    {{ $next->service->name }}@if ($next->addons->isNotEmpty()) · + {{ $next->addons->pluck('name')->implode(', ') }}@endif
                </p>

                <div class="mt-4">
                    <x-staff.contact-buttons :phone="$next->client->phone" tone="dark" />
                </div>
            </div>
        </div>
        @endif

        {{-- Day numbers --}}
        <div class="mt-4 grid grid-cols-3 gap-3">
            <div class="rounded-2xl border border-slate-300 bg-white px-4 py-3 shadow-sm">
                <p class="text-xs font-medium text-slate-600">To do</p>
                <p class="text-2xl font-semibold text-teal-700">{{ $stats['todo'] }}</p>
            </div>
            <div class="rounded-2xl border border-slate-300 bg-white px-4 py-3 shadow-sm">
                <p class="text-xs font-medium text-slate-600">Done</p>
                <p class="text-2xl font-semibold text-slate-900">{{ $stats['done'] }}</p>
            </div>
            <div class="rounded-2xl border border-slate-300 bg-white px-4 py-3 shadow-sm">
                <p class="text-xs font-medium text-slate-600">Total</p>
                <p class="text-2xl font-semibold text-slate-900">{{ $stats['total'] }}</p>
            </div>
        </div>

        {{-- Appointments --}}
        <div class="mt-4 space-y-3">
            @forelse ($appointments as $appointment)
            <x-staff.appointment-card :appointment="$appointment" />
            @empty
            <div class="rounded-2xl border border-slate-300 bg-white p-8 text-center shadow-sm">
                <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-xl bg-slate-100 text-slate-600">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3M4 11h16M5 5h14a1 1 0 011 1v13a1 1 0 01-1 1H5a1 1 0 01-1-1V6a1 1 0 011-1z" />
                    </svg>
                </div>
                <h2 class="text-base font-semibold text-slate-900">
                    {{ $hours->isEmpty() ? 'Day off' : 'Nothing booked' }}
                </h2>
                <p class="mt-1 text-sm text-slate-600">
                    {{ $hours->isEmpty() ? 'You are not scheduled to work this day.' : 'No appointments for this day yet.' }}
                </p>
            </div>
            @endforelse
        </div>
    </x-staff-layout>