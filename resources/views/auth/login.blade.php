<x-staff-guest-layout>
    <div class="mb-6">
        <h1 class="text-xl font-semibold text-slate-900">Sign in</h1>
        <p class="mt-1 text-sm text-slate-500">Access your schedule and appointments.</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" type="email" name="email" :value="old('email')" required autofocus autocomplete="email"
                class="focus:!border-[#2F8F7A] focus:!ring-[#2F8F7A]/10" />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password"
                type="password"
                name="password"
                required autocomplete="current-password"
                class="focus:!border-[#2F8F7A] focus:!ring-[#2F8F7A]/10" />
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-slate-300 text-[#2F8F7A] accent-[#2F8F7A] focus:ring-[#2F8F7A]" name="remember">
                <span class="ms-2 text-sm text-slate-600">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-end mt-6">
            <x-primary-button class="!bg-[#2F8F7A] hover:!bg-[#267566] focus:!ring-[#2F8F7A]/30 active:!bg-[#1F5C50]">
                {{ __('Log in') }}
            </x-primary-button>
        </div>
    </form>
</x-staff-guest-layout>