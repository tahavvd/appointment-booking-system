<x-client-layout>
    <div class="max-w-md md:max-w-2xl mx-auto px-4 pt-10 pb-16">

        <p class="text-xs tracking-[0.2em] uppercase text-cyan-300/80 mb-2">{{ config('app.name') }}</p>
        <h1 class="text-3xl text-white mb-1" style="font-family:'Fraunces',serif;">Pick a time</h1>
        <p class="text-sm text-white/50 mb-6">With {{ $staff->name }}</p>

        @if (session('error'))
        <p class="text-sm text-red-300 bg-red-900/30 border border-red-800 rounded-lg px-4 py-2 mb-6">
            {{ session('error') }}
        </p>
        @endif

        <!-- Day picker -->
        <div class="flex gap-2 overflow-x-auto pb-2 mb-6 -mx-4 px-4">
            @foreach ($days as $day)
            <a href="{{ route('booking.slots', ['date' => $day->toDateString()]) }}"
                class="shrink-0 flex flex-col items-center justify-center w-14 h-16 rounded-xl
                          {{ $day->isSameDay($selectedDate) ? 'bg-cyan-600 text-white' : 'bg-white/10 text-white/70' }}">
                <span class="text-xs">{{ $day->format('D') }}</span>
                <span class="text-lg font-semibold">{{ $day->format('d') }}</span>
            </a>
            @endforeach
        </div>

        <!-- Slots for the selected day -->
        <div class="grid grid-cols-3 gap-3">
            @forelse ($slots as $slot)
            <div x-data="{ open: false }">
                <button type="button" @click="open = true" class="w-full bg-white text-slate-900 text-sm font-medium py-3 rounded-xl shadow-sm hover:bg-cyan-50 transition">
                    {{ $slot->format('H:i') }}
                </button>

                <!-- Confirmation panel for this specific slot -->
                <div x-show="open" x-cloak @click.self="open = false"
                    class="fixed inset-0 bg-black/50 flex items-end md:items-center justify-center z-50 p-4">
                    <div @click.stop class="bg-white rounded-2xl w-full max-w-sm p-5">

                        <h3 class="text-lg text-slate-900" style="font-family:'Fraunces',serif;">Confirm your booking</h3>

                        <div class="mt-4 space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-slate-500">Stylist</span>
                                <span class="text-slate-900 font-medium">{{ $staff->name }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-500">Service</span>
                                <span class="text-slate-900 font-medium">{{ $service->name }}</span>
                            </div>
                            @foreach ($addons as $addon)
                            <div class="flex justify-between">
                                <span class="text-slate-500">+ {{ $addon->name }}</span>
                                <span class="text-slate-900">{{ number_format($addon->extra_price, 0) }} DA</span>
                            </div>
                            @endforeach
                            <div class="flex justify-between">
                                <span class="text-slate-500">When</span>
                                <span class="text-slate-900 font-medium">{{ $selectedDate->format('D, M d') }} at {{ $slot->format('H:i') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-500">Duration</span>
                                <span class="text-slate-900">{{ $totalDuration }} min</span>
                            </div>
                            <div class="flex justify-between pt-2 border-t border-slate-100">
                                <span class="text-slate-700 font-medium">Total</span>
                                <span class="text-cyan-700 font-semibold">{{ number_format($totalPrice, 0) }} DA</span>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('booking.confirm') }}" class="mt-5">
                            @csrf
                            <input type="hidden" name="start_time" value="{{ $slot->toDateTimeString() }}">

                            <div class="flex gap-2">
                                <button type="button" @click="open = false" class="flex-1 text-sm text-slate-500 hover:text-slate-700 px-4 py-2.5">
                                    Cancel
                                </button>
                                <button type="submit" class="flex-1 bg-cyan-600 hover:bg-cyan-700 text-white text-sm font-semibold px-4 py-2.5 rounded-full transition active:scale-95">
                                    Book
                                </button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
            @empty
            <p class="col-span-3 text-white/50 text-sm">No availability this day.</p>
            @endforelse
        </div>

    </div>
</x-client-layout>