<?php

namespace App\Http\Controllers\Receptionist;

use App\Enums\Feature;
use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AssetCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $hotel = $request->attributes->get('assigned_hotel');

        abort_unless($hotel->hasFeature(Feature::INVENTORY_MANAGEMENT), 403,
            'Inventory & Asset Management is not enabled for this hotel.'
        );

        $query = Asset::forHotel($hotel->id)
            ->with('category')
            ->latest();

        if ($request->filled('category')) {
            $query->where('asset_category_id', $request->category);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('condition')) {
            $query->where('condition', $request->condition);
        }
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('asset_code', 'like', "%{$request->search}%")
                  ->orWhere('location', 'like', "%{$request->search}%");
            });
        }

        $assets     = $query->paginate(20)->withQueryString();
        $categories = AssetCategory::orderBy('name')->get();

        $summary = [
            'total'       => Asset::forHotel($hotel->id)->count(),
            'active'      => Asset::forHotel($hotel->id)->active()->count(),
            'maintenance' => Asset::forHotel($hotel->id)->maintenance()->count(),
            'damaged'     => Asset::forHotel($hotel->id)->damaged()->count(),
            'total_value' => Asset::forHotel($hotel->id)
                                ->whereNotNull('purchase_price')
                                ->sum(DB::raw('purchase_price * quantity')),
        ];

        $categoryBreakdown = Asset::forHotel($hotel->id)
            ->select('asset_category_id', DB::raw('COUNT(*) as count'))
            ->groupBy('asset_category_id')
            ->with('category')
            ->get();

        return view('receptionist.inventory.index', compact(
            'hotel', 'assets', 'categories', 'summary', 'categoryBreakdown'
        ));
    }

    public function store(Request $request)
    {
        $hotel = $request->attributes->get('assigned_hotel');
        abort_unless($hotel->hasFeature(Feature::INVENTORY_MANAGEMENT), 403);

        $data = $request->validate([
            'asset_category_id'  => ['required', 'exists:asset_categories,id'],
            'name'               => ['required', 'string', 'max:150'],
            'description'        => ['nullable', 'string', 'max:500'],
            'location'           => ['nullable', 'string', 'max:100'],
            'quantity'           => ['required', 'integer', 'min:1'],
            'condition'          => ['required', 'in:excellent,good,fair,poor,damaged'],
            'status'             => ['required', 'in:active,under_maintenance,disposed'],
            'purchase_date'      => ['nullable', 'date'],
            'purchase_price'     => ['nullable', 'numeric', 'min:0'],
            'current_value'      => ['nullable', 'numeric', 'min:0'],
            'warranty_expires_at'=> ['nullable', 'date'],
            'last_serviced_at'   => ['nullable', 'date'],
            'notes'              => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($data, $hotel) {
            $category           = AssetCategory::findOrFail($data['asset_category_id']);
            $data['asset_code'] = Asset::generateCode($hotel->id, $category->name);
            $data['hotel_id']   = $hotel->id;
            Asset::create($data);
        });

        return back()->with('success', 'Asset added to inventory.');
    }

    public function update(Request $request, Asset $asset)
    {
        $hotel = $request->attributes->get('assigned_hotel');
        abort_unless($asset->hotel_id === $hotel->id, 403);
        abort_unless($hotel->hasFeature(Feature::INVENTORY_MANAGEMENT), 403);

        $data = $request->validate([
            'name'               => ['required', 'string', 'max:150'],
            'asset_category_id'  => ['required', 'exists:asset_categories,id'],
            'description'        => ['nullable', 'string', 'max:500'],
            'location'           => ['nullable', 'string', 'max:100'],
            'quantity'           => ['required', 'integer', 'min:1'],
            'condition'          => ['required', 'in:excellent,good,fair,poor,damaged'],
            'status'             => ['required', 'in:active,under_maintenance,disposed'],
            'purchase_date'      => ['nullable', 'date'],
            'purchase_price'     => ['nullable', 'numeric', 'min:0'],
            'current_value'      => ['nullable', 'numeric', 'min:0'],
            'warranty_expires_at'=> ['nullable', 'date'],
            'last_serviced_at'   => ['nullable', 'date'],
            'notes'              => ['nullable', 'string', 'max:1000'],
        ]);

        $asset->update($data);

        return back()->with('success', 'Asset updated.');
    }

    public function storeCategory(Request $request)
    {
        $hotel = $request->attributes->get('assigned_hotel');
        abort_unless($hotel->hasFeature(Feature::INVENTORY_MANAGEMENT), 403);

        $data = $request->validate([
            'name'  => ['required', 'string', 'max:100', 'unique:asset_categories,name'],
            'color' => ['nullable', 'string', 'in:slate,gray,amber,blue,purple,orange,emerald,cyan,rose'],
        ]);

        AssetCategory::create([
            'name'  => $data['name'],
            'color' => $data['color'] ?? 'slate',
        ]);

        return back()->with('success', "Category \"{$data['name']}\" added.");
    }

    public function destroyCategory(Request $request, AssetCategory $category)
    {
        $hotel = $request->attributes->get('assigned_hotel');
        abort_unless($hotel->hasFeature(Feature::INVENTORY_MANAGEMENT), 403);

        if ($category->assets()->exists()) {
            return back()->withErrors(['category' => "Category \"{$category->name}\" is still in use by assets and can't be deleted."]);
        }

        $category->delete();

        return back()->with('success', "Category \"{$category->name}\" deleted.");
    }
}
