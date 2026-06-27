<?php

namespace App\Http\Controllers\Owner;

use App\Enums\Feature;
use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Models\HousekeepingTask;
use Illuminate\Http\Request;

class HousekeepingController extends Controller
{
    public function index(Hotel $hotel, Request $request)
    {
        abort_unless($hotel->isOwnedBy(auth()->user()), 403);
        abort_unless($hotel->hasFeature(Feature::HOUSEKEEPING), 403,
            'Housekeeping module is not enabled for this hotel.'
        );

        $query = HousekeepingTask::forHotel($hotel->id)
            ->with(['room.roomType', 'assignedTo', 'inspectedBy'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $tasks = $query->paginate(30)->withQueryString();

        $summary = [
            'pending'     => HousekeepingTask::forHotel($hotel->id)->pending()->count(),
            'in_progress' => HousekeepingTask::forHotel($hotel->id)->inProgress()->count(),
            'completed'   => HousekeepingTask::forHotel($hotel->id)->completedToday()->count(),
        ];

        return view('owner.housekeeping.index', compact('hotel', 'tasks', 'summary'));
    }
}
