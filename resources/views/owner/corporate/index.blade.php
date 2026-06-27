@extends('layouts.owner')
@section('title', 'Corporate Accounts — ' . $hotel->name)
@section('page-title', 'Corporate / B2B Portal')

@section('content')

<div class="mb-6 flex flex-wrap items-center justify-between gap-4">
    <div>
        <p class="text-sm text-slate-500 dark:text-slate-400">
            Manage B2B clients for <span class="font-semibold text-slate-700 dark:text-slate-200">{{ $hotel->name }}</span>.
            Each account gets a private portal link with negotiated rates.
        </p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('owner.hotels.show', $hotel) }}" class="btn-ghost btn-sm">← Hotel</a>
        <a href="{{ route('owner.hotels.corporate.create', $hotel) }}" class="btn-primary btn-sm">+ New Account</a>
    </div>
</div>

@if(session('success'))
<div class="mb-5 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 px-4 py-3 text-sm text-emerald-700 dark:text-emerald-300">
    {{ session('success') }}
</div>
@endif

@forelse($accounts as $account)
<div class="card mb-4 p-5">
    <div class="flex flex-wrap items-start justify-between gap-4">

        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 flex-wrap mb-1">
                <h3 class="font-bold text-slate-900 dark:text-white">{{ $account->company_name }}</h3>
                @if($account->is_active && $account->isContractActive())
                    <span class="badge badge-confirmed">Active</span>
                @elseif(!$account->is_active)
                    <span class="badge badge-cancelled">Inactive</span>
                @else
                    <span class="badge badge-pending">Expired</span>
                @endif
                <span class="text-xs text-slate-400 font-mono bg-slate-100 dark:bg-slate-700 px-2 py-0.5 rounded">
                    {{ $account->discount_label() }}
                </span>
            </div>

            <div class="flex flex-wrap gap-4 text-xs text-slate-500 dark:text-slate-400">
                @if($account->contact_name)
                <span>Contact: {{ $account->contact_name }}</span>
                @endif
                @if($account->contact_email)
                <span>{{ $account->contact_email }}</span>
                @endif
                <span>{{ $account->bookings_count }} booking{{ $account->bookings_count !== 1 ? 's' : '' }}</span>
                @if($account->contract_end)
                <span>Contract until {{ $account->contract_end->format('d M Y') }}</span>
                @endif
            </div>

            {{-- Portal link --}}
            <div class="mt-3 flex items-center gap-2">
                <span class="text-xs text-slate-400">Portal:</span>
                <code class="text-xs bg-slate-100 dark:bg-slate-700 px-2 py-0.5 rounded text-slate-600 dark:text-slate-300 truncate max-w-xs select-all">
                    {{ route('corporate.portal', [$hotel->slug, $account->access_code]) }}
                </code>
                <button
                    x-data
                    @click="
                        navigator.clipboard.writeText('{{ route('corporate.portal', [$hotel->slug, $account->access_code]) }}');
                        $el.textContent = 'Copied!';
                        setTimeout(() => $el.textContent = 'Copy', 1500)
                    "
                    class="btn-ghost btn-xs">Copy</button>
            </div>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            <a href="{{ route('owner.hotels.corporate.show', [$hotel, $account]) }}" class="btn-outline btn-sm">View</a>
            <a href="{{ route('owner.hotels.corporate.edit', [$hotel, $account]) }}" class="btn-ghost btn-sm">Edit</a>
        </div>
    </div>
</div>
@empty
<div class="card flex flex-col items-center justify-center py-20 text-center">
    <svg class="h-14 w-14 text-slate-200 dark:text-slate-700 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
        <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 00-1-1h-2a1 1 0 00-1 1v5m4 0H9"/>
    </svg>
    <p class="text-lg font-semibold text-slate-900 dark:text-white mb-1">No corporate accounts yet</p>
    <p class="text-sm text-slate-500 dark:text-slate-400 mb-5">Create an account to give a company a private booking portal with negotiated rates.</p>
    <a href="{{ route('owner.hotels.corporate.create', $hotel) }}" class="btn-primary btn-sm">+ Create First Account</a>
</div>
@endforelse

@endsection
