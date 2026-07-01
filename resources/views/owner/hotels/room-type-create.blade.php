@extends('layouts.owner')
@section('title', __('Add Room Type') . ' — ' . $hotel->name)
@section('page-title', __('Add Room Type'))

@section('content')
<div class="max-w-2xl">
    <div class="mb-4"><a href="{{ route('owner.hotels.show', $hotel) }}" class="btn-ghost btn-sm">{{ __('← Back') }}</a></div>

    <form method="POST" action="{{ route('owner.hotels.room-types.store', $hotel) }}" enctype="multipart/form-data">
        @csrf
        <div class="card p-6 space-y-4">
            <h2 class="text-base font-bold text-slate-900 dark:text-white">{{ __('Room Type Details') }}</h2>

            <div>
                <label class="form-label">{{ __('Room Type Name') }} *</label>
                <input type="text" name="name" value="{{ old('name') }}"
                       class="form-input @error('name') border-rose-500 @enderror"
                       placeholder="{{ __('e.g. Deluxe King, Suite…') }}" required>
                @error('name') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <label class="form-label">{{ __('Bed Type') }} *</label>
                    <select name="bed_type" class="form-select @error('bed_type') border-rose-500 @enderror" required>
                        @foreach(['Single','Twin','Double','Queen','King','Bunk'] as $bed)
                        <option value="{{ strtolower($bed) }}" {{ old('bed_type') === strtolower($bed) ? 'selected' : '' }}>{{ $bed }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">{{ __('Number of Beds') }} *</label>
                    <input type="number" name="beds_count" value="{{ old('beds_count', 1) }}"
                           min="1" class="form-input" required>
                </div>
                <div>
                    <label class="form-label">{{ __('Max Guests') }} *</label>
                    <input type="number" name="max_guests" value="{{ old('max_guests', 2) }}"
                           min="1" max="20" class="form-input" required>
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="form-label">{{ __('Base Price / Night') }} *</label>
                    <input type="number" name="base_price" value="{{ old('base_price') }}"
                           min="0" step="0.01" class="form-input @error('base_price') border-rose-500 @enderror" required>
                    @error('base_price') <p class="form-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="form-label">{{ __('Size (m²)') }} <span class="font-normal text-slate-400">({{ __('optional') }})</span></label>
                    <input type="number" name="size_sqm" value="{{ old('size_sqm') }}"
                           min="0" step="0.1" class="form-input">
                </div>
            </div>

            <div>
                <label class="form-label">{{ __('Description') }} <span class="font-normal text-slate-400">({{ __('optional') }})</span></label>
                <textarea name="description" rows="3" class="form-textarea"
                          placeholder="{{ __('Describe this room type…') }}">{{ old('description') }}</textarea>
            </div>

            <div>
                <label class="form-label">{{ __('How many rooms of this type?') }}</label>
                <input type="number" name="quantity" value="{{ old('quantity', 1) }}"
                       min="1" class="form-input w-full sm:w-28">
                <p class="mt-1 text-xs text-slate-500">{{ __('We will create this many individual room records automatically.') }}</p>
            </div>

            {{-- Room Photos ──────────────────────────────────────────────────── --}}
            <div x-data="{
                     previews: [],
                     addFiles(event) {
                         const files = Array.from(event.target.files);
                         files.forEach(file => {
                             const reader = new FileReader();
                             reader.onload = e => this.previews.push({ url: e.target.result, name: file.name });
                             reader.readAsDataURL(file);
                         });
                     },
                     removePreview(index) {
                         this.previews.splice(index, 1);
                         const dt = new DataTransfer();
                         Array.from(this.$refs.fileInput.files)
                             .filter((_, i) => i !== index)
                             .forEach(f => dt.items.add(f));
                         this.$refs.fileInput.files = dt.files;
                     },
                     isDragging: false
                 }">
                <label class="form-label">{{ __('Room Photos') }} <span class="font-normal text-slate-400">({{ __('optional') }})</span></label>
                <p class="text-xs text-slate-500 mb-2">{{ __('The first photo becomes the cover image shown to guests.') }}</p>

                <label for="rt-images"
                       class="relative flex flex-col items-center justify-center gap-2 rounded-2xl border-2 border-dashed cursor-pointer transition-colors
                              border-slate-300 dark:border-slate-600
                              hover:border-navy dark:hover:border-navy-light hover:bg-slate-50 dark:hover:bg-slate-800/50"
                       :class="isDragging ? 'border-navy bg-slate-50 dark:border-navy-light dark:bg-slate-800/50' : ''"
                       @dragover.prevent="isDragging = true"
                       @dragleave.prevent="isDragging = false"
                       @drop.prevent="isDragging = false; $refs.fileInput.files = $event.dataTransfer.files; addFiles({ target: $refs.fileInput })"
                       style="min-height: 7rem; padding: 1.25rem;">
                    <svg class="h-7 w-7 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
                    </svg>
                    <p class="text-sm text-slate-600 dark:text-slate-300">
                        {{ __('Drag & drop photos, or') }} <span class="text-navy dark:text-navy-light underline font-medium">{{ __('click to browse') }}</span>
                    </p>
                    <p class="text-xs text-slate-400">JPG, PNG {{ __('or') }} WebP · {{ __('Up to 8 files · Max 4 MB each') }}</p>
                    <input type="file" id="rt-images" name="images[]"
                           multiple accept="image/jpeg,image/png,image/webp"
                           class="sr-only"
                           x-ref="fileInput"
                           @change="addFiles($event)">
                </label>

                <div x-show="previews.length > 0" class="mt-3 grid grid-cols-4 gap-2">
                    <template x-for="(img, index) in previews" :key="index">
                        <div class="relative group rounded-lg overflow-hidden aspect-square bg-slate-100 dark:bg-slate-800">
                            <img :src="img.url" class="h-full w-full object-cover">
                            <button type="button" @click="removePreview(index)"
                                    class="absolute top-1 right-1 hidden group-hover:flex h-5 w-5 items-center justify-center rounded-full bg-black/60 text-white hover:bg-rose-600 transition">
                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                            <div x-show="index === 0" class="absolute top-1 left-1 rounded-full bg-gold px-1.5 py-0.5 text-[9px] font-bold text-white uppercase">{{ __('Cover') }}</div>
                        </div>
                    </template>
                </div>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary">{{ __('Add Room Type') }}</button>
                <a href="{{ route('owner.hotels.show', $hotel) }}" class="btn-ghost">{{ __('Cancel') }}</a>
            </div>
        </div>
    </form>
</div>
@endsection
