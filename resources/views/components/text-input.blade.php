@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-slate-900 placeholder-slate-400 shadow-sm transition focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10 focus:outline-none disabled:bg-slate-50 disabled:text-slate-400']) }}>