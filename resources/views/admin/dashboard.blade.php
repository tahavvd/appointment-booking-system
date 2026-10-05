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

    <div class="bg-white border border-slate-200 rounded-2xl p-5 sm:p-6 mb-6">
        <div class="flex items-center gap-3 mb-6">
            <div class="w-9 h-9 rounded-lg bg-amber-50 text-amber-700 flex items-center justify-center shrink-0">
                <x-admin-nav-icon name="cash" class="w-5 h-5" />
            </div>
            <div class="flex-1 flex flex-wrap items-baseline justify-between gap-2">
                <div>
                    <h2 class="text-base font-semibold text-slate-900">Revenue this week</h2>
                    <p class="text-sm text-slate-500">Confirmed &amp; completed appointments, Monday to Sunday</p>
                </div>
                <p class="text-2xl font-semibold text-slate-900">{{ number_format($weekRevenueTotal, 0) }} DA</p>
            </div>
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

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <x-admin.section-card label="Services" description="Manage services and add-ons offered by the salon."
            icon="scissors" :href="route('admin.services.index')" />
        <x-admin.section-card label="Staff" description="Manage staff accounts and permissions."
            icon="users" :href="route('admin.staff.index')" />
        <x-admin.section-card label="Schedules" description="Set weekly working hours for each staff member."
            icon="calendar" :href="route('admin.schedules.index')" />
    </div>

</x-admin-layout>