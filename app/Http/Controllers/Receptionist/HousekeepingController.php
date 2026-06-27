<?php

namespace App\Http\Controllers\Receptionist;

use App\Enums\Feature;
use App\Http\Controllers\Controller;
use App\Models\HousekeepingTask;
use App\Models\Room;
use Illuminate\Http\Request;

class HousekeepingController extends Controller
{
    public function index(Request $request)
    {
        $hotel = $request->attributes->get('assigned_hotel');

        abort_unless($hotel->hasFeature(Feature::HOUSEKEEPING), 403,
            'Housekeeping module is not enabled for this hotel. Contact your hotel owner.'
        );

        $query = HousekeepingTask::forHotel($hotel->id)
            ->with(['room.roomType', 'assignedTo', 'booking'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        $tasks = $query->paginate(30)->withQueryString();

        // Summary counts
        $summary = [
            'pending'     => HousekeepingTask::forHotel($hotel->id)->pending()->count(),
            'in_progress' => HousekeepingTask::forHotel($hotel->id)->inProgress()->count(),
            'completed'   => HousekeepingTask::forHotel($hotel->id)->completedToday()->count(),
        ];

        // Rooms available for manual task creation
        $rooms = Room::whereHas('roomType', fn ($q) => $q->where('hotel_id', $hotel->id))
            ->orderBy('room_number')
            ->get(['id', 'room_number']);

        return view('receptionist.housekeeping.index', compact(
            'hotel', 'tasks', 'summary', 'rooms'
        ));
    }

    public function store(Request $request)
    {
        $hotel = $request->attributes->get('assigned_hotel');
        abort_unless($hotel->hasFeature(Feature::HOUSEKEEPING), 403);

        $data = $request->validate([
            'room_id'  => ['required', 'exists:rooms,id'],
            'type'     => ['required', 'in:checkout_cleaning,routine_cleaning,deep_clean,turndown'],
            'priority' => ['required', 'in:normal,high,urgent'],
            'notes'    => ['nullable', 'string', 'max:500'],
        ]);

        HousekeepingTask::create([
            'hotel_id' => $hotel->id,
            'room_id'  => $data['room_id'],
            'type'     => $data['type'],
            'priority' => $data['priority'],
            'status'   => HousekeepingTask::STATUS_PENDING,
            'notes'    => $data['notes'] ?? null,
        ]);

        return back()->with('success', 'Housekeeping task created.');
    }

    public function updateStatus(Request $request, HousekeepingTask $task)
    {
        $hotel = $request->attributes->get('assigned_hotel');
        abort_unless($task->hotel_id === $hotel->id, 403);

        $action = $request->input('action');

        match ($action) {
            'start'    => $task->markInProgress(auth()->user()),
            'complete' => $task->markCompleted($request->input('notes')),
            'inspect'  => $task->markInspected(auth()->user(), $request->input('inspector_notes')),
            default    => abort(422, 'Unknown action.'),
        };

        return back()->with('success', 'Task updated.');
    }

    public function assign(Request $request, HousekeepingTask $task)
    {
        $hotel = $request->attributes->get('assigned_hotel');
        abort_unless($task->hotel_id === $hotel->id, 403);

        $task->update(['assigned_to' => $request->validate(['user_id' => 'required|exists:users,id'])['user_id']]);

        return back()->with('success', 'Task assigned.');
    }

    public function destroy(HousekeepingTask $task)
    {
        $hotel = request()->attributes->get('assigned_hotel');
        abort_unless($task->hotel_id === $hotel->id, 403);
        abort_unless(in_array($task->status, [HousekeepingTask::STATUS_PENDING]), 403, 'Only pending tasks can be deleted.');

        $task->delete();

        return back()->with('success', 'Task removed.');
    }
}
