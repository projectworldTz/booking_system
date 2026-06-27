<?php

namespace App\Http\Controllers\Owner;

use App\Enums\Feature;
use App\Http\Controllers\Controller;
use App\Models\CorporateAccount;
use App\Models\Hotel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CorporateAccountController extends Controller
{
    public function index(Request $request, Hotel $hotel): View
    {
        $this->gate($hotel);

        $accounts = CorporateAccount::forHotel($hotel->id)
            ->withCount('bookings')
            ->latest()
            ->get();

        return view('owner.corporate.index', compact('hotel', 'accounts'));
    }

    public function create(Hotel $hotel): View
    {
        $this->gate($hotel);
        return view('owner.corporate.create', compact('hotel'));
    }

    public function store(Request $request, Hotel $hotel): RedirectResponse
    {
        $this->gate($hotel);

        $data = $request->validate([
            'company_name'   => ['required', 'string', 'max:255'],
            'contact_name'   => ['nullable', 'string', 'max:255'],
            'contact_email'  => ['nullable', 'email', 'max:255'],
            'contact_phone'  => ['nullable', 'string', 'max:30'],
            'discount_type'  => ['required', 'in:percentage,fixed'],
            'discount_value' => ['required', 'numeric', 'min:0', 'max:100000'],
            'credit_limit'   => ['nullable', 'numeric', 'min:0'],
            'billing_terms'  => ['nullable', 'string', 'max:1000'],
            'notes'          => ['nullable', 'string', 'max:1000'],
            'contract_start' => ['nullable', 'date'],
            'contract_end'   => ['nullable', 'date', 'after_or_equal:contract_start'],
        ]);

        CorporateAccount::create(array_merge($data, [
            'hotel_id'    => $hotel->id,
            'access_code' => CorporateAccount::generateCode(),
            'created_by'  => auth()->id(),
        ]));

        return redirect()->route('owner.hotels.corporate.index', $hotel)
            ->with('success', "Corporate account for \"{$data['company_name']}\" created.");
    }

    public function show(Hotel $hotel, CorporateAccount $corporate): View
    {
        $this->gate($hotel);
        abort_if($corporate->hotel_id !== $hotel->id, 404);

        $bookings = $corporate->bookings()
            ->with('user')
            ->latest()
            ->paginate(15);

        $stats = [
            'total_bookings' => $corporate->bookings()->count(),
            'active_bookings' => $corporate->bookings()->whereIn('status', ['confirmed', 'checked_in'])->count(),
            'total_spend'    => $corporate->bookings()->whereNotIn('status', ['cancelled'])->sum('grand_total'),
            'avg_booking'    => $corporate->bookings()->whereNotIn('status', ['cancelled'])->avg('grand_total') ?? 0,
        ];

        return view('owner.corporate.show', compact('hotel', 'corporate', 'bookings', 'stats'));
    }

    public function edit(Hotel $hotel, CorporateAccount $corporate): View
    {
        $this->gate($hotel);
        abort_if($corporate->hotel_id !== $hotel->id, 404);
        return view('owner.corporate.edit', compact('hotel', 'corporate'));
    }

    public function update(Request $request, Hotel $hotel, CorporateAccount $corporate): RedirectResponse
    {
        $this->gate($hotel);
        abort_if($corporate->hotel_id !== $hotel->id, 404);

        $data = $request->validate([
            'company_name'   => ['required', 'string', 'max:255'],
            'contact_name'   => ['nullable', 'string', 'max:255'],
            'contact_email'  => ['nullable', 'email', 'max:255'],
            'contact_phone'  => ['nullable', 'string', 'max:30'],
            'discount_type'  => ['required', 'in:percentage,fixed'],
            'discount_value' => ['required', 'numeric', 'min:0', 'max:100000'],
            'credit_limit'   => ['nullable', 'numeric', 'min:0'],
            'billing_terms'  => ['nullable', 'string', 'max:1000'],
            'notes'          => ['nullable', 'string', 'max:1000'],
            'contract_start' => ['nullable', 'date'],
            'contract_end'   => ['nullable', 'date', 'after_or_equal:contract_start'],
            'is_active'      => ['boolean'],
        ]);

        $corporate->update($data);

        return redirect()->route('owner.hotels.corporate.show', [$hotel, $corporate])
            ->with('success', 'Corporate account updated.');
    }

    public function regenerateCode(Hotel $hotel, CorporateAccount $corporate): RedirectResponse
    {
        $this->gate($hotel);
        abort_if($corporate->hotel_id !== $hotel->id, 404);

        $corporate->update(['access_code' => CorporateAccount::generateCode()]);

        return back()->with('success', 'Portal link regenerated. Share the new link with the company.');
    }

    public function destroy(Hotel $hotel, CorporateAccount $corporate): RedirectResponse
    {
        $this->gate($hotel);
        abort_if($corporate->hotel_id !== $hotel->id, 404);

        $name = $corporate->company_name;
        $corporate->delete();

        return redirect()->route('owner.hotels.corporate.index', $hotel)
            ->with('success', "Corporate account for \"{$name}\" deleted.");
    }

    private function gate(Hotel $hotel): void
    {
        abort_unless(
            auth()->user()->isSuperAdmin() || $hotel->isOwnedBy(auth()->user()),
            403
        );
        abort_unless(
            $hotel->hasFeature(Feature::CORPORATE_PORTAL),
            403,
            'Corporate Portal feature is not enabled for this hotel.'
        );
    }
}
