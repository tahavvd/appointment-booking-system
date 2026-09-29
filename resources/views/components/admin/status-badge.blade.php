@props(['status'])

@php
$status = $status instanceof \App\Enums\AppointmentStatus ? $status->value : $status;

$styles = [
'confirmed' => 'bg-teal-50 text-teal-700 ring-1 ring-teal-600/20',
'completed' => 'bg-slate-100 text-slate-600 ring-1 ring-slate-500/20',
'cancelled' => 'bg-red-50 text-red-700 ring-1 ring-red-600/20',
'no_show' => 'bg-amber-50 text-amber-700 ring-1 ring-amber-600/20',
];

$labels = [
'confirmed' => 'Confirmed',
'completed' => 'Completed',
'cancelled' => 'Cancelled',
'no_show' => 'No-show',
];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium ' . ($styles[$status] ?? 'bg-slate-100 text-slate-600')]) }}>
    {{ $labels[$status] ?? ucfirst($status) }}
</span>