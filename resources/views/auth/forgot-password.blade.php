<x-guest-layout>
    <div class="mb-6">
        <h1 class="text-xl font-semibold text-slate-900">Reset your password</h1>
        <p class="mt-1 text-sm text-slate-500">Enter your email and we'll send you a link to choose a new one.</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" type="email" name="email" :value="old('email')" required autofocus autocomplete="email" />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <div class="flex items-center justify-between mt-6">
            <a class="text-sm text-cyan-700 hover:text-cyan-800 underline underline-offset-2" href="{{ route('login') }}">
                {{ __('Back to sign in') }}
            </a>

            <x-primary-button>
                {{ __('Send reset link') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>