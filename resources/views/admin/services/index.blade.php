@php
$activeCount = $services->where('is_active', true)->count();
$hiddenCount = $services->count() - $activeCount;
@endphp

<x-admin-layout title="Services">
    <div
        x-data="{
            filter: 'all',
            submitting: false,
            confirm: { open: false, mode: 'hide', url: '', name: '' },
            modes: {
                hide: { title: 'Hide from booking?', text: 'Clients will no longer see this service. Existing appointments are kept.', button: 'Yes, hide', btn: 'bg-slate-900 hover:bg-slate-800', method: 'PATCH' },
                show: { title: 'Show in booking?', text: 'Clients will be able to book this service again.', button: 'Yes, show', btn: 'bg-teal-600 hover:bg-teal-700', method: 'PATCH' },
                remove: { title: 'Delete this service?', text: 'This permanently removes the service, its add-ons and its photo. It cannot be undone.', button: 'Yes, delete', btn: 'bg-red-600 hover:bg-red-700', method: 'DELETE' }
            },
            get cur() { return this.modes[this.confirm.mode]; },
            ask(mode, url, name) {
                this.submitting = false;
                this.confirm = { open: true, mode: mode, url: url, name: name };
            }
        }"
        @keydown.escape.window="confirm.open = false">

        @if (session('status'))
        <div x-data="{ show: true }" x-show="show" x-transition.opacity
            class="mb-4 flex items-start justify-between gap-3 text-sm font-medium text-slate-800 bg-white border border-slate-300 border-l-4 border-l-teal-500 rounded-lg pl-4 pr-2 py-2.5 shadow-sm">
            <p class="py-0.5">{{ session('status') }}</p>
            <button type="button" @click="show = false" aria-label="Dismiss"
                class="shrink-0 p-1 rounded-md text-slate-500 hover:text-slate-900 hover:bg-slate-100 transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        @endif

        @if (session('error'))
        <div x-data="{ show: true }" x-show="show" x-transition.opacity
            class="mb-4 flex items-start justify-between gap-3 text-sm font-medium text-slate-800 bg-white border border-slate-300 border-l-4 border-l-red-500 rounded-lg pl-4 pr-2 py-2.5 shadow-sm">
            <p class="py-0.5">{{ session('error') }}</p>
            <button type="button" @click="show = false" aria-label="Dismiss"
                class="shrink-0 p-1 rounded-md text-slate-500 hover:text-slate-900 hover:bg-slate-100 transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        @endif

        {{-- Top bar --}}
        <div class="flex flex-wrap items-center justify-between gap-4 mb-5">
            <div class="inline-flex p-1 rounded-xl bg-slate-200 text-sm font-medium">
                <button type="button" @click="filter = 'all'"
                    :class="filter === 'all' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                    class="px-3.5 py-1.5 rounded-lg transition">
                    All <span class="ml-1 text-slate-500">{{ $services->count() }}</span>
                </button>
                <button type="button" @click="filter = 'active'"
                    :class="filter === 'active' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                    class="px-3.5 py-1.5 rounded-lg transition">
                    Visible <span class="ml-1 text-slate-500">{{ $activeCount }}</span>
                </button>
                <button type="button" @click="filter = 'hidden'"
                    :class="filter === 'hidden' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                    class="px-3.5 py-1.5 rounded-lg transition">
                    Hidden <span class="ml-1 text-slate-500">{{ $hiddenCount }}</span>
                </button>
            </div>

            <a href="{{ route('admin.services.create') }}"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-teal-600 text-white text-sm font-medium hover:bg-teal-700 shadow-sm transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14" />
                </svg>
                Add service
            </a>
        </div>

        @if ($services->isEmpty())
        <div class="bg-white border border-slate-300 shadow-sm rounded-2xl p-10 text-center">
            <div class="w-12 h-12 mx-auto rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center mb-4">
                <x-admin-nav-icon name="scissors" class="w-6 h-6" />
            </div>
            <h3 class="text-base font-semibold text-slate-900">No services yet</h3>
            <p class="mt-1 text-sm text-slate-600">Add your first service so clients have something to book.</p>
        </div>
        @else
        <div class="rounded-3xl bg-slate-200/70 border border-slate-200 p-3 sm:p-5">
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 sm:gap-5">
                @foreach ($services as $service)
                @php
                $dur = $service->duration_minutes;
                $durText = $dur >= 60
                ? intdiv($dur, 60) . ' h' . ($dur % 60 ? ' ' . ($dur % 60) . ' min' : '')
                : $dur . ' min';
                $shownAddons = $service->addons->take(3);
                $moreAddons = $service->addons->count() - $shownAddons->count();
                @endphp

                <div x-show="filter === 'all' || filter === '{{ $service->is_active ? 'active' : 'hidden' }}'"
                    class="flex flex-col bg-white rounded-2xl border border-slate-300 shadow-sm overflow-hidden hover:shadow-lg hover:-translate-y-0.5 transition">

                    {{-- Photo --}}
                    <div class="relative h-44 bg-slate-200">
                        <img src="{{ $service->photo_url }}" alt="{{ $service->name }}" loading="lazy"
                            class="w-full h-full object-cover {{ $service->is_active ? '' : 'grayscale opacity-60' }}">

                        @unless ($service->is_active)
                        <span class="absolute right-3 top-3 rounded-full bg-slate-900/80 px-2.5 py-1 text-xs font-medium text-white">Hidden</span>
                        @endunless

                        <span class="absolute left-3 bottom-3 rounded-lg bg-teal-700 px-3 py-1 text-sm font-semibold text-white shadow">
                            {{ number_format($service->base_price, 0) }} DA
                        </span>
                    </div>

                    {{-- Body --}}
                    <div class="flex-1 p-5">
                        <h3 class="text-base font-semibold text-slate-900 truncate">{{ $service->name }}</h3>
                        <p class="mt-1 text-sm text-slate-600 line-clamp-2 min-h-[2.5rem]">
                            {{ $service->description ?: 'No description.' }}
                        </p>

                        <div class="mt-4 flex flex-wrap items-center gap-2 text-xs font-medium text-slate-700">
                            <span class="inline-flex items-center gap-1.5 rounded-md bg-slate-100 border border-slate-200 px-2.5 py-1">
                                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <circle cx="12" cy="12" r="9" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 7v5l3 3" />
                                </svg>
                                {{ $durText }}
                            </span>
                            <span class="inline-flex items-center rounded-md bg-slate-100 border border-slate-200 px-2.5 py-1">
                                {{ $service->bookings_count }} booking{{ $service->bookings_count === 1 ? '' : 's' }}
                            </span>
                        </div>

                        <div class="mt-3 flex flex-wrap items-center gap-1.5">
                            @forelse ($shownAddons as $addon)
                            <span class="rounded-md bg-teal-50 border border-teal-100 px-2 py-0.5 text-xs font-medium text-teal-800">
                                + {{ $addon->name }}
                            </span>
                            @empty
                            <span class="text-xs text-slate-500">No add-ons</span>
                            @endforelse

                            @if ($moreAddons > 0)
                            <span class="text-xs font-medium text-slate-600">+{{ $moreAddons }} more</span>
                            @endif
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="px-5 py-3 bg-slate-50 border-t border-slate-200 flex items-center gap-2">
                        <a href="{{ route('admin.services.edit', $service) }}"
                            class="text-sm font-medium px-3 py-1.5 rounded-lg bg-white border border-slate-300 text-slate-800 hover:bg-slate-100 transition">
                            Edit
                        </a>

                        <button type="button"
                            @click="ask(@js($service->is_active ? 'hide' : 'show'), @js(route('admin.services.toggle', $service)), @js($service->name))"
                            class="text-sm font-medium px-3 py-1.5 rounded-lg transition
                                {{ $service->is_active ? 'text-slate-700 hover:bg-slate-200' : 'bg-slate-900 text-white hover:bg-slate-800' }}">
                            {{ $service->is_active ? 'Hide' : 'Show' }}
                        </button>

                        @if ($service->appointments_count === 0)
                        <button type="button" aria-label="Delete {{ $service->name }}"
                            @click="ask('remove', @js(route('admin.services.destroy', $service)), @js($service->name))"
                            class="ml-auto p-1.5 rounded-lg text-red-600 hover:bg-red-50 transition">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M10 11v6M14 11v6M6 7l1 12a2 2 0 002 2h6a2 2 0 002-2l1-12M9 7V4h6v3" />
                            </svg>
                        </button>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>

            <p x-cloak
                x-show="(filter === 'active' && {{ $activeCount }} === 0) || (filter === 'hidden' && {{ $hiddenCount }} === 0)"
                class="text-center text-sm font-medium text-slate-700 py-10">
                Nothing here.
            </p>
        </div>
        @endif

        {{-- Confirmation modal (hide / show / delete) --}}
        <div x-show="confirm.open" x-cloak x-transition.opacity
            class="fixed inset-0 z-[100] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm" @click="confirm.open = false"></div>

            <div x-show="confirm.open" x-transition @click.stop
                class="relative w-full max-w-sm bg-white rounded-2xl shadow-xl border border-slate-200 p-6">

                <h2 class="text-base font-semibold text-slate-900" x-text="cur.title"></h2>
                <p class="mt-2 text-sm text-slate-700" x-text="cur.text"></p>

                <p class="mt-3 rounded-lg bg-slate-100 px-3 py-2 text-sm font-semibold text-slate-900" x-text="confirm.name"></p>

                <form method="POST" :action="confirm.url" @submit="submitting = true" class="mt-6 flex justify-end gap-3">
                    @csrf
                    <input type="hidden" name="_method" :value="cur.method">

                    <button type="button" @click="confirm.open = false"
                        class="px-4 py-2 text-sm font-medium text-slate-700 hover:text-slate-900 hover:bg-slate-50 rounded-lg transition">
                        Go back
                    </button>
                    <button type="submit" :disabled="submitting"
                        class="px-4 py-2 text-sm font-medium text-white rounded-lg transition disabled:opacity-60"
                        :class="cur.btn" x-text="cur.button"></button>
                </form>
            </div>
        </div>
    </div>
</x-admin-layout>