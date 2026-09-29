@props(['label', 'value', 'icon', 'href' => null, 'accent' => 'teal'])

@php
$accents = [
'teal' => 'bg-teal-50 text-teal-700',
'amber' => 'bg-amber-50 text-amber-700',
'slate' => 'bg-slate-100 text-slate-700',
];
@endphp

@if ($href)
<a href="{{ $href }}" class="group block bg-white border border-slate-200 rounded-2xl p-5 hover:border-teal-300 hover:shadow-sm transition">
    @else
    <div class="bg-white border border-slate-200 rounded-2xl p-5">
        @endif

        <div class="flex items-start justify-between">
            <div>
                <p class="text-sm text-slate-500">{{ $label }}</p>
                <p class="mt-2 text-3xl font-semibold text-slate-900">{{ $value }}</p>
            </div>
            <div class="w-10 h-10 rounded-xl flex items-center justify-center {{ $accents[$accent] ?? $accents['teal'] }}">
                <x-admin-nav-icon :name="$icon" class="w-5 h-5" />
            </div>
        </div>

        @if ($href)
        <p class="mt-3 text-sm text-teal-700 font-medium opacity-0 group-hover:opacity-100 transition">View details →</p>
        @endif

        @if ($href)
</a>
@else
</div>
@endif