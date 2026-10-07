@props(['phone', 'tone' => 'light'])

@php
$digits = preg_replace('/\D+/', '', (string) $phone);
// 0550123456 -> 213550123456 for WhatsApp
$wa = str_starts_with($digits, '0') ? '213' . substr($digits, 1) : $digits;
$btn = $tone === 'dark'
? 'bg-white/20 text-white hover:bg-white/30'
: 'bg-white border border-slate-300 text-slate-800 hover:bg-slate-100';
@endphp

@if ($digits !== '')
<div class="flex flex-wrap items-center gap-2">
    <a href="tel:{{ $digits }}"
        class="inline-flex min-h-[44px] items-center gap-2 rounded-xl px-4 text-sm font-semibold transition {{ $btn }}">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 4h4l2 5-2.5 1.5a11 11 0 005 5L15 13l5 2v4a2 2 0 01-2 2A16 16 0 013 6a2 2 0 012-2z" />
        </svg>
        Call
    </a>
    <a href="https://wa.me/{{ $wa }}" target="_blank" rel="noopener"
        class="inline-flex min-h-[44px] items-center gap-2 rounded-xl px-4 text-sm font-semibold transition {{ $btn }}">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h8M8 14h5M21 12a8 8 0 01-11.6 7.1L4 20l1-4.6A8 8 0 1121 12z" />
        </svg>
        WhatsApp
    </a>
</div>
@endif