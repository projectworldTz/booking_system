{{-- Sidebar filter panel — included in hotels/index --}}
<form method="GET" action="{{ route('hotels.index') }}"
      class="card divide-y divide-slate-100 dark:divide-slate-700">

    {{-- Search --}}
    <div class="p-4">
        <label class="form-label">{{ __('Search') }}</label>
        <input type="text" name="search" value="{{ $filters['search'] ?? '' }}"
               class="form-input text-sm" placeholder="{{ __('Hotel name, city…') }}">
    </div>

    {{-- Stars --}}
    <div class="p-4">
        <p class="form-label mb-2">{{ __('Star Rating') }}</p>
        <div class="space-y-1.5">
            @foreach([5, 4, 3, 2, 1] as $star)
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="radio" name="star_rating" value="{{ $star }}"
                       {{ ($filters['star_rating'] ?? '') == $star ? 'checked' : '' }}
                       class="text-navy">
                <span class="flex text-gold">
                    @for($i = 1; $i <= $star; $i++) ★ @endfor
                </span>
                <span class="text-xs text-slate-500 dark:text-slate-400">{{ $star }}-{{ __('Star') }}</span>
            </label>
            @endforeach
        </div>
    </div>

    {{-- Category --}}
    @if($categories->isNotEmpty())
    <div class="p-4">
        <label class="form-label">{{ __('Hotel Type') }}</label>
        <select name="category_id" class="form-select text-sm">
            <option value="">{{ __('All Types') }}</option>
            @foreach($categories as $cat)
                <option value="{{ $cat->id }}" {{ ($filters['category_id'] ?? '') == $cat->id ? 'selected' : '' }}>
                    {{ $cat->name }}
                </option>
            @endforeach
        </select>
    </div>
    @endif

    {{-- Price --}}
    <div class="p-4">
        <p class="form-label mb-2">{{ __('Price per night') }}</p>
        <div class="grid grid-cols-2 gap-2">
            <div>
                <label class="text-xs text-slate-500 dark:text-slate-400">{{ __('Min') }} ($)</label>
                <input type="number" name="min_price" value="{{ $filters['min_price'] ?? '' }}"
                       min="0" class="form-input text-sm" placeholder="0">
            </div>
            <div>
                <label class="text-xs text-slate-500 dark:text-slate-400">{{ __('Max') }} ($)</label>
                <input type="number" name="max_price" value="{{ $filters['max_price'] ?? '' }}"
                       min="0" class="form-input text-sm" placeholder="{{ __('Any') }}">
            </div>
        </div>
    </div>

    {{-- City --}}
    <div class="p-4">
        <label class="form-label">{{ __('City') }}</label>
        <input type="text" name="city" value="{{ $filters['city'] ?? '' }}"
               class="form-input text-sm" placeholder="{{ __('e.g. New York') }}">
    </div>

    {{-- Actions --}}
    <div class="flex gap-2 p-4">
        <button type="submit" class="btn-primary btn-sm flex-1">{{ __('Apply') }}</button>
        <a href="{{ route('hotels.index') }}" class="btn-ghost btn-sm">{{ __('Clear') }}</a>
    </div>
</form>
