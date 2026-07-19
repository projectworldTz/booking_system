{{-- ── Manage Amenities Modal ──────────────────────────────────────────────── --}}
<div x-data="{ open: false }"
     x-on:open-manage-amenities.window="open = true"
     x-show="open"
     x-trap="open"
     class="fixed inset-0 z-50 flex items-center justify-center p-4"
     style="display:none">
    <div class="absolute inset-0 bg-black/50" @click="open = false"></div>
    <div class="relative w-full max-w-md rounded-2xl bg-white dark:bg-slate-800 shadow-2xl p-6 z-10 max-h-[90vh] overflow-y-auto" @click.stop>
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white">{{ __('Manage Amenities') }}</h3>
            <button type="button" @click="open = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form method="POST" action="{{ route('owner.amenities.store') }}" class="flex gap-2 mb-2">
            @csrf
            <input type="text" name="name" required maxlength="100" placeholder="{{ __('e.g. Rooftop Bar') }}"
                   class="form-input flex-1 text-sm">
            <select name="category" class="form-input w-auto text-sm">
                @foreach(['general' => __('General'), 'connectivity' => __('Connectivity'), 'recreation' => __('Recreation'), 'dining' => __('Dining'), 'transport' => __('Transport'), 'services' => __('Services')] as $v => $l)
                <option value="{{ $v }}">{{ $l }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn-primary btn-sm shrink-0">{{ __('Add') }}</button>
        </form>
        @error('name') <p class="form-error mb-3">{{ $message }}</p> @enderror
        @error('amenity') <p class="form-error mb-3">{{ $message }}</p> @enderror

        <div class="space-y-1.5 mt-3">
            @forelse($amenities as $a)
            <div class="flex items-center justify-between rounded-lg px-3 py-2 bg-slate-50 dark:bg-slate-700/50">
                <span class="text-sm text-slate-700 dark:text-slate-200">
                    {{ $a->name }} <span class="text-xs text-slate-400">({{ $a->category }})</span>
                </span>
                <form method="POST" action="{{ route('owner.amenities.destroy', $a) }}"
                      onsubmit="return confirm('{{ __('Delete amenity') }} {{ addslashes($a->name) }}?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="text-slate-400 hover:text-rose-600 transition">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                </form>
            </div>
            @empty
            <p class="text-xs text-slate-400 text-center py-3">{{ __('No amenities yet — add one above.') }}</p>
            @endforelse
        </div>
    </div>
</div>
