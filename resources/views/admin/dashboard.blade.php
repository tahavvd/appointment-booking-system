<x-admin-layout title="Dashboard">

    @php $appointmentsRouteExists = Route::has('admin.appointments.index'); @endphp

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
        @if ($appointmentsRouteExists)
        <a href="{{ route('admin.appointments.index') }}"
            class="block bg-white border border-slate-200 rounded-xl p-5 hover:border-teal-300 transition">
            @else
            <div class="bg-white border border-slate-200 rounded-xl p-5">
                @endif
                <p class="text-sm text-slate-500">Today's appointments</p>
                <p class="mt-2 text-3xl font-semibold text-slate-900">{{ $todayCount }}</p>
                @if ($appointmentsRouteExists)
                <p class="mt-1 text-sm text-teal-700">View appointments →</p>
                @endif
                @if ($appointmentsRouteExists)
        </a>
        @else
    </div>
    @endif
    </div>

    <div class="bg-white border border-slate-200 rounded-xl p-5 sm:p-6">
        <div class="flex items-baseline justify-between mb-6">
            <div>
                <h2 class="text-base font-semibold text-slate-900">Revenue this week</h2>
                <p class="text-sm text-slate-500">Confirmed &amp; completed appointments, Monday to Sunday</p>
            </div>
            <p class="text-2xl font-semibold text-slate-900">{{ number_format($weekRevenueTotal, 0) }} DA</p>
        </div>

        @php $max = max(1, $revenueByDay->max()); @endphp

        <div class="flex items-end gap-3 sm:gap-5 h-48">
            @foreach ($revenueByDay as $day => $amount)
            <div class="flex-1 flex flex-col items-center gap-2 h-full">
                <div class="w-full flex-1 flex items-end">
                    <div class="w-full bg-teal-500/80 rounded-t-md"
                        style="height: {{ $amount > 0 ? max(4, ($amount / $max) * 100) : 2 }}%"
                        title="{{ number_format($amount, 0) }} DA"></div>
                </div>
                <span class="text-xs text-slate-500">{{ $day }}</span>
            </div>
            @endforeach
        </div>
    </div>

</x-admin-layout>