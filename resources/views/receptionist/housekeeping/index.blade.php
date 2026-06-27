@extends('layouts.receptionist')
@section('title', 'Housekeeping')
@section('page-title', 'Housekeeping')

@section('content')

{{-- ── Summary cards ────────────────────────────────────────────────────────── --}}
<div class="grid gap-4 sm:grid-cols-3 mb-6">
    @foreach([
        ['Pending',       $summary['pending'],     'text-amber-600 dark:text-amber-400',   'bg-amber-50 dark:bg-amber-900/20',   'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'],
        ['In Progress',   $summary['in_progress'], 'text-blue-600 dark:text-blue-400',     'bg-blue-50 dark:bg-blue-900/20',     'M13 10V3L4 14h7v7l9-11h-7z'],
        ['Done Today',    $summary['completed'],   'text-emerald-600 dark:text-emerald-400','bg-emerald-50 dark:bg-emerald-900/20','M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
    ] as [$label, $count, $textColor, $bgColor, $icon])
    <div class="stat-card">
        <div class="flex items-center justify-between">
            <p class="text-sm font-medium text-slate-500 dark:text-slate-400">{{ $label }}</p>
            <div class="flex h-9 w-9 items-center justify-center rounded-xl {{ $bgColor }}">
                <svg class="h-4 w-4 {{ $textColor }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/>
                </svg>
            </div>
        </div>
        <p class="mt-2 text-3xl font-bold {{ $textColor }}">{{ $count }}</p>
    </div>
    @endforeach
</div>

{{-- ── Controls row: filter + create ──────────────────────────────────────────── --}}
<div class="flex flex-wrap gap-3 items-center justify-between mb-5">

    {{-- Filters --}}
    <form method="GET" class="flex flex-wrap gap-2">
        <select name="status" class="form-input w-auto text-sm" onchange="this.form.submit()">
            <option value="">All Statuses</option>
            @foreach(['pending' => 'Pending', 'in_progress' => 'In Progress', 'completed' => 'Completed', 'inspected' => 'Inspected'] as $val => $lbl)
            <option value="{{ $val }}" @selected(request('status') === $val)>{{ $lbl }}</option>
            @endforeach
        </select>
        <select name="priority" class="form-input w-auto text-sm" onchange="this.form.submit()">
            <option value="">All Priorities</option>
            @foreach(['urgent' => 'Urgent', 'high' => 'High', 'normal' => 'Normal'] as $val => $lbl)
            <option value="{{ $val }}" @selected(request('priority') === $val)>{{ $lbl }}</option>
            @endforeach
        </select>
        @if(request()->hasAny(['status', 'priority']))
        <a href="{{ route('receptionist.housekeeping.index') }}" class="btn-ghost btn-sm">Clear</a>
        @endif
    </form>

    {{-- New task button --}}
    <button x-data @click="$dispatch('open-create-task')"
            class="btn-primary btn-sm flex items-center gap-2">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
        </svg>
        Create Task
    </button>
</div>

{{-- ── Task table ───────────────────────────────────────────────────────────── --}}
<div class="card">
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Room</th>
                    <th>Type</th>
                    <th>Priority</th>
                    <th>Status</th>
                    <th>Assigned To</th>
                    <th>Timing</th>
                    <th>Notes</th>
                    <th class="w-40">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tasks as $task)
                @php
                    $statusColors = [
                        'pending'     => 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400',
                        'in_progress' => 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400',
                        'completed'   => 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400',
                        'inspected'   => 'bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400',
                    ];
                    $priorityColors = [
                        'urgent' => 'bg-rose-100 dark:bg-rose-900/30 text-rose-700 dark:text-rose-400',
                        'high'   => 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400',
                        'normal' => 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300',
                    ];
                @endphp
                <tr class="tr-hover {{ $task->priority === 'urgent' ? 'border-l-2 border-rose-400' : '' }}">

                    {{-- Room --}}
                    <td>
                        <p class="font-bold text-slate-900 dark:text-white font-mono">{{ $task->room->room_number ?? '—' }}</p>
                        <p class="text-xs text-slate-400">{{ $task->room->roomType->name ?? '' }}</p>
                    </td>

                    {{-- Type --}}
                    <td class="text-sm">{{ $task->getTypeLabel() }}</td>

                    {{-- Priority --}}
                    <td>
                        <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $priorityColors[$task->priority] }}">
                            {{ ucfirst($task->priority) }}
                        </span>
                    </td>

                    {{-- Status --}}
                    <td>
                        <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $statusColors[$task->status] }}">
                            {{ ucwords(str_replace('_', ' ', $task->status)) }}
                        </span>
                    </td>

                    {{-- Assigned To --}}
                    <td class="text-sm text-slate-600 dark:text-slate-300">
                        {{ $task->assignedTo->name ?? '—' }}
                    </td>

                    {{-- Timing --}}
                    <td class="text-xs text-slate-400">
                        @if($task->completed_at)
                            Done {{ $task->completed_at->diffForHumans() }}
                        @elseif($task->started_at)
                            Started {{ $task->started_at->diffForHumans() }}
                        @else
                            {{ $task->created_at->diffForHumans() }}
                        @endif
                    </td>

                    {{-- Notes --}}
                    <td class="text-xs text-slate-400 max-w-[160px] truncate" title="{{ $task->notes }}">
                        {{ $task->notes ?? '—' }}
                    </td>

                    {{-- Actions --}}
                    <td>
                        <div class="flex flex-wrap gap-1">
                            @if($task->status === 'pending')
                                <form method="POST" action="{{ route('receptionist.housekeeping.status', $task) }}">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="action" value="start">
                                    <button class="rounded-lg bg-blue-500 hover:bg-blue-600 text-white text-xs font-semibold px-2.5 py-1.5 transition">
                                        Start
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('receptionist.housekeeping.destroy', $task) }}"
                                      onsubmit="return confirm('Remove this task?')">
                                    @csrf @method('DELETE')
                                    <button class="rounded-lg border border-slate-300 dark:border-slate-600 text-slate-500 text-xs font-semibold px-2.5 py-1.5 hover:bg-slate-100 dark:hover:bg-slate-700 transition">
                                        ✕
                                    </button>
                                </form>
                            @elseif($task->status === 'in_progress')
                                <form method="POST" action="{{ route('receptionist.housekeeping.status', $task) }}">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="action" value="complete">
                                    <button class="rounded-lg bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-semibold px-2.5 py-1.5 transition">
                                        Done
                                    </button>
                                </form>
                            @elseif($task->status === 'completed')
                                <form method="POST" action="{{ route('receptionist.housekeeping.status', $task) }}">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="action" value="inspect">
                                    <button class="rounded-lg bg-purple-500 hover:bg-purple-600 text-white text-xs font-semibold px-2.5 py-1.5 transition">
                                        Inspect ✓
                                    </button>
                                </form>
                            @else
                                <span class="text-xs text-slate-400 italic">Complete</span>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="py-14 text-center">
                        <svg class="mx-auto h-12 w-12 text-slate-300 dark:text-slate-600 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                        <p class="text-slate-500 font-medium">No housekeeping tasks found.</p>
                        <p class="text-slate-400 text-sm mt-1">Tasks are auto-created on guest check-out, or you can create one manually.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($tasks->hasPages())
    <div class="p-4 border-t border-slate-100 dark:border-slate-700">{{ $tasks->links() }}</div>
    @endif
</div>

{{-- ── Create Task Modal ────────────────────────────────────────────────────── --}}
<div x-data="{ open: false }"
     x-on:open-create-task.window="open = true"
     x-show="open"
     x-trap="open"
     class="fixed inset-0 z-50 flex items-center justify-center p-4"
     style="display:none;">

    {{-- Backdrop --}}
    <div class="absolute inset-0 bg-black/50" @click="open = false"></div>

    {{-- Dialog --}}
    <div class="relative w-full max-w-md rounded-2xl bg-white dark:bg-slate-800 shadow-2xl p-6 z-10"
         @click.stop>
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white">Create Housekeeping Task</h3>
            <button @click="open = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <form method="POST" action="{{ route('receptionist.housekeeping.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Room</label>
                <select name="room_id" class="form-input w-full" required>
                    <option value="">Select room…</option>
                    @foreach($rooms as $room)
                    <option value="{{ $room->id }}">Room {{ $room->room_number }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Task Type</label>
                <select name="type" class="form-input w-full" required>
                    <option value="routine_cleaning">Routine Cleaning</option>
                    <option value="checkout_cleaning">Checkout Cleaning</option>
                    <option value="deep_clean">Deep Clean</option>
                    <option value="turndown">Turndown</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Priority</label>
                <select name="priority" class="form-input w-full" required>
                    <option value="normal">Normal</option>
                    <option value="high">High</option>
                    <option value="urgent">Urgent</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Notes (optional)</label>
                <textarea name="notes" rows="2" class="form-input w-full resize-none"
                          placeholder="Any special instructions…"></textarea>
            </div>
            <div class="flex gap-3 pt-1">
                <button type="submit" class="flex-1 btn-primary">Create Task</button>
                <button type="button" @click="open = false" class="flex-1 btn-ghost">Cancel</button>
            </div>
        </form>
    </div>
</div>

@endsection
