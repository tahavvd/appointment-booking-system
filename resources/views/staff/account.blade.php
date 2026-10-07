@php
$names = [6 => 'Saturday', 0 => 'Sunday', 1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday'];
$byDay = $staff->staffSchedules->groupBy('day_of_week');
@endphp

<x-staff-layout title="Account">

    <h1 class="text-2xl font-semibold text-slate-900" style="font-family: 'Fraunces', serif;">Account</h1>

    {{-- Profile --}}
    <div class="mt-4 overflow-hidden rounded-2xl border border-slate-300 bg-white shadow-sm">
        <div class="relative overflow-hidden bg-gradient-to-br from-teal-700 to-teal-500 px-5 py-5">
            <div class="absolute -right-6 -top-6 h-28 w-28 rounded-full bg-white/10"></div>
            <div class="relative flex items-center gap-3.5">
                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-white/20 text-xl font-semibold text-white ring-2 ring-white/60"
                    style="font-family: 'Fraunces', serif;">
                    {{ strtoupper(substr($staff->name, 0, 1)) }}
                </div>
                <div class="min-w-0">
                    <h2 class="truncate text-lg font-semibold text-white">{{ $staff->name }}</h2>
                    <p class="text-sm text-white/90">Stylist</p>
                </div>
            </div>
        </div>

        <dl class="divide-y divide-slate-200 text-sm">
            <div class="flex items-center justify-between gap-4 px-5 py-3">
                <dt class="font-medium text-slate-600">Email</dt>
                <dd class="truncate font-medium text-slate-900">{{ $staff->email }}</dd>
            </div>
            <div class="flex items-center justify-between gap-4 px-5 py-3">
                <dt class="font-medium text-slate-600">Phone</dt>
                <dd class="font-medium text-slate-900">{{ $staff->phone ?: '—' }}</dd>
            </div>
        </dl>
    </div>

    {{-- Weekly schedule --}}
    <div class="mt-4 overflow-hidden rounded-2xl border border-slate-300 bg-white shadow-sm">
        <div class="border-b border-slate-200 bg-slate-50 px-5 py-3.5">
            <h2 class="text-base font-semibold text-slate-900">My week</h2>
            <p class="text-sm text-slate-600">Set by the admin. Ask them if something needs to change.</p>
        </div>

        <ul class="divide-y divide-slate-100 text-sm">
            @foreach ($names as $num => $name)
            @php
            $rows = $byDay->get($num, collect())->sortBy('start_time');
            $text = $rows->map(fn ($r) => substr($r->start_time, 0, 5) . ' – ' . substr($r->end_time, 0, 5))->implode(' · ');
            $isToday = now()->dayOfWeek === $num;
            @endphp
            <li class="flex items-center justify-between gap-4 px-5 py-3 {{ $isToday ? 'bg-teal-50/60' : '' }}">
                <span class="font-semibold {{ $isToday ? 'text-teal-800' : 'text-slate-900' }}">
                    {{ $name }}
                    @if ($isToday)<span class="ml-1 text-xs font-medium text-teal-700">today</span>@endif
                </span>
                <span class="font-medium {{ $rows->isEmpty() ? 'text-slate-400' : 'text-slate-800' }}">
                    {{ $rows->isEmpty() ? 'Day off' : $text }}
                </span>
            </li>
            @endforeach
        </ul>
    </div>

    {{-- Change password --}}
    <div class="mt-4 overflow-hidden rounded-2xl border border-slate-300 bg-white shadow-sm">
        <div class="border-b border-slate-200 bg-slate-50 px-5 py-3.5">
            <h2 class="text-base font-semibold text-slate-900">Change password</h2>
        </div>

        <form method="POST" action="{{ route('staff.account.password') }}" class="space-y-4 p-5">
            @csrf
            @method('PUT')

            <div>
                <label for="current_password" class="mb-1 block text-sm font-medium text-slate-800">Current password</label>
                <input id="current_password" type="password" name="current_password" autocomplete="current-password" required
                    class="w-full rounded-xl border px-3 py-3 text-base text-slate-900 focus:border-teal-500 focus:ring-teal-500/20 {{ $errors->has('current_password') ? 'border-red-400' : 'border-slate-300' }}">
                @error('current_password')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="password" class="mb-1 block text-sm font-medium text-slate-800">New password</label>
                <input id="password" type="password" name="password" autocomplete="new-password" required minlength="8"
                    class="w-full rounded-xl border px-3 py-3 text-base text-slate-900 focus:border-teal-500 focus:ring-teal-500/20 {{ $errors->has('password') ? 'border-red-400' : 'border-slate-300' }}">
                @error('password')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="password_confirmation" class="mb-1 block text-sm font-medium text-slate-800">Confirm new password</label>
                <input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" required
                    class="w-full rounded-xl border border-slate-300 px-3 py-3 text-base text-slate-900 focus:border-teal-500 focus:ring-teal-500/20">
            </div>

            <button type="submit"
                class="min-h-[48px] w-full rounded-xl bg-teal-600 text-sm font-semibold text-white transition hover:bg-teal-700">
                Update password
            </button>
        </form>
    </div>

    {{-- Log out --}}
    <button type="button" @click="logoutOpen = true"
        class="mt-4 min-h-[48px] w-full rounded-xl border border-slate-300 bg-white text-sm font-semibold text-red-600 shadow-sm transition hover:bg-red-50">
        Log out
    </button>
</x-staff-layout>