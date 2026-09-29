@props(['label', 'description', 'icon', 'href'])

<a href="{{ $href }}" class="group flex flex-col gap-4 bg-white border border-slate-200 rounded-2xl p-6 hover:border-teal-300 hover:shadow-sm transition">
    <div class="w-11 h-11 rounded-xl bg-teal-50 text-teal-700 flex items-center justify-center">
        <x-admin-nav-icon :name="$icon" class="w-6 h-6" />
    </div>

    <div>
        <h3 class="text-base font-semibold text-slate-900">{{ $label }}</h3>
        <p class="mt-1 text-sm text-slate-500">{{ $description }}</p>
    </div>

    <span class="mt-auto text-sm font-medium text-teal-700 flex items-center gap-1">
        Open
        <svg class="w-4 h-4 group-hover:translate-x-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
        </svg>
    </span>
</a>