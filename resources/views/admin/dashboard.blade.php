<x-admin-layout title="Dashboard">

    @if (session('status'))
    <div class="mb-4 text-sm font-medium text-teal-700 bg-teal-50 border border-teal-200 rounded-lg px-4 py-2.5">
        {{ session('status') }}
    </div>
    @endif

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-6">
        <x-admin.stat-card label="Today's appointments" :value="$todayCount" icon="clipboard"
            :href="route('admin.appointments.index')" accent="teal" />
        <x-admin.stat-card label="Active staff" :value="$activeStaffCount" icon="users"
            :href="route('admin.staff.index')" accent="violet" />
        <x-admin.stat-card label="Services offered" :value="$servicesCount" icon="scissors"
            :href="route('admin.services.index')" accent="sky" />
        <x-admin.stat-card label="Revenue this week" :value="number_format($weekRevenueTotal, 0) . ' DA'"
            icon="cash" accent="amber" />
    </div>

    <div class="mb-6">
        <x-admin.appointments-manager :appointments="$todayAppointments" :date="$today"
            :full-page-href="route('admin.appointments.index')" />
    </div>

    @php
    $total = (float) $weekRevenueTotal;
    $max = max(1, (float) $revenueByDay->max());
    $dayCount = max(1, $revenueByDay->count());
    $average = $total / $dayCount;
    $bestAmount = (float) $revenueByDay->max();
    $bestDay = $bestAmount > 0 ? $revenueByDay->sortDesc()->keys()->first() : null;
    $todayLabel = now()->format('D');
    @endphp

    <div class="bg-white border border-slate-200 rounded-2xl p-5 sm:p-6 mb-6">

        {{-- Header --}}
        <div class="flex flex-wrap items-start justify-between gap-4 mb-5">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg bg-amber-50 text-amber-700 flex items-center justify-center shrink-0">
                    <x-admin-nav-icon name="cash" class="w-5 h-5" />
                </div>
                <div>
                    <h2 class="text-base font-semibold text-slate-900">Revenue this week</h2>
                    <p class="text-sm text-slate-600">Confirmed &amp; completed appointments</p>
                </div>
            </div>

            <p class="text-2xl sm:text-3xl font-semibold text-slate-900 tracking-tight">
                {{ number_format($total, 0) }} <span class="text-base font-medium text-teal-700">DA</span>
            </p>
        </div>

        {{-- Quick stats --}}
        <div class="grid grid-cols-2 gap-3 mb-6">
            <div class="rounded-xl bg-teal-50 px-3.5 py-3">
                <p class="text-xs font-medium text-teal-700">Daily average</p>
                <p class="mt-0.5 text-base sm:text-lg font-semibold text-slate-900">{{ number_format($average, 0) }} DA</p>
            </div>
            <div class="rounded-xl bg-slate-100 px-3.5 py-3">
                <p class="text-xs font-medium text-slate-700">Best day</p>
                <p class="mt-0.5 text-base sm:text-lg font-semibold text-slate-900">
                    @if ($bestDay)
                    {{ $bestDay }} <span class="text-sm font-medium text-slate-700">· {{ number_format($bestAmount, 0) }} DA</span>
                    @else
                    —
                    @endif
                </p>
            </div>
        </div>

        <p class="sm:hidden mb-2 text-xs font-medium text-slate-600">Swipe to see all days →</p>

        {{-- Chart: scrolls sideways on phones --}}
        <div class="overflow-x-auto -mx-5 px-5 sm:mx-0 sm:px-0 pb-1">
            <div class="min-w-[540px] sm:min-w-0">

                <div class="relative h-56 mt-6">

                    {{-- Gridlines --}}
                    @foreach ([0, 25, 50, 75, 100] as $line)
                    <div class="absolute inset-x-0 border-t {{ $line === 0 ? 'border-slate-300' : 'border-dashed border-slate-200' }}"
                        style="bottom: {{ $line }}%"></div>
                    @endforeach

                    {{-- Bars --}}
                    <div class="absolute inset-0 flex items-end gap-3 sm:gap-5">
                        @foreach ($revenueByDay as $day => $amount)
                        @php
                        $isBest = $bestDay === $day;
                        $height = $amount > 0 ? max(6, ($amount / $max) * 100) : 0;
                        @endphp

                        <div class="relative flex-1 h-full flex items-end justify-center">
                            @if ($amount > 0)
                            <div class="relative w-full max-w-14 rounded-t-lg transition-all duration-300 hover:brightness-110
                                    {{ $isBest ? 'bg-gradient-to-t from-teal-800 to-teal-600' : 'bg-gradient-to-t from-teal-500 to-teal-300' }}"
                                style="height: {{ $height }}%"
                                title="{{ $day }}: {{ number_format($amount, 0) }} DA">
                                <span class="absolute -top-6 inset-x-0 text-center text-xs font-semibold text-slate-800 whitespace-nowrap">
                                    {{ number_format($amount, 0) }}
                                </span>
                            </div>
                            @else
                            <div class="w-full max-w-14 h-1.5 rounded-full bg-slate-200"></div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Day labels --}}
                <div class="flex gap-3 sm:gap-5 mt-3">
                    @foreach ($revenueByDay as $day => $amount)
                    <div class="flex-1 flex justify-center">
                        <span class="text-xs font-semibold px-2.5 py-1 rounded-full
                        {{ $day === $todayLabel ? 'bg-teal-600 text-white' : 'text-slate-700' }}">
                            {{ $day }}
                        </span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <x-admin.section-card label="Services" description="Manage services and add-ons offered by the salon."
            icon="scissors" :href="route('admin.services.index')" />
        <x-admin.section-card label="Staff" description="Manage staff accounts and permissions."
            icon="users" :href="route('admin.staff.index')" />
        <x-admin.section-card label="Schedules" description="Set weekly working hours for each staff member."
            icon="calendar" :href="route('admin.schedules.index')" />
    </div>

</x-admin-layout>