@extends('layouts.app')
@section('title', 'Hotel Management Platform')

@section('content')

{{-- Hero --}}
<section class="relative overflow-hidden bg-gradient-to-br from-slate-900 via-navy to-slate-800 text-white">
    <div class="mx-auto max-w-7xl px-6 py-28 lg:py-36 text-center">
        <span class="inline-block rounded-full bg-white/10 px-4 py-1.5 text-sm font-medium tracking-wide mb-6">
            Multi-Hotel Management Platform
        </span>
        <h1 class="text-4xl font-extrabold tracking-tight sm:text-5xl lg:text-6xl leading-tight">
            Run your hotel smarter.<br class="hidden sm:block"> All in one place.
        </h1>
        <p class="mt-6 max-w-2xl mx-auto text-lg text-slate-300">
            A complete management system for hotel owners — bookings, rooms, staff,
            payments and reports under one roof.
        </p>
        <div class="mt-10 flex flex-wrap justify-center gap-4">
            @guest
                <a href="{{ route('register') }}"
                   class="inline-flex items-center gap-2 rounded-xl bg-white px-7 py-3.5 text-base font-semibold text-navy shadow-lg hover:bg-slate-100 transition">
                    Get started free
                </a>
                <a href="{{ route('login') }}"
                   class="inline-flex items-center gap-2 rounded-xl border border-white/30 px-7 py-3.5 text-base font-semibold hover:bg-white/10 transition">
                    Sign in
                </a>
            @else
                <a href="{{ route('dashboard') }}"
                   class="inline-flex items-center gap-2 rounded-xl bg-white px-7 py-3.5 text-base font-semibold text-navy shadow-lg hover:bg-slate-100 transition">
                    Go to dashboard &rarr;
                </a>
            @endguest
        </div>
    </div>
</section>

{{-- Features --}}
<section class="bg-white dark:bg-slate-900 py-20">
    <div class="mx-auto max-w-7xl px-6">
        <h2 class="text-center text-3xl font-bold text-slate-900 dark:text-white mb-14">
            Everything you need to run a hotel
        </h2>
        <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">

            @foreach([
                ['icon' => 'calendar',       'title' => 'Booking Management',  'desc' => 'Handle reservations end-to-end — availability checks to check-out and invoicing.'],
                ['icon' => 'building-office', 'title' => 'Room & Rate Control', 'desc' => 'Configure room types, seasonal pricing and availability calendars with ease.'],
                ['icon' => 'users',           'title' => 'Staff & Roles',       'desc' => 'Add receptionists, managers and cashiers scoped strictly to your hotel.'],
                ['icon' => 'credit-card',     'title' => 'Payments',            'desc' => 'Accept Stripe, PayPal, bank transfer or cash. Every transaction is tracked.'],
                ['icon' => 'chart-bar',       'title' => 'Reports & Revenue',   'desc' => 'Monthly revenue charts, occupancy rates and booking summaries at a glance.'],
                ['icon' => 'ticket',          'title' => 'Coupons & Offers',    'desc' => 'Create discount codes scoped to your hotel with flexible rules and expiry.'],
            ] as $f)
            <div class="rounded-2xl border border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 p-6">
                <div class="mb-4 flex h-11 w-11 items-center justify-center rounded-xl bg-navy/10 text-navy dark:bg-navy/20">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7">
                        @if($f['icon'] === 'calendar')
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        @elseif($f['icon'] === 'building-office')
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/>
                        @elseif($f['icon'] === 'users')
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/>
                        @elseif($f['icon'] === 'credit-card')
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z"/>
                        @elseif($f['icon'] === 'chart-bar')
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/>
                        @else
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 14.25l6-6m4.5-3.493V21.75l-3.75-1.5-3.75 1.5-3.75-1.5-3.75 1.5V4.757c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0111.186 0c1.1.128 1.907 1.077 1.907 2.185z"/>
                        @endif
                    </svg>
                </div>
                <h3 class="font-semibold text-slate-900 dark:text-white mb-1">{{ $f['title'] }}</h3>
                <p class="text-sm text-slate-600 dark:text-slate-400">{{ $f['desc'] }}</p>
            </div>
            @endforeach

        </div>
    </div>
</section>

{{-- CTA --}}
<section class="bg-navy py-16 text-center text-white">
    <div class="mx-auto max-w-2xl px-6">
        <h2 class="text-3xl font-bold mb-4">Ready to get started?</h2>
        <p class="text-slate-300 mb-8">Create your account and have your hotel set up in minutes.</p>
        @guest
            <a href="{{ route('register') }}"
               class="inline-flex items-center gap-2 rounded-xl bg-white px-8 py-3.5 text-base font-semibold text-navy hover:bg-slate-100 transition">
                Create a free account
            </a>
        @else
            <a href="{{ route('dashboard') }}"
               class="inline-flex items-center gap-2 rounded-xl bg-white px-8 py-3.5 text-base font-semibold text-navy hover:bg-slate-100 transition">
                Go to your dashboard &rarr;
            </a>
        @endguest
    </div>
</section>

@endsection
