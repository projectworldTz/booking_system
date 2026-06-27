<form method="POST" action="{{ $action }}" class="space-y-4">
    @csrf
    @if($method === 'PUT') @method('PUT') @endif

    <div class="grid grid-cols-2 gap-4">
        <div class="col-span-2">
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Asset Name <span class="text-rose-500">*</span></label>
            <input type="text" name="name" value="{{ old('name', $asset?->name) }}" required
                   class="form-input w-full" placeholder="e.g. King Size Bed Frame">
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Category <span class="text-rose-500">*</span></label>
            <select name="asset_category_id" class="form-input w-full" required>
                <option value="">Select…</option>
                @foreach($categories as $cat)
                <option value="{{ $cat->id }}" @selected(old('asset_category_id', $asset?->asset_category_id) == $cat->id)>
                    {{ $cat->name }}
                </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Location</label>
            <input type="text" name="location" value="{{ old('location', $asset?->location) }}"
                   class="form-input w-full" placeholder="Room 101, Lobby…">
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Quantity <span class="text-rose-500">*</span></label>
            <input type="number" name="quantity" min="1" value="{{ old('quantity', $asset?->quantity ?? 1) }}" required
                   class="form-input w-full">
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Condition <span class="text-rose-500">*</span></label>
            <select name="condition" class="form-input w-full" required>
                @foreach(['excellent'=>'Excellent','good'=>'Good','fair'=>'Fair','poor'=>'Poor','damaged'=>'Damaged'] as $v => $l)
                <option value="{{ $v }}" @selected(old('condition', $asset?->condition ?? 'good') === $v)>{{ $l }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Status <span class="text-rose-500">*</span></label>
            <select name="status" class="form-input w-full" required>
                <option value="active" @selected(old('status', $asset?->status ?? 'active') === 'active')>Active</option>
                <option value="under_maintenance" @selected(old('status', $asset?->status) === 'under_maintenance')>Under Maintenance</option>
                <option value="disposed" @selected(old('status', $asset?->status) === 'disposed')>Disposed</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Purchase Date</label>
            <input type="date" name="purchase_date" value="{{ old('purchase_date', $asset?->purchase_date?->format('Y-m-d')) }}"
                   class="form-input w-full">
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Purchase Price (per unit)</label>
            <input type="number" name="purchase_price" step="0.01" min="0"
                   value="{{ old('purchase_price', $asset?->purchase_price) }}"
                   class="form-input w-full" placeholder="0.00">
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Current Value (per unit)</label>
            <input type="number" name="current_value" step="0.01" min="0"
                   value="{{ old('current_value', $asset?->current_value) }}"
                   class="form-input w-full" placeholder="0.00">
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Warranty Expires</label>
            <input type="date" name="warranty_expires_at" value="{{ old('warranty_expires_at', $asset?->warranty_expires_at?->format('Y-m-d')) }}"
                   class="form-input w-full">
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Last Serviced</label>
            <input type="date" name="last_serviced_at" value="{{ old('last_serviced_at', $asset?->last_serviced_at?->format('Y-m-d')) }}"
                   class="form-input w-full">
        </div>

        <div class="col-span-2">
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Description / Notes</label>
            <textarea name="notes" rows="2" class="form-input w-full resize-none"
                      placeholder="Additional details, serial number, supplier…">{{ old('notes', $asset?->notes) }}</textarea>
        </div>
    </div>

    <div class="flex gap-3 pt-1">
        <button type="submit" class="flex-1 btn-primary">
            {{ $asset ? 'Save Changes' : 'Add Asset' }}
        </button>
    </div>
</form>
