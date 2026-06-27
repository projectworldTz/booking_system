@extends('layouts.owner')
@section('title', 'Premium Features — ' . $hotel->name)
@section('page-title', 'Premium Features')

@section('content')

{{-- Header --}}
<div class="mb-6 flex flex-wrap items-start justify-between gap-4">
    <div>
        <h2 class="text-xl font-bold text-slate-900 dark:text-white">{{ $hotel->name }}</h2>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
            Browse available premium features. Request access from the platform admin — once approved, the feature activates instantly for your hotel.
        </p>
    </div>
    <a href="{{ route('owner.hotels.show', $hotel) }}" class="btn-ghost btn-sm">← Back to Hotel</a>
</div>

@if(session('success'))
<div class="mb-5 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 px-4 py-3 flex items-start gap-3">
    <svg class="h-5 w-5 text-emerald-500 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    <p class="text-sm text-emerald-700 dark:text-emerald-300">{{ session('success') }}</p>
</div>
@endif

@if(session('info'))
<div class="mb-5 rounded-xl bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 px-4 py-3 flex items-start gap-3">
    <svg class="h-5 w-5 text-blue-500 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    <p class="text-sm text-blue-700 dark:text-blue-300">{{ session('info') }}</p>
</div>
@endif

{{-- Feature tiers --}}
@php
    $tierColors = [
        'Growth'     => ['ring' => 'ring-emerald-200 dark:ring-emerald-700',  'badge' => 'bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300', 'dot' => 'bg-emerald-500'],
        'Operations' => ['ring' => 'ring-blue-200 dark:ring-blue-700',         'badge' => 'bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300',           'dot' => 'bg-blue-500'],
        'Revenue'    => ['ring' => 'ring-purple-200 dark:ring-purple-700',     'badge' => 'bg-purple-100 dark:bg-purple-900/40 text-purple-700 dark:text-purple-300',   'dot' => 'bg-purple-500'],
        'Premium'    => ['ring' => 'ring-amber-200 dark:ring-amber-700',       'badge' => 'bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300',       'dot' => 'bg-amber-500'],
    ];
@endphp

@foreach($grouped as $tier => $features)
@php $tc = $tierColors[$tier] ?? $tierColors['Growth']; @endphp

<div class="mb-8">
    {{-- Tier heading --}}
    <div class="flex items-center gap-3 mb-4">
        <span class="h-2.5 w-2.5 rounded-full {{ $tc['dot'] }}"></span>
        <h3 class="text-sm font-bold uppercase tracking-widest text-slate-500 dark:text-slate-400">{{ $tier }} Tier</h3>
        <div class="flex-1 h-px bg-slate-200 dark:bg-slate-700"></div>
        <span class="text-xs {{ $tc['badge'] }} px-2.5 py-0.5 rounded-full font-semibold">{{ count($features) }} features</span>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @foreach($features as $feature)
        @php
            $isGranted  = isset($granted[$feature->value]) && $granted[$feature->value]->isActive();
            $req        = $requests[$feature->value] ?? null;
            $isPending  = $req && $req->isPending();
            $isDenied   = $req && $req->isDenied();
        @endphp

        <div class="relative rounded-2xl border bg-white dark:bg-slate-800 p-5 transition
            {{ $isGranted
                ? 'border-emerald-200 dark:border-emerald-700 ring-1 ring-emerald-100 dark:ring-emerald-900/40'
                : 'border-slate-200 dark:border-slate-700' }}">

            {{-- Status badge top-right --}}
            <div class="absolute top-3 right-3">
                @if($isGranted)
                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 dark:bg-emerald-900/50 px-2.5 py-0.5 text-xs font-bold text-emerald-700 dark:text-emerald-300">
                        <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        Active
                    </span>
                @elseif($isPending)
                    <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 dark:bg-amber-900/50 px-2.5 py-0.5 text-xs font-bold text-amber-700 dark:text-amber-300">
                        <svg class="h-3 w-3 animate-pulse" fill="currentColor" viewBox="0 0 20 20"><circle cx="10" cy="10" r="4"/></svg>
                        Pending
                    </span>
                @elseif($isDenied)
                    <span class="inline-flex items-center gap-1 rounded-full bg-rose-100 dark:bg-rose-900/50 px-2.5 py-0.5 text-xs font-bold text-rose-600 dark:text-rose-400">
                        <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                        Denied
                    </span>
                @else
                    <span class="{{ $tc['badge'] }} rounded-full px-2.5 py-0.5 text-xs font-semibold">
                        {{ $tier }}
                    </span>
                @endif
            </div>

            {{-- Lock icon (not granted) --}}
            @if(!$isGranted)
            <div class="mb-3">
                <div class="h-9 w-9 rounded-xl {{ $tc['badge'] }} flex items-center justify-center">
                    <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
            </div>
            @else
            <div class="mb-3">
                <div class="h-9 w-9 rounded-xl bg-emerald-100 dark:bg-emerald-900/40 flex items-center justify-center">
                    <svg class="h-4.5 w-4.5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/>
                    </svg>
                </div>
            </div>
            @endif

            {{-- Name & description --}}
            <h4 class="font-bold text-sm text-slate-900 dark:text-white pr-16">{{ $feature->label() }}</h4>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400 leading-relaxed">{{ $feature->description() }}</p>

            {{-- Granted info --}}
            @if($isGranted)
                @php $hf = $granted[$feature->value]; @endphp
                <div class="mt-3 pt-3 border-t border-slate-100 dark:border-slate-700 text-xs text-slate-400">
                    Granted {{ $hf->granted_at?->diffForHumans() }}
                    @if($hf->expires_at) · expires {{ $hf->expires_at->format('d M Y') }} @endif
                </div>

            {{-- Pending request --}}
            @elseif($isPending)
                <div class="mt-3 pt-3 border-t border-slate-100 dark:border-slate-700 text-xs text-amber-600 dark:text-amber-400">
                    Request submitted {{ $req->created_at->diffForHumans() }} — awaiting admin review.
                </div>

            {{-- Denied — show reason + allow re-request --}}
            @elseif($isDenied)
                @if($req->admin_notes)
                <div class="mt-3 pt-3 border-t border-slate-100 dark:border-slate-700 text-xs text-rose-500 dark:text-rose-400">
                    Admin note: {{ $req->admin_notes }}
                </div>
                @endif
                <div class="mt-3" x-data="{ open: false }">
                    <button @click="open = !open"
                        class="text-xs font-semibold text-navy dark:text-navy-light hover:underline">
                        Request again →
                    </button>
                    <div x-show="open" x-cloak class="mt-2">
                        <form method="POST" action="{{ route('owner.hotels.features.request', $hotel) }}">
                            @csrf
                            <input type="hidden" name="feature" value="{{ $feature->value }}">
                            <textarea name="message" rows="2"
                                placeholder="Briefly describe your use case…"
                                class="form-input text-xs w-full resize-none"></textarea>
                            <button type="submit" class="btn-primary btn-sm mt-2 w-full text-center">Send Request</button>
                        </form>
                    </div>
                </div>

            {{-- Not requested yet --}}
            @else
                <div class="mt-3" x-data="{ open: false }">
                    <button @click="open = !open"
                        class="inline-flex items-center gap-1.5 text-xs font-semibold text-navy dark:text-navy-light hover:underline">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                        </svg>
                        Request Access
                    </button>
                    <div x-show="open" x-cloak x-transition class="mt-2">
                        <form method="POST" action="{{ route('owner.hotels.features.request', $hotel) }}">
                            @csrf
                            <input type="hidden" name="feature" value="{{ $feature->value }}">
                            <textarea name="message" rows="2"
                                placeholder="Optional: briefly describe how you plan to use this…"
                                class="form-input text-xs w-full resize-none"></textarea>
                            <button type="submit" class="btn-primary btn-sm mt-2 w-full text-center">Send Request</button>
                        </form>
                    </div>
                </div>
            @endif

        </div>
        @endforeach
    </div>
</div>
@endforeach

@endsection
