<x-client-layout>
    <div class="max-w-md mx-auto px-4 pt-10 pb-16">

        <p class="text-xs tracking-[0.2em] uppercase text-cyan-300/80 mb-2">{{ config('app.name') }}</p>
        <h1 class="text-3xl text-white mb-1" style="font-family:'Fraunces',serif;">Your appointment</h1>
        <p class="text-sm text-white/50 mb-8">Upcoming bookings.</p>

        @if (session('status'))
        <p class="text-sm text-cyan-200 bg-cyan-900/30 border border-cyan-800 rounded-lg px-4 py-2 mb-6">
            {{ session('status') }}
        </p>
        @endif

        <div class="space-y-4">
            @forelse ($appointments as $appointment)
            <div class="bg-white rounded-2xl p-5 shadow-[0_20px_40px_-15px_rgba(0,0,0,0.4)]">
                <p class="font-medium text-slate-900">{{ $appointment->service->name }}</p>
                <p class="text-sm text-slate-500">with {{ $appointment->staff->name }}</p>
                <p class="text-sm text-slate-500 mt-1">{{ $appointment->start_time->format('D, M d \a\t H:i') }}</p>

                <form method="POST" action="{{ route('booking.appointment.cancel', $appointment) }}"
                    onsubmit="return confirm('Cancel this appointment?')" class="mt-4">
                    @csrf
                    <button type="submit" class="text-sm text-red-600 hover:text-red-700 font-medium">
                        Cancel appointment
                    </button>
                </form>
            </div>
            @empty
            <p class="text-white/50 text-sm">You have no upcoming appointments.</p>
            @endforelse
        </div>

    </div>
</x-client-layout>