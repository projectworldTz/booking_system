@extends('layouts.admin')
@section('title', 'Audit Logs')
@section('page-title', 'Audit Logs')

@section('content')

{{-- Filters --}}
<form method="GET" class="card p-4 mb-5 flex flex-wrap gap-3 items-end">
    <div class="flex-1 min-w-40">
        <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Hotel</label>
        <select name="hotel_id" class="form-input w-full text-sm">
            <option value="">All Hotels</option>
            @foreach($hotels as $h)
            <option value="{{ $h->id }}" @selected(request('hotel_id') == $h->id)>{{ $h->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="flex-1 min-w-40">
        <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Action</label>
        <select name="action" class="form-input w-full text-sm">
            <option value="">All Actions</option>
            @foreach($actions as $a)
            <option value="{{ $a }}" @selected(request('action') === $a)>{{ ucwords(str_replace(['.','_'], ' ', $a)) }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">From</label>
        <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-input text-sm w-40">
    </div>
    <div>
        <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">To</label>
        <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-input text-sm w-40">
    </div>
    <div class="flex gap-2 self-end">
        <button type="submit" class="btn-primary btn-sm">Filter</button>
        @if(request()->hasAny(['hotel_id','action','date_from','date_to','user_id']))
        <a href="{{ route('admin.audit-logs.index') }}" class="btn-ghost btn-sm">Clear</a>
        @endif
    </div>
</form>

{{-- Log table --}}
<div class="card">
    <div class="p-5 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
        <h3 class="font-bold text-slate-900 dark:text-white">
            Audit Trail
            <span class="ml-2 text-sm font-normal text-slate-500">({{ $logs->total() }} entries)</span>
        </h3>
    </div>
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>When</th>
                    <th>Actor</th>
                    <th>Action</th>
                    <th>Hotel</th>
                    <th>Context</th>
                    <th>IP</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                <tr class="tr-hover">
                    <td class="text-xs text-slate-500 whitespace-nowrap">
                        <span title="{{ $log->created_at->format('d M Y H:i:s') }}">
                            {{ $log->created_at->diffForHumans() }}
                        </span>
                        <br>
                        <span class="text-slate-400">{{ $log->created_at->format('d M Y') }}</span>
                    </td>
                    <td>
                        @if($log->user)
                            <p class="text-sm font-medium text-slate-900 dark:text-white">{{ $log->user->name }}</p>
                            <p class="text-xs text-slate-400">{{ $log->user->email }}</p>
                        @else
                            <span class="text-slate-400 text-xs italic">System</span>
                        @endif
                    </td>
                    <td>
                        @php $color = $log->action_color; @endphp
                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold
                            {{ $color === 'emerald' ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400' :
                               ($color === 'rose'   ? 'bg-rose-100 dark:bg-rose-900/30 text-rose-700 dark:text-rose-400' :
                               ($color === 'amber'  ? 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400' :
                                                      'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300')) }}">
                            {{ $log->action_label }}
                        </span>
                    </td>
                    <td class="text-sm">
                        @if($log->hotel)
                            <a href="{{ route('admin.hotels.show', $log->hotel) }}"
                               class="text-navy dark:text-amber-400 hover:underline">
                                {{ $log->hotel->name }}
                            </a>
                        @else
                            <span class="text-slate-400 text-xs">Platform</span>
                        @endif
                    </td>
                    <td class="text-xs text-slate-500 max-w-xs">
                        @if($log->properties)
                            @foreach($log->properties as $k => $v)
                            <span class="text-slate-400">{{ str_replace('_', ' ', $k) }}:</span>
                            <span class="font-medium text-slate-700 dark:text-slate-300">{{ is_bool($v) ? ($v ? 'yes' : 'no') : $v }}</span>
                            @if(! $loop->last) <br> @endif
                            @endforeach
                        @else
                            <span class="text-slate-300">—</span>
                        @endif
                    </td>
                    <td class="text-xs text-slate-400 font-mono">{{ $log->ip_address ?? '—' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="py-12 text-center text-slate-400">
                        <svg class="mx-auto h-10 w-10 mb-2 opacity-40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        No audit log entries match the current filters.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($logs->hasPages())
    <div class="p-4 border-t border-slate-100 dark:border-slate-700">{{ $logs->links() }}</div>
    @endif
</div>

@endsection
