<x-client-layout>
    <div class="max-w-md md:max-w-3xl mx-auto px-4 pt-10 pb-16">

        <p class="text-xs tracking-[0.2em] uppercase text-cyan-300/80 mb-2">{{ config('app.name') }}</p>
        <h1 class="text-3xl md:text-4xl text-white mb-1" style="font-family:'Fraunces',serif;">Choose your stylist</h1>
        <p class="text-sm text-white/50 mb-8">Pick who you'd like to see.</p>

        <div class="space-y-4">
            @foreach ($staff as $member)
            <div class="bg-white rounded-2xl shadow-[0_20px_40px_-15px_rgba(0,0,0,0.4)] p-5 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full bg-cyan-100 text-cyan-700 font-semibold flex items-center justify-center" style="font-family:'Fraunces',serif;">
                        {{ strtoupper(substr($member->name, 0, 1)) }}
                    </div>
                    <p class="font-medium text-slate-900">{{ $member->name }}</p>
                </div>

                <form method="POST" action="{{ route('booking.staff.store') }}">
                    @csrf
                    <input type="hidden" name="staff_id" value="{{ $member->id }}">
                    <button type="submit" class="bg-cyan-600 hover:bg-cyan-700 text-white text-sm font-semibold px-5 py-2 rounded-full shadow-sm transition active:scale-95">
                        Choose
                    </button>
                </form>
            </div>
            @endforeach
        </div>

    </div>
</x-client-layout>