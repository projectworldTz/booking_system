@extends('layouts.receptionist')
@section('title', __('Dashboard'))
@section('page-title', __('Reception Dashboard'))

@section('content')
<div x-data="receptionistDashboard()" x-init="init()">

{{-- Quick stats (numbers auto-update every 30s) --}}
<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4 mb-6">
    <div class="stat-card">
        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('Arrivals Today') }}</p>
        <p class="mt-1 text-3xl font-bold text-navy dark:text-navy-light" x-text="stats.arrivals_today">{{ $stats['arrivals_today'] }}</p>
    </div>
    <div class="stat-card">
        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('Departures Today') }}</p>
        <p class="mt-1 text-3xl font-bold text-gold" x-text="stats.departures_today">{{ $stats['departures_today'] }}</p>
    </div>
    <div class="stat-card">
        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('Awaiting Confirm') }}</p>
        <p class="mt-1 text-3xl font-bold text-amber-500" x-text="stats.pending_confirm">{{ $stats['pending_confirm'] }}</p>
    </div>
    <div class="stat-card relative">
        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ __('Currently In-House') }}</p>
        <p class="mt-1 text-3xl font-bold text-emerald-600" x-text="stats.currently_in">{{ $stats['currently_in'] }}</p>
        {{-- Live indicator --}}
        <div class="absolute top-3 right-3 flex items-center gap-1.5" title="{{ __('Auto-refreshes every 30 seconds') }}">
            <span :class="loading ? 'bg-amber-400 animate-pulse' : 'bg-emerald-400'"
                  class="block h-2 w-2 rounded-full"></span>
            <span class="text-[10px] text-slate-400 dark:text-slate-500 tabular-nums" x-text="'↻ ' + refreshIn + 's'"></span>
        </div>
    </div>
</div>

<div class="grid gap-6 lg:grid-cols-2">
    {{-- Today's arrivals --}}
    <div class="card">
        <div class="flex items-center justify-between p-5 border-b border-slate-100 dark:border-slate-700">
            <h2 class="font-bold text-slate-900 dark:text-white">{{ __("Today's Arrivals") }}</h2>
            <a href="{{ route('receptionist.bookings.index', ['status' => 'confirmed']) }}" class="text-xs text-navy dark:text-navy-light hover:underline">{{ __('View all') }}</a>
        </div>
        @forelse($arrivalsToday as $booking)
        <div class="flex items-center justify-between px-5 py-3 border-b border-slate-50 dark:border-slate-800 last:border-0">
            <div>
                <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $booking->user->name }}</p>
                <p class="text-xs text-slate-500">{{ $booking->booking_number }} · {{ $booking->roomType->name ?? '—' }}</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="badge badge-{{ $booking->status }}">{{ ucfirst(str_replace('_', ' ', $booking->status)) }}</span>
                @if($booking->status === 'confirmed')
                <form method="POST" action="{{ route('receptionist.bookings.check-in', $booking) }}" data-loading>
                    @csrf
                    <button type="submit" class="btn-primary btn-sm">{{ __('Check In') }}</button>
                </form>
                @endif
            </div>
        </div>
        @empty
        <p class="p-5 text-sm text-slate-500">{{ __('No arrivals scheduled today.') }}</p>
        @endforelse
    </div>

    {{-- Today's departures --}}
    <div class="card">
        <div class="flex items-center justify-between p-5 border-b border-slate-100 dark:border-slate-700">
            <h2 class="font-bold text-slate-900 dark:text-white">{{ __("Today's Departures") }}</h2>
            <a href="{{ route('receptionist.bookings.index', ['status' => 'checked_in']) }}" class="text-xs text-navy dark:text-navy-light hover:underline">{{ __('View all') }}</a>
        </div>
        @forelse($departuresToday as $booking)
        <div class="flex items-center justify-between px-5 py-3 border-b border-slate-50 dark:border-slate-800 last:border-0">
            <div>
                <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $booking->user->name }}</p>
                <p class="text-xs text-slate-500">{{ $booking->booking_number }} · {{ __('Room') }} {{ $booking->roomType->name ?? '—' }}</p>
            </div>
            <form method="POST" action="{{ route('receptionist.bookings.check-out', $booking) }}" data-loading>
                @csrf
                <button type="submit" class="btn-outline btn-sm">{{ __('Check Out') }}</button>
            </form>
        </div>
        @empty
        <p class="p-5 text-sm text-slate-500">{{ __('No departures scheduled today.') }}</p>
        @endforelse
    </div>
</div>

</div>{{-- end x-data --}}

@push('scripts')
<script>
function receptionistDashboard() {
    return {
        stats: {
            arrivals_today:   {{ $stats['arrivals_today'] }},
            departures_today: {{ $stats['departures_today'] }},
            pending_confirm:  {{ $stats['pending_confirm'] }},
            currently_in:     {{ $stats['currently_in'] }},
        },
        loading:   false,
        refreshIn: 30,
        _timer:    null,
        _tick:     null,

        init() {
            this._tick = setInterval(() => {
                this.refreshIn--;
                if (this.refreshIn <= 0) {
                    this.refreshIn = 30;
                    this.fetchStats();
                }
            }, 1000);

            // Pause countdown when tab is hidden, resume when visible
            document.addEventListener('visibilitychange', () => {
                if (document.hidden) {
                    clearInterval(this._tick);
                } else {
                    this.fetchStats(); // immediate refresh on tab focus
                    this.refreshIn = 30;
                    this._tick = setInterval(() => {
                        this.refreshIn--;
                        if (this.refreshIn <= 0) { this.refreshIn = 30; this.fetchStats(); }
                    }, 1000);
                }
            });
        },

        async fetchStats() {
            this.loading = true;
            try {
                const res  = await fetch(window.location.href, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (!res.ok) return;
                const data = await res.json();
                this.stats = data;
            } catch (_) { /* silently ignore */ } finally {
                this.loading = false;
            }
        }
    };
}
</script>
@endpush

@endsection
