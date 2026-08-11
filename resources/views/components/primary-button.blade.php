<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-5 py-2.5 bg-cyan-600 border border-transparent rounded-xl font-semibold text-sm text-white shadow-sm transition hover:bg-cyan-700 focus:outline-none focus:ring-4 focus:ring-cyan-500/30 active:bg-cyan-800']) }}>
    {{ $slot }}
</button>