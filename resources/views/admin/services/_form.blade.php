@php
$editing = $service->exists;

$addonRows = old('addons', $editing
? $service->addons->map(fn ($a) => [
'id' => $a->id,
'name' => $a->name,
'extra_price' => (float) $a->extra_price,
'extra_duration_minutes' => (int) $a->extra_duration_minutes,
])->values()->all()
: []);
@endphp

<form method="POST"
    action="{{ $editing ? route('admin.services.update', $service) : route('admin.services.store') }}"
    enctype="multipart/form-data"
    @submit="submitting = true"
    x-data="{
        name: @js(old('name', $service->name ?? '')),
        description: @js(old('description', $service->description ?? '')),
        price: @js(old('base_price', $editing ? (float) $service->base_price : '')),
        duration: @js(old('duration_minutes', $editing ? $service->duration_minutes : 60)),
        active: @js((bool) old('is_active', $service->is_active ?? true)),
        photoUrl: @js($editing ? $service->photo_url : null),
        addons: @js($addonRows),
        nextKey: 0,
        fileError: '',
        submitting: false,

        init() {
            this.addons = this.addons.map((a, n) => ({ ...a, _k: n }));
            this.nextKey = this.addons.length;
        },
        pick(e) {
            const file = e.target.files[0];
            this.fileError = '';
            if (!file) return;
            if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
                this.fileError = 'Use a JPG, PNG or WebP image.';
                e.target.value = '';
                return;
            }
            if (file.size > 4 * 1024 * 1024) {
                this.fileError = 'The image must be 4 MB or smaller.';
                e.target.value = '';
                return;
            }
            this.photoUrl = URL.createObjectURL(file);
        },
        addAddon() {
            if (this.addons.length >= 12) return;
            this.addons.push({ id: null, name: '', extra_price: '', extra_duration_minutes: 0, _k: this.nextKey++ });
        },
        removeAddon(i) { this.addons.splice(i, 1); },
        durationText() {
            const m = parseInt(this.duration) || 0;
            if (!m) return '—';
            const h = Math.floor(m / 60);
            const r = m % 60;
            return ((h ? h + ' h ' : '') + (r ? r + ' min' : '')).trim();
        },
        money(v) {
            const n = parseFloat(v);
            return isNaN(n) ? '—' : Math.round(n).toLocaleString('en-US') + ' DA';
        }
    }">
    @csrf
    @if ($editing)
    @method('PUT')
    @endif

    @if ($errors->any())
    <div class="mb-5 bg-white border border-slate-300 border-l-4 border-l-red-500 rounded-lg px-4 py-3 shadow-sm">
        <p class="text-sm font-semibold text-slate-900">The service wasn't saved:</p>
        <ul class="mt-1 text-sm text-red-600 list-disc list-inside">
            @foreach ($errors->all() as $message)
            <li>{{ $message }}</li>
            @endforeach
        </ul>
        @if (! $editing)
        <p class="mt-2 text-xs text-slate-600">Browsers can't keep a chosen file, so pick the photo again.</p>
        @endif
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 items-start">

        {{-- LEFT: photo, details, add-ons --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- Photo --}}
            <div class="bg-white border border-slate-300 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-5 py-3.5 bg-slate-50 border-b border-slate-200">
                    <h2 class="text-base font-semibold text-slate-900">Photo</h2>
                    <p class="text-sm text-slate-600">
                        Upload from your device. JPG, PNG or WebP, up to 4 MB, at least 400 × 300.
                        @if ($editing) Leave it as is to keep the current photo. @endif
                    </p>
                </div>

                <div class="p-5">
                    <label class="block cursor-pointer focus-within:ring-2 focus-within:ring-teal-500/40 rounded-xl">
                        <input type="file" name="photo" accept="image/jpeg,image/png,image/webp"
                            @if (! $editing) required @endif
                            @change="pick($event)" class="sr-only">

                        <div class="relative h-56 sm:h-64 rounded-xl overflow-hidden border-2 border-dashed bg-slate-100 hover:bg-slate-50 transition
                            {{ $errors->has('photo') ? 'border-red-400' : 'border-slate-300' }}">

                            <template x-if="photoUrl">
                                <img :src="photoUrl" alt="Service photo preview" class="absolute inset-0 w-full h-full object-cover">
                            </template>

                            <div x-show="!photoUrl" class="absolute inset-0 flex flex-col items-center justify-center gap-2 text-slate-600">
                                <svg class="w-10 h-10 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5V18a2 2 0 002 2h14a2 2 0 002-2v-1.5M16 8l-4-4-4 4M12 4v12" />
                                </svg>
                                <span class="text-sm font-medium">Click to choose a photo</span>
                            </div>

                            <span x-show="photoUrl"
                                class="absolute bottom-3 right-3 rounded-full bg-slate-900/75 px-3 py-1.5 text-xs font-medium text-white">
                                Change photo
                            </span>
                        </div>
                    </label>

                    <p x-show="fileError" x-text="fileError" class="mt-2 text-sm text-red-600"></p>
                </div>
            </div>

            {{-- Details --}}
            <div class="bg-white border border-slate-300 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-5 py-3.5 bg-slate-50 border-b border-slate-200">
                    <h2 class="text-base font-semibold text-slate-900">Details</h2>
                </div>

                <div class="p-5 space-y-4">
                    <div>
                        <label for="f-name" class="block text-sm font-medium text-slate-800 mb-1">Name</label>
                        <input id="f-name" type="text" name="name" x-model="name" required maxlength="255"
                            placeholder="e.g. Women's haircut"
                            class="w-full rounded-lg border px-3 py-2 text-sm text-slate-900 focus:border-teal-500 focus:ring-teal-500/20 {{ $errors->has('name') ? 'border-red-400' : 'border-slate-300' }}">
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label for="f-desc" class="block text-sm font-medium text-slate-800">
                                Description <span class="font-normal text-slate-500">(optional)</span>
                            </label>
                            <span class="text-xs text-slate-500" x-text="description.length + ' / 1000'"></span>
                        </div>
                        <textarea id="f-desc" name="description" x-model="description" rows="3" maxlength="1000"
                            placeholder="What does the client get?"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 focus:border-teal-500 focus:ring-teal-500/20"></textarea>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="f-price" class="block text-sm font-medium text-slate-800 mb-1">Price</label>
                            <div class="relative">
                                <input id="f-price" type="number" name="base_price" x-model="price" required min="0" max="999999" step="50"
                                    class="w-full rounded-lg border px-3 py-2 pr-11 text-sm text-slate-900 focus:border-teal-500 focus:ring-teal-500/20 {{ $errors->has('base_price') ? 'border-red-400' : 'border-slate-300' }}">
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-sm font-medium text-slate-500">DA</span>
                            </div>
                        </div>

                        <div>
                            <label for="f-duration" class="block text-sm font-medium text-slate-800 mb-1">
                                Duration <span class="font-normal text-slate-500" x-text="'· ' + durationText()"></span>
                            </label>
                            <div class="relative">
                                <input id="f-duration" type="number" name="duration_minutes" x-model="duration" required min="5" max="480" step="5"
                                    class="w-full rounded-lg border px-3 py-2 pr-12 text-sm text-slate-900 focus:border-teal-500 focus:ring-teal-500/20 {{ $errors->has('duration_minutes') ? 'border-red-400' : 'border-slate-300' }}">
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-sm font-medium text-slate-500">min</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Add-ons --}}
            <div class="bg-white border border-slate-300 rounded-2xl shadow-sm overflow-hidden">
                <div class="flex items-center justify-between gap-3 px-5 py-3.5 bg-slate-50 border-b border-slate-200">
                    <div>
                        <h2 class="text-base font-semibold text-slate-900">Add-ons</h2>
                        <p class="text-sm text-slate-600">Optional extras clients can tick, like a deep conditioning treatment.</p>
                    </div>
                    <span class="shrink-0 rounded-full bg-slate-200 px-2.5 py-1 text-xs font-semibold text-slate-700"
                        x-text="addons.length + ' / 12'"></span>
                </div>

                <div class="p-5 space-y-3">
                    <p x-show="addons.length === 0" class="text-sm text-slate-500">No add-ons yet.</p>

                    <template x-for="(a, i) in addons" :key="a._k">
                        <div class="flex flex-col sm:flex-row sm:items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 p-3">
                            <input type="hidden" :name="`addons[${i}][id]`" :value="a.id ?? ''">

                            <input type="text" :name="`addons[${i}][name]`" x-model="a.name" required maxlength="255"
                                placeholder="Add-on name"
                                class="flex-1 min-w-0 rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-900 focus:border-teal-500 focus:ring-teal-500/20">

                            <div class="flex items-center gap-2">
                                <div class="relative w-28">
                                    <input type="number" :name="`addons[${i}][extra_price]`" x-model="a.extra_price" required min="0" max="99999" step="50" placeholder="0"
                                        class="w-full rounded-lg border border-slate-300 px-3 py-2 pr-9 text-sm text-slate-900 focus:border-teal-500 focus:ring-teal-500/20">
                                    <span class="absolute right-2.5 top-1/2 -translate-y-1/2 text-xs font-medium text-slate-500">DA</span>
                                </div>

                                <div class="relative w-28">
                                    <input type="number" :name="`addons[${i}][extra_duration_minutes]`" x-model="a.extra_duration_minutes" required min="0" max="240" step="5" placeholder="0"
                                        class="w-full rounded-lg border border-slate-300 px-3 py-2 pr-11 text-sm text-slate-900 focus:border-teal-500 focus:ring-teal-500/20">
                                    <span class="absolute right-2.5 top-1/2 -translate-y-1/2 text-xs font-medium text-slate-500">min</span>
                                </div>

                                <button type="button" @click="removeAddon(i)" aria-label="Remove add-on"
                                    class="p-1.5 rounded-lg text-slate-500 hover:text-red-600 hover:bg-red-50 transition">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </template>

                    <button type="button" @click="addAddon()" :disabled="addons.length >= 12"
                        class="inline-flex items-center gap-1.5 text-sm font-medium text-teal-700 hover:text-teal-900 disabled:opacity-50">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14" />
                        </svg>
                        Add an add-on
                    </button>
                </div>
            </div>
        </div>

        {{-- RIGHT: visibility + live preview --}}
        <div class="space-y-5 lg:sticky lg:top-24">

            <div class="bg-white border border-slate-300 rounded-2xl shadow-sm p-5">
                <input type="hidden" name="is_active" :value="active ? 1 : 0">

                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h2 class="text-sm font-semibold text-slate-900" x-text="active ? 'Visible to clients' : 'Hidden from booking'"></h2>
                        <p class="text-sm text-slate-600">Hidden services keep their history but can't be booked.</p>
                    </div>

                    <button type="button" role="switch" :aria-checked="active" @click="active = !active"
                        class="relative inline-flex h-6 w-11 shrink-0 rounded-full transition-colors"
                        :class="active ? 'bg-teal-600' : 'bg-slate-300'">
                        <span class="absolute top-0.5 left-0.5 h-5 w-5 rounded-full bg-white shadow transition-transform"
                            :class="active ? 'translate-x-5' : 'translate-x-0'"></span>
                    </button>
                </div>
            </div>

            <div class="bg-white border border-slate-300 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-5 py-3.5 bg-slate-50 border-b border-slate-200">
                    <h2 class="text-sm font-semibold text-slate-900">Client preview</h2>
                    <p class="text-xs text-slate-600">How the card looks when booking.</p>
                </div>

                <div class="p-4 bg-slate-100">
                    <div class="bg-white rounded-2xl shadow-md overflow-hidden" :class="active ? '' : 'opacity-60'">
                        <div class="relative h-36 bg-slate-200">
                            <template x-if="photoUrl">
                                <img :src="photoUrl" alt="" class="absolute inset-0 w-full h-full object-cover">
                            </template>
                            <div x-show="!photoUrl" class="absolute inset-0 flex items-center justify-center text-xs font-medium text-slate-500">
                                No photo yet
                            </div>
                            <span class="absolute left-3 bottom-3 rounded-lg bg-teal-700 px-3 py-1 text-sm font-semibold text-white shadow"
                                x-text="money(price)"></span>
                        </div>

                        <div class="p-4">
                            <h3 class="text-base font-semibold text-slate-900 truncate" x-text="name || 'Service name'"></h3>
                            <p class="mt-1 text-sm text-slate-600 line-clamp-2" x-text="description || 'The description shows up here.'"></p>
                            <div class="mt-3 flex items-center justify-between">
                                <span class="text-xs text-slate-500" x-text="durationText()"></span>
                                <span class="rounded-full bg-teal-600 px-4 py-1.5 text-xs font-semibold text-white">Book</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Save bar --}}
    <div class="sticky bottom-0 z-10 mt-5 flex items-center justify-end gap-2 px-4 sm:px-5 py-3 bg-white/95 backdrop-blur border border-slate-300 rounded-2xl shadow-sm">
        <a href="{{ route('admin.services.index') }}"
            class="px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 rounded-lg transition">
            Cancel
        </a>
        <button type="submit" :disabled="submitting"
            class="px-4 py-2 text-sm font-medium text-white bg-teal-600 hover:bg-teal-700 rounded-lg shadow-sm transition disabled:opacity-60">
            {{ $editing ? 'Save changes' : 'Add service' }}
        </button>
    </div>
</form>