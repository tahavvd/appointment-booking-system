<x-client-layout>
    <div class="max-w-md mx-auto px-4 pt-16 pb-16 text-center">

        <div class="w-16 h-16 rounded-full bg-cyan-600 flex items-center justify-center mx-auto mb-6">
            <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
            </svg>
        </div>

        <h1 class="text-2xl text-white mb-2" style="font-family:'Fraunces',serif;">You're booked!</h1>
        <p class="text-white/60 text-sm mb-8">See you soon.</p>

        <div class="bg-white rounded-2xl p-5 text-left space-y-2 text-sm">
            <div class="flex justify-between">
                <span class="text-slate-500">Service</span>
                <span class="text-slate-900 font-medium">{{ $appointment->service->name }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">Stylist</span>
                <span class="text-slate-900 font-medium">{{ $appointment->staff->name }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">When</span>
                <span class="text-slate-900 font-medium">{{ $appointment->start_time->format('D, M d \a\t H:i') }}</span>
            </div>
            <div class="flex justify-between pt-2 border-t border-slate-100">
                <span class="text-slate-700 font-medium">Total</span>
                <span class="text-cyan-700 font-semibold">{{ number_format($appointment->total_price, 0) }} DA</span>
            </div>
        </div>

    </div>
</x-client-layout>