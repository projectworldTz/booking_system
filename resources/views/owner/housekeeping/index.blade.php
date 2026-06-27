@extends('layouts.owner')
@section('title', 'Housekeeping Overview')
@section('page-title', 'Housekeeping Overview')

@section('content')

{{-- Summary cards --}}
<div class="grid gap-4 sm:grid-cols-3 mb-6">
    @foreach([
        ['Pending',     $summary['pending'],     'text-amber-600 dark:text-amber-400',    'bg-amber-50 dark:bg-amber-900/20',    'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'],
        ['In Progress', $summary['in_progress'], 'text-blue-600 dark:text-blue-400',      'bg-blue-50 dark:bg-blue-900/20',      'M13 10V3L4 14h7v7l9-11h-7z'],
        ['Done Today',  $summary['completed'],   'text-emerald-600 dark:text-emerald-400', 'bg-emerald-50 dark:bg-emerald-900/20', 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
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

{{-- Filters --}}
<div class="flex flex-wrap gap-3 mb-5">
    <form method="GET">
        <select name="status" class="form-input w-auto text-sm" onchange="this.form.submit()">
            <option value="">All Statuses</option>
            @foreach(['pending' => 'Pending', 'in_progress' => 'In Progress', 'completed' => 'Completed', 'inspected' => 'Inspected'] as $val => $lbl)
            <option value="{{ $val }}" @selected(request('status') === $val)>{{ $lbl }}</option>
            @endforeach
        </select>
    </form>
    @if(request('status'))
    <a href="{{ route('owner.housekeeping.index', $hotel) }}" class="btn-ghost btn-sm">Clear</a>
    @endif
</div>

{{-- Task table --}}
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
                    <th>Inspected By</th>
                    <th>Completed</th>
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
                <tr class="tr-hover">
                    <td>
                        <p class="font-bold font-mono text-slate-900 dark:text-white">{{ $task->room->room_number ?? '—' }}</p>
                        <p class="text-xs text-slate-400">{{ $task->room->roomType->name ?? '' }}</p>
                    </td>
                    <td class="text-sm">{{ $task->getTypeLabel() }}</td>
                    <td>
                        <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $priorityColors[$task->priority] }}">
                            {{ ucfirst($task->priority) }}
                        </span>
                    </td>
                    <td>
                        <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $statusColors[$task->status] }}">
                            {{ ucwords(str_replace('_', ' ', $task->status)) }}
                        </span>
                    </td>
                    <td class="text-sm text-slate-600 dark:text-slate-300">{{ $task->assignedTo->name ?? '—' }}</td>
                    <td class="text-sm text-slate-600 dark:text-slate-300">{{ $task->inspectedBy->name ?? '—' }}</td>
                    <td class="text-xs text-slate-400">
                        {{ $task->completed_at?->format('d M H:i') ?? '—' }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="py-14 text-center">
                        <svg class="mx-auto h-12 w-12 text-slate-300 dark:text-slate-600 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                        <p class="text-slate-500 font-medium">No housekeeping tasks found.</p>
                        <p class="text-slate-400 text-sm mt-1">Tasks are created automatically when guests check out.</p>
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

@endsection
