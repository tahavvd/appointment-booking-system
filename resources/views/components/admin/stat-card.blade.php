@props(['label', 'value', 'icon', 'href' => null, 'accent' => 'teal'])

@php
$styles = [
'teal' => [
'icon' => 'bg-teal-50 text-teal-700',
'label' => 'text-teal-700',
'link' => 'text-teal-700',
'hover' => 'hover:border-teal-300',
],
'violet' => [
'icon' => 'bg-violet-50 text-violet-700',
'label' => 'text-violet-700',
'link' => 'text-violet-700',
'hover' => 'hover:border-violet-300',
],
'sky' => [
'icon' => 'bg-sky-50 text-sky-700',
'label' => 'text-sky-700',
'link' => 'text-sky-700',
'hover' => 'hover:border-sky-300',
],
'amber' => [
'icon' => 'bg-amber-50 text-amber-700',
'label' => 'text-amber-700',
'link' => 'text-amber-700',
'hover' => 'hover:border-amber-300',
],
];

$s = $styles[$accent] ?? $styles['teal'];
$tag = $href ? 'a' : 'div';
@endphp

<{{ $tag }}
    @if ($href) href="{{ $href }}" @endif
    class="group block bg-white border border-slate-200 rounded-2xl p-4 sm:p-5 {{ $href ? $s['hover'] . ' hover:shadow-sm transition' : '' }}">

    <div class="flex items-start justify-between gap-2">
        <div class="min-w-0">
            <p class="text-xs sm:text-sm font-medium {{ $s['label'] }} truncate">{{ $label }}</p>
            <p class="mt-1 sm:mt-2 text-xl sm:text-3xl font-semibold text-slate-900 truncate">{{ $value }}</p>
        </div>

        <div class="w-8 h-8 sm:w-10 sm:h-10 shrink-0 rounded-lg sm:rounded-xl flex items-center justify-center {{ $s['icon'] }}">
            <x-admin-nav-icon :name="$icon" class="w-4 h-4 sm:w-5 sm:h-5" />
        </div>
    </div>

    @if ($href)
    <p class="mt-2 sm:mt-3 text-xs sm:text-sm font-medium {{ $s['link'] }} sm:opacity-0 sm:group-hover:opacity-100 transition">
        View details →
    </p>
    @endif
</{{ $tag }}>