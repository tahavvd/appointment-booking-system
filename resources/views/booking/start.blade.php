<x-guest-layout>
    <div class="mb-6">
        <h1 class="text-xl font-semibold text-slate-900">Let's get you booked</h1>
        <p class="mt-1 text-sm text-slate-500">Just your name and number to get started.</p>
    </div>

    <form method="POST" action="{{ route('booking.store') }}">
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" value="Full name" />
            <x-text-input id="name" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" />
        </div>

        <!-- Phone -->
        <div class="mt-4">
            <x-input-label for="phone" value="Phone number" />
            <x-text-input id="phone" type="tel" name="phone" :value="old('phone')" required autocomplete="tel" placeholder="0555 12 34 56" />
            <x-input-error :messages="$errors->get('phone')" />
        </div>

        <div class="mt-6">
            <x-primary-button class="w-full justify-center">
                Continue
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>