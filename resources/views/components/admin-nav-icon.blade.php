@props(['name'])

@switch($name)
@case('home')
<svg {{ $attributes->merge(['class' => 'w-5 h-5']) }} fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
    <path stroke-linecap="round" stroke-linejoin="round" d="M3 9.5L12 3l9 6.5V20a1 1 0 01-1 1h-5v-6H9v6H4a1 1 0 01-1-1V9.5z" />
</svg>
@break

@case('scissors')
<svg {{ $attributes->merge(['class' => 'w-5 h-5']) }} fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
    <circle cx="6" cy="6" r="2.5" />
    <circle cx="6" cy="18" r="2.5" />
    <path stroke-linecap="round" stroke-linejoin="round" d="M8.2 7.8L20 19M20 5L8.2 16.2" />
</svg>
@break

@case('users')
<svg {{ $attributes->merge(['class' => 'w-5 h-5']) }} fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
    <circle cx="9" cy="8" r="3" />
    <path stroke-linecap="round" stroke-linejoin="round" d="M3.5 19a5.5 5.5 0 0111 0M15 8.5a2.5 2.5 0 110 5M17.5 19a4.5 4.5 0 00-3-4.24" />
</svg>
@break

@case('calendar')
<svg {{ $attributes->merge(['class' => 'w-5 h-5']) }} fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
    <rect x="3" y="5" width="18" height="16" rx="2" />
    <path stroke-linecap="round" d="M8 3v4M16 3v4M3 10h18" />
</svg>
@break

@case('clipboard')
<svg {{ $attributes->merge(['class' => 'w-5 h-5']) }} fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
    <rect x="6" y="4" width="12" height="17" rx="2" />
    <path stroke-linecap="round" d="M9 3.5h6a1 1 0 011 1V6H8V4.5a1 1 0 011-1z" />
    <path stroke-linecap="round" d="M9 11h6M9 15h4" />
</svg>
@break

@case('cash')
<svg {{ $attributes->merge(['class' => 'w-5 h-5']) }} fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
    <rect x="2.5" y="6.5" width="19" height="11" rx="2" />
    <circle cx="12" cy="12" r="2.4" />
    <path stroke-linecap="round" d="M5.5 9v0M18.5 15v0" />
</svg>
@break

@case('clock')
<svg {{ $attributes->merge(['class' => 'w-5 h-5']) }} fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
    <circle cx="12" cy="12" r="9" />
    <path stroke-linecap="round" stroke-linejoin="round" d="M12 7v5l3 3" />
</svg>
@break


@endswitch