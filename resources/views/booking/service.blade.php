<x-client-layout>
    <div class="max-w-md md:max-w-3xl lg:max-w-6xl mx-auto px-4 pt-10 pb-16 md:pt-16">

        <p class="text-xs tracking-[0.2em] uppercase text-cyan-300/80 mb-2">{{ config('app.name') }}</p>
        <h1 class="text-3xl md:text-4xl lg:text-5xl text-white mb-1" style="font-family:'Fraunces',serif;">Choose your service</h1>
        <p class="text-sm md:text-base text-white/50 mb-8 md:mb-12">Tap Book on the one you'd like.</p>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($services as $service)
            <div x-data="{ open: false }" class="relative bg-white rounded-2xl shadow-[0_20px_40px_-15px_rgba(0,0,0,0.4)] overflow-hidden">

                <img src="{{ $service->photo_url }}" alt="{{ $service->name }}" class="w-full h-44 object-cover">

                <div class="absolute left-4 top-44 bg-cyan-600 text-white text-sm font-semibold px-3 py-1.5 rounded-lg shadow-md" style="transform: translateY(-50%) rotate(-2deg);">
                    {{ number_format($service->base_price, 0) }} DA
                </div>

                <div class="pt-5 px-5 pb-5">
                    <h2 class="text-lg text-slate-900" style="font-family:'Fraunces',serif;">{{ $service->name }}</h2>
                    <p class="text-sm text-slate-500 mt-1 leading-relaxed">{{ $service->description }}</p>

                    <div class="flex items-center justify-between mt-4">
                        <span class="text-xs text-slate-400">{{ $service->duration_minutes }} min</span>

                        <button type="button" @click="open = true" class="bg-cyan-600 hover:bg-cyan-700 text-white text-sm font-semibold px-5 py-2 rounded-full shadow-sm transition active:scale-95">
                            Book
                        </button>
                    </div>
                </div>

                <!-- Addon panel, hidden until "Book" is tapped -->
                <div x-show="open" x-cloak @click.self="open = false"
                    class="fixed inset-0 bg-black/50 flex items-end md:items-center justify-center z-50 p-4">
                    <div @click.stop class="bg-white rounded-2xl w-full max-w-sm p-5">

                        <h3 class="text-lg text-slate-900" style="font-family:'Fraunces',serif;">Add anything extra?</h3>
                        <p class="text-sm text-slate-500 mt-1 mb-4">Optional — skip if you just want the {{ $service->name }}.</p>

                        <form method="POST" action="{{ route('booking.service.store') }}">
                            @csrf
                            <input type="hidden" name="service_id" value="{{ $service->id }}">

                            <div class="space-y-3 mb-5">
                                @forelse ($service->addons as $addon)
                                <label class="flex items-center justify-between gap-3 cursor-pointer">
                                    <span class="flex items-center gap-3">
                                        <input type="checkbox" name="addon_ids[]" value="{{ $addon->id }}"
                                            class="rounded border-slate-300 text-cyan-600 accent-cyan-600 focus:ring-cyan-500">
                                        <span class="text-sm text-slate-700">{{ $addon->name }}</span>
                                    </span>
                                    <span class="text-sm text-slate-400">+{{ number_format($addon->extra_price, 0) }} DA</span>
                                </label>
                                @empty
                                <p class="text-sm text-slate-400">No add-ons available for this service.</p>
                                @endforelse
                            </div>

                            <div class="flex gap-2">
                                <button type="button" @click="open = false" class="flex-1 text-sm text-slate-500 hover:text-slate-700 px-4 py-2.5">
                                    Cancel
                                </button>
                                <button type="submit" class="flex-1 bg-cyan-600 hover:bg-cyan-700 text-white text-sm font-semibold px-4 py-2.5 rounded-full transition active:scale-95">
                                    Continue
                                </button>
                            </div>
                        </form>

                    </div>
                </div>

            </div>
            @endforeach
        </div>

    </div>
</x-client-layout>