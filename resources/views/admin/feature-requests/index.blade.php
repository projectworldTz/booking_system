@extends('layouts.admin')
@section('title', 'Feature Access Requests')
@section('page-title', 'Feature Access Requests')

@section('content')

{{-- Status filter tabs --}}
<div class="mb-5 flex flex-wrap items-center gap-2">
    @foreach(['pending' => 'Pending', 'approved' => 'Approved', 'denied' => 'Denied', 'all' => 'All'] as $val => $label)
    <a href="{{ route('admin.feature-requests.index', ['status' => $val]) }}"
       class="btn-sm {{ $status === $val ? 'btn-primary' : 'btn-ghost' }}">
        {{ $label }}
        @if($val === 'pending' && $pendingCount > 0)
            <span class="ml-1 inline-flex items-center justify-center h-4 w-4 rounded-full bg-white/20 text-[10px] font-bold">{{ $pendingCount }}</span>
        @endif
    </a>
    @endforeach
</div>

@if(session('success'))
<div class="mb-4 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-700 px-4 py-3 text-sm text-emerald-700 dark:text-emerald-300">
    {{ session('success') }}
</div>
@endif

<div class="space-y-4">
    @forelse($requests as $req)
    @php
        $tierColor = match($req->feature->tierColor()) {
            'emerald' => 'bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300',
            'blue'    => 'bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300',
            'purple'  => 'bg-purple-100 dark:bg-purple-900/40 text-purple-700 dark:text-purple-300',
            'amber'   => 'bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300',
            default   => 'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300',
        };
    @endphp

    <div class="card p-5" x-data="{ reviewing: false }">
        <div class="flex flex-wrap items-start justify-between gap-4">

            {{-- Left: Feature + Hotel info --}}
            <div class="flex-1 min-w-0">
                <div class="flex flex-wrap items-center gap-2 mb-1">
                    <span class="text-[11px] font-semibold {{ $tierColor }} px-2 py-0.5 rounded-full">{{ $req->feature->tier() }}</span>
                    <h3 class="font-bold text-slate-900 dark:text-white">{{ $req->feature->label() }}</h3>
                    <span class="badge badge-{{ $req->status === 'pending' ? 'pending' : ($req->status === 'approved' ? 'confirmed' : 'cancelled') }}">
                        {{ ucfirst($req->status) }}
                    </span>
                </div>

                <p class="text-xs text-slate-500 dark:text-slate-400 mb-2">{{ $req->feature->description() }}</p>

                <div class="flex flex-wrap gap-4 text-xs text-slate-500 dark:text-slate-400">
                    <span class="flex items-center gap-1">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5"/>
                        </svg>
                        <a href="{{ route('admin.hotels.show', $req->hotel) }}" class="font-semibold text-navy dark:text-navy-light hover:underline">
                            {{ $req->hotel->name }}
                        </a>
                        <span class="text-slate-400">({{ $req->hotel->city ?? '' }})</span>
                    </span>
                    <span class="flex items-center gap-1">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        {{ $req->requestedBy->name ?? 'Unknown' }}
                        &lt;{{ $req->requestedBy->email ?? '' }}&gt;
                    </span>
                    <span>Requested {{ $req->created_at->diffForHumans() }}</span>
                    @if($req->reviewed_at)
                    <span>· Reviewed {{ $req->reviewed_at->diffForHumans() }} by {{ $req->reviewedBy->name ?? 'Admin' }}</span>
                    @endif
                </div>

                @if($req->message)
                <div class="mt-3 rounded-lg bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 px-3 py-2 text-xs text-slate-600 dark:text-slate-300 italic">
                    "{{ $req->message }}"
                </div>
                @endif

                @if($req->admin_notes && !$req->isPending())
                <div class="mt-2 rounded-lg bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 px-3 py-2 text-xs text-blue-700 dark:text-blue-300">
                    <span class="font-semibold">Admin note:</span> {{ $req->admin_notes }}
                </div>
                @endif
            </div>

            {{-- Right: Actions --}}
            @if($req->isPending())
            <div class="flex flex-col gap-2 shrink-0">
                <button @click="reviewing = !reviewing"
                    class="btn-primary btn-sm">
                    Review
                </button>
            </div>
            @endif
        </div>

        {{-- Inline review form --}}
        @if($req->isPending())
        <div x-show="reviewing" x-cloak x-transition
             class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-700">
            <p class="text-sm font-semibold text-slate-700 dark:text-slate-200 mb-3">Review this request</p>
            <div class="grid sm:grid-cols-2 gap-3">

                {{-- Approve --}}
                <form method="POST" action="{{ route('admin.feature-requests.approve', $req) }}">
                    @csrf
                    <textarea name="admin_notes" rows="2"
                        placeholder="Optional note to the owner (reason, expiry, conditions…)"
                        class="form-input text-sm w-full resize-none mb-2"></textarea>
                    <button type="submit"
                        onclick="return confirm('Grant {{ $req->feature->label() }} to {{ $req->hotel->name }}?')"
                        class="btn-success btn-sm w-full">
                        Approve & Activate Feature
                    </button>
                </form>

                {{-- Deny --}}
                <form method="POST" action="{{ route('admin.feature-requests.deny', $req) }}">
                    @csrf
                    <textarea name="admin_notes" rows="2"
                        placeholder="Reason for denial (shown to the owner)…"
                        class="form-input text-sm w-full resize-none mb-2"></textarea>
                    <button type="submit" class="btn-danger btn-sm w-full">
                        Deny Request
                    </button>
                </form>
            </div>
        </div>
        @endif
    </div>
    @empty
    <div class="card flex flex-col items-center justify-center py-16 text-center">
        <svg class="h-12 w-12 text-slate-300 dark:text-slate-600 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
        </svg>
        <p class="text-slate-500 dark:text-slate-400">No {{ $status !== 'all' ? $status : '' }} feature requests.</p>
    </div>
    @endforelse
</div>

@if($requests->hasPages())
<div class="mt-5">{{ $requests->withQueryString()->links() }}</div>
@endif

@endsection
