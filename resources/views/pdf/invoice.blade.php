<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice?->invoice_number ?? $booking->booking_number }}</title>
    <style>
        /* ── Reset & Base ───────────────────────────────────── */
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'DejaVu Sans', 'Helvetica Neue', Arial, sans-serif;
            font-size: 11px;
            color: #1a1a2e;
            line-height: 1.55;
            background: #fff;
        }

        @page {
            size: A4 portrait;
            margin: 0;
        }

        .page {
            width: 210mm;
            min-height: 297mm;
            padding: 0;
            background: #fff;
        }

        /* ── Status Badges ──────────────────────────────────── */
        .status-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .status-paid      { background-color: #D1FAE5; color: #065F46; }
        .status-issued    { background-color: #DBEAFE; color: #1E40AF; }
        .status-draft     { background-color: #F3F4F6; color: #374151; }
        .status-cancelled { background-color: #FEE2E2; color: #991B1B; }
        .status-pending   { background-color: #FEF9C3; color: #854D0E; }
        .status-confirmed { background-color: #D1FAE5; color: #065F46; }
        .status-checked_in  { background-color: #DBEAFE; color: #1E40AF; }
        .status-checked_out { background-color: #F3F4F6; color: #374151; }
        .status-no_show   { background-color: #FCE7F3; color: #9D174D; }
        .status-refunded  { background-color: #EDE9FE; color: #5B21B6; }

        /* ── Header Band ────────────────────────────────────── */
        .header-band {
            background-color: #0F2147;
            padding: 22px 32px 20px;
            color: #fff;
        }
        .header-table { width: 100%; border-collapse: collapse; }
        .header-table td { vertical-align: top; padding: 0; }

        .hotel-badge {
            display: inline-block;
            width: 52px; height: 52px;
            background-color: #C9A227;
            border-radius: 10px;
            text-align: center;
            line-height: 52px;
            font-size: 22px;
            font-weight: bold;
            color: #0F2147;
        }
        .hotel-name    { font-size: 18px; font-weight: bold; color: #FFFFFF; line-height: 1.2; }
        .hotel-stars   { color: #C9A227; font-size: 11px; margin-top: 3px; }
        .hotel-tagline { font-size: 9.5px; color: rgba(255,255,255,0.6); margin-top: 2px; }
        .hotel-contact { font-size: 9px; color: rgba(255,255,255,0.7); margin-top: 8px; line-height: 1.8; }

        .invoice-meta-box {
            background-color: rgba(255,255,255,0.08);
            border-radius: 8px;
            padding: 14px 18px;
        }
        .invoice-word   { font-size: 22px; font-weight: bold; color: #C9A227; letter-spacing: 0.1em; text-transform: uppercase; }
        .invoice-number { font-size: 11px; color: rgba(255,255,255,0.9); font-weight: bold; margin-top: 4px; }

        /* ── Accent stripe ──────────────────────────────────── */
        .accent-stripe { height: 4px; background-color: #C9A227; }

        /* ── Body ───────────────────────────────────────────── */
        .body-pad { padding: 20px 32px; }

        .section-heading {
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: #0F2147;
            border-bottom: 1.5px solid #0F2147;
            padding-bottom: 4px;
            margin-bottom: 10px;
        }

        /* ── Info panels ────────────────────────────────────── */
        .panel {
            background-color: #F5F7FA;
            border-radius: 8px;
            padding: 13px 15px;
        }
        .panel-label { color: #6B7280; font-size: 9px; }
        .panel-value { font-weight: bold; font-size: 10.5px; color: #1a1a2e; margin-top: 1px; margin-bottom: 6px; }

        /* ── Charges table ──────────────────────────────────── */
        .charges-table { width: 100%; border-collapse: collapse; margin-bottom: 4px; }
        .charges-table thead tr { background-color: #0F2147; color: #fff; }
        .charges-table thead th {
            padding: 9px 12px;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            font-weight: bold;
        }
        .charges-table thead th:first-child { text-align: left; }
        .charges-table thead th:not(:first-child) { text-align: right; }
        .charges-table tbody tr { border-bottom: 1px solid #E5E7EB; }
        .charges-table tbody tr:nth-child(even) { background-color: #F9FAFB; }
        .charges-table tbody td { padding: 9px 12px; font-size: 10.5px; vertical-align: top; }
        .charges-table tbody td:not(:first-child) { text-align: right; }

        /* ── Totals ─────────────────────────────────────────── */
        .totals-inner { width: 100%; border-collapse: collapse; }
        .totals-inner td { padding: 3.5px 12px; font-size: 10.5px; }
        .t-label { color: #374151; }
        .t-value { text-align: right; font-weight: bold; }

        .grand-total-bg { background-color: #0F2147; border-radius: 6px; }
        .grand-total-bg .t-label { color: #FFFFFF; font-size: 12px; padding: 9px 12px; }
        .grand-total-bg .t-value { color: #C9A227; font-size: 12px; padding: 9px 12px; }

        /* ── Payment box ────────────────────────────────────── */
        .payment-panel {
            background-color: #F5F7FA;
            border-radius: 8px;
            padding: 12px 15px;
            margin-bottom: 0;
        }

        /* ── Cancellation box ───────────────────────────────── */
        .cancellation-box {
            border: 1.5px solid #FCA5A5;
            border-radius: 8px;
            padding: 13px 16px;
            margin-top: 14px;
            background-color: #FFF7F7;
        }
        .c-heading { font-size: 9px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.08em; color: #B91C1C; margin-bottom: 9px; }
        .c-table { width: 100%; border-collapse: collapse; }
        .c-table td { font-size: 10.5px; padding: 3px 0; }
        .c-val { text-align: right; font-weight: bold; }

        /* ── Footer ─────────────────────────────────────────── */
        .footer-band { background-color: #0F2147; padding: 16px 32px; }
        .footer-table { width: 100%; border-collapse: collapse; }
        .footer-table td { vertical-align: middle; padding: 0; }
        .footer-thank   { font-size: 12px; font-weight: bold; color: #C9A227; }
        .footer-contact { font-size: 9px; color: rgba(255,255,255,0.65); margin-top: 5px; line-height: 1.9; }
        .footer-tnc     { font-size: 8px; color: rgba(255,255,255,0.4); margin-top: 8px; line-height: 1.6; }

        @media print {
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>
@php
    $invoice  ??= $booking->invoice;
    $hotel     = $booking->hotel;
    $guest     = $booking->user;
    $payment   = $booking->payment;
    $rooms     = $booking->rooms;

    // Hotel initials for badge
    $words    = array_filter(explode(' ', $hotel->name));
    $initials = implode('', array_map(fn($w) => strtoupper($w[0]), array_slice($words, 0, 2)));

    // Stars
    $stars    = str_repeat('★', (int) $hotel->star_rating) . str_repeat('☆', max(0, 5 - (int) $hotel->star_rating));

    // Invoice status
    $invoiceStatus = $invoice?->status ?? ($booking->status === 'cancelled' ? 'cancelled' : 'issued');
    $statusLabel   = match($invoiceStatus) {
        'paid'      => 'PAID',
        'issued'    => 'ISSUED',
        'draft'     => 'DRAFT',
        'cancelled' => 'CANCELLED',
        default     => strtoupper($invoiceStatus),
    };
    $statusClass = 'status-' . $invoiceStatus;

    // Amounts
    $subtotal      = (float) ($invoice?->subtotal       ?? $booking->sub_total    ?? 0);
    $taxTotal      = (float) ($invoice?->tax_total      ?? $booking->tax_total    ?? 0);
    $discountTotal = (float) ($invoice?->discount_total ?? $booking->discount_total ?? 0);
    $grandTotal    = (float) ($invoice?->grand_total    ?? $booking->grand_total  ?? 0);
    $taxRate       = (float) ($booking->tax_rate ?? 0);
    $currency      = $booking->currency ?? 'TZS';

    $amountPaid = (float) ($payment?->amount ?? ($invoiceStatus === 'paid' ? $grandTotal : 0));
    $balanceDue = max(0, $grandTotal - $amountPaid);

    $isCancelled = $invoice && $invoice->isCancelled() && $invoice->cancellation_deduction;

    $issuedDate = ($invoice?->issued_at ?? $booking->confirmed_at ?? $booking->created_at)?->format('d M Y');
@endphp

<div class="page">

{{-- ══════════════ HEADER ══════════════ --}}
<div class="header-band">
    <table class="header-table">
        <tr>
            {{-- Left: Hotel branding --}}
            <td style="width:58%;">
                <table style="border-collapse:collapse; width:100%;">
                    <tr>
                        <td style="width:60px; vertical-align:middle; padding-right:14px; padding-bottom:0;">
                            <div class="hotel-badge">{{ $initials }}</div>
                        </td>
                        <td style="vertical-align:middle;">
                            <div class="hotel-name">{{ $hotel->name }}</div>
                            <div class="hotel-stars">{{ $stars }}</div>
                            <div class="hotel-tagline">
                                {{ implode(' · ', array_filter([$hotel->city, $hotel->state, $hotel->country])) }}
                            </div>
                        </td>
                    </tr>
                </table>
                <div class="hotel-contact" style="margin-top:10px; padding-left:74px;">
                    @if($hotel->address)
                    {{ $hotel->address }}{{ $hotel->city ? ', '.$hotel->city : '' }}{{ $hotel->postal_code ? ' '.$hotel->postal_code : '' }}<br>
                    @endif
                    @if($hotel->phone)&#9990;&nbsp;{{ $hotel->phone }}@endif
                    @if($hotel->phone && $hotel->email)&nbsp; &nbsp;@endif
                    @if($hotel->email)&#9993;&nbsp;{{ $hotel->email }}@endif
                    @if($hotel->website)
                    <br>&#127760;&nbsp;{{ $hotel->website }}
                    @endif
                </div>
            </td>

            {{-- Right: Invoice meta --}}
            <td style="width:42%; vertical-align:top;">
                <div class="invoice-meta-box">
                    <div class="invoice-word">Invoice</div>
                    <div class="invoice-number">
                        @if($invoice){{ $invoice->invoice_number }}@else{{ $booking->booking_number }}@endif
                    </div>
                    <table style="width:100%; border-collapse:collapse; margin-top:10px;">
                        <tr>
                            <td style="font-size:9px; color:rgba(255,255,255,0.5);">DATE ISSUED</td>
                            <td style="font-size:9px; color:rgba(255,255,255,0.9); font-weight:bold; text-align:right;">{{ $issuedDate }}</td>
                        </tr>
                        <tr>
                            <td style="font-size:9px; color:rgba(255,255,255,0.5);">BOOKING REF</td>
                            <td style="font-size:9px; color:rgba(255,255,255,0.9); font-weight:bold; text-align:right;">{{ $booking->booking_number }}</td>
                        </tr>
                        <tr>
                            <td style="font-size:9px; color:rgba(255,255,255,0.5); vertical-align:middle; padding-top:5px;">STATUS</td>
                            <td style="text-align:right; padding-top:5px;">
                                <span class="status-badge {{ $statusClass }}">{{ $statusLabel }}</span>
                            </td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>
</div>

{{-- Gold accent stripe --}}
<div class="accent-stripe"></div>

{{-- ══════════════ BODY ══════════════ --}}
<div class="body-pad">

    {{-- Guest + Stay panels (two columns) --}}
    <table style="width:100%; border-collapse:collapse; margin-bottom:16px;">
        <tr>
            <td style="width:49%; vertical-align:top;">
                <div class="panel">
                    <div class="section-heading">Billed To</div>
                    <div class="panel-label">Guest Name</div>
                    <div class="panel-value">{{ $guest?->name ?? '—' }}</div>

                    @if($guest?->email)
                    <div class="panel-label">Email</div>
                    <div class="panel-value" style="font-size:10px;">{{ $guest->email }}</div>
                    @endif

                    @if($guest?->phone)
                    <div class="panel-label">Phone</div>
                    <div class="panel-value">{{ $guest->phone }}</div>
                    @endif

                    @if($booking->special_requests)
                    <div style="margin-top:6px; padding-top:6px; border-top:1px solid #E5E7EB;">
                        <div class="panel-label">Special Requests</div>
                        <div style="font-size:9px; color:#374151; margin-top:2px; font-style:italic;">{{ Str::limit($booking->special_requests, 110) }}</div>
                    </div>
                    @endif
                </div>
            </td>

            <td style="width:2%;"></td>

            <td style="width:49%; vertical-align:top;">
                <div class="panel">
                    <div class="section-heading">Stay Details</div>

                    @foreach($rooms as $item)
                    <div class="panel-label">Room Type</div>
                    <div class="panel-value">{{ $item->roomType?->name ?? '—' }}</div>
                    @if($item->room?->room_number)
                    <div class="panel-label">Room Number</div>
                    <div class="panel-value">{{ $item->room->room_number }}
                        @if($item->room->floor)<span style="font-size:9px; font-weight:normal; color:#6B7280;">&nbsp;(Floor {{ $item->room->floor }})</span>@endif
                    </div>
                    @endif
                    @endforeach

                    <div class="panel-label">Check-in</div>
                    <div class="panel-value">{{ $booking->check_in?->format('D, d M Y') }}
                        <span style="font-size:9px; font-weight:normal; color:#6B7280;">from {{ $hotel->check_in_time ?? '14:00' }}</span>
                    </div>

                    <div class="panel-label">Check-out</div>
                    <div class="panel-value">{{ $booking->check_out?->format('D, d M Y') }}
                        <span style="font-size:9px; font-weight:normal; color:#6B7280;">by {{ $hotel->check_out_time ?? '11:00' }}</span>
                    </div>

                    <div class="panel-label">Duration / Guests</div>
                    <div class="panel-value">
                        {{ $booking->nights }} Night{{ $booking->nights != 1 ? 's' : '' }}
                        &nbsp;·&nbsp;
                        {{ $booking->guests_adults ?? 1 }} Adult{{ ($booking->guests_adults ?? 1) != 1 ? 's' : '' }}
                        @if($booking->guests_children ?? 0)
                        &nbsp;· {{ $booking->guests_children }} Child{{ $booking->guests_children != 1 ? 'ren' : '' }}
                        @endif
                    </div>
                </div>
            </td>
        </tr>
    </table>

    {{-- Charges Table --}}
    <div class="section-heading">Room Charges</div>
    <table class="charges-table" style="margin-bottom:16px;">
        <thead>
            <tr>
                <th style="text-align:left; width:38%;">Description</th>
                <th>Check-in</th>
                <th>Check-out</th>
                <th>Nights</th>
                <th>Rate / Night</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rooms as $item)
            <tr>
                <td>
                    <strong style="color:#0F2147;">{{ $item->roomType?->name ?? 'Room' }}</strong>
                    @if($item->room?->room_number)
                    <br><span style="font-size:9px; color:#6B7280;">Room {{ $item->room->room_number }}</span>
                    @endif
                </td>
                <td>{{ \Carbon\Carbon::parse($item->check_in)->format('d M Y') }}</td>
                <td>{{ \Carbon\Carbon::parse($item->check_out)->format('d M Y') }}</td>
                <td>{{ $item->nights }}</td>
                <td>{{ $currency }} {{ number_format((float) $item->nightly_rate, 0) }}</td>
                <td><strong>{{ $currency }} {{ number_format((float) $item->sub_total, 0) }}</strong></td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align:center; color:#9CA3AF; font-style:italic; padding:14px;">No room charges recorded.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Totals + Payment side-by-side --}}
    <table style="width:100%; border-collapse:collapse;">
        <tr>
            {{-- Left: Payment info --}}
            <td style="width:52%; vertical-align:top; padding-right:20px;">
                @if($payment)
                <div class="payment-panel">
                    <div class="section-heading" style="margin-bottom:10px;">Payment Information</div>
                    <table style="width:100%; border-collapse:collapse;">
                        <tr>
                            <td style="width:50%; vertical-align:top; padding-right:10px;">
                                <div class="panel-label">Payment Method</div>
                                <div style="font-weight:bold; font-size:11px; color:#0F2147; margin-top:2px;">
                                    {{ $booking->payment_method ? ucwords(str_replace('_', ' ', $booking->payment_method)) : '—' }}
                                </div>
                            </td>
                            <td style="width:50%; vertical-align:top;">
                                <div class="panel-label">Payment Status</div>
                                <div style="margin-top:3px;">
                                    <span class="status-badge status-{{ $payment->status ?? 'pending' }}">{{ strtoupper($payment->status ?? 'PENDING') }}</span>
                                </div>
                            </td>
                        </tr>
                    </table>
                    @if($payment->transaction_id)
                    <div style="margin-top:9px; padding-top:8px; border-top:1px solid #E5E7EB;">
                        <span class="panel-label">Transaction Reference:&nbsp;</span>
                        <span style="font-weight:bold; font-size:10px; color:#0F2147;">{{ $payment->transaction_id }}</span>
                    </div>
                    @endif
                </div>
                @endif

                <div style="font-size:8.5px; color:#9CA3AF; margin-top:8px;">
                    Generated: {{ now()->format('d M Y, H:i') }} &nbsp;·&nbsp; {{ config('app.name') }}
                </div>
            </td>

            {{-- Right: Totals --}}
            <td style="width:48%; vertical-align:top;">
                <table class="totals-inner">
                    <tr>
                        <td class="t-label">Subtotal</td>
                        <td class="t-value">{{ $currency }} {{ number_format($subtotal, 0) }}</td>
                    </tr>
                    @if($discountTotal > 0)
                    <tr style="color:#16A34A;">
                        <td>
                            Discount
                            @if($booking->coupon_code)
                            <span style="font-size:8.5px;">({{ $booking->coupon_code }})</span>
                            @endif
                        </td>
                        <td class="t-value">−{{ $currency }} {{ number_format($discountTotal, 0) }}</td>
                    </tr>
                    @endif
                    @if($taxTotal > 0)
                    <tr>
                        <td class="t-label">Tax{{ $taxRate > 0 ? ' ('.number_format($taxRate, 0).'%)' : '' }}</td>
                        <td class="t-value">{{ $currency }} {{ number_format($taxTotal, 0) }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td colspan="2" style="padding:1px 12px;">
                            <div style="height:1px; background-color:#E5E7EB;"></div>
                        </td>
                    </tr>
                    <tr class="grand-total-bg">
                        <td class="t-label" style="border-radius:6px 0 0 6px; font-size:12px; padding:9px 12px; color:#fff; font-weight:bold;">TOTAL</td>
                        <td class="t-value" style="border-radius:0 6px 6px 0; font-size:12px; padding:9px 12px; color:#C9A227; font-weight:bold; text-align:right;">{{ $currency }} {{ number_format($grandTotal, 0) }}</td>
                    </tr>
                    @if($amountPaid > 0)
                    <tr>
                        <td class="t-label" style="padding-top:7px;">Amount Paid</td>
                        <td class="t-value" style="padding-top:7px; color:#16A34A;">{{ $currency }} {{ number_format($amountPaid, 0) }}</td>
                    </tr>
                    @endif
                    @if($balanceDue > 0)
                    <tr>
                        <td class="t-label" style="color:#DC2626; font-weight:bold;">Balance Due</td>
                        <td class="t-value" style="color:#DC2626;">{{ $currency }} {{ number_format($balanceDue, 0) }}</td>
                    </tr>
                    @endif
                </table>
            </td>
        </tr>
    </table>

    {{-- Emergency Cancellation block --}}
    @if($isCancelled)
    <div class="cancellation-box">
        <div class="c-heading">&#9888; Emergency Cancellation — Partial Refund Applied</div>
        <table class="c-table">
            <tr>
                <td style="color:#6B7280;">Amount Originally Charged</td>
                <td class="c-val">{{ $currency }} {{ number_format($grandTotal, 0) }}</td>
            </tr>
            <tr style="color:#DC2626;">
                <td>Cancellation Deduction ({{ number_format((float) $invoice->deduction_percentage, 0) }}%)</td>
                <td class="c-val">−{{ $currency }} {{ number_format((float) $invoice->cancellation_deduction, 0) }}</td>
            </tr>
            <tr>
                <td colspan="2" style="padding:0;"><div style="height:1px; background-color:#FCA5A5; margin:5px 0;"></div></td>
            </tr>
            <tr style="color:#16A34A;">
                <td style="font-weight:bold; font-size:12px;">Refund Due to Guest ({{ number_format(100 - (float) $invoice->deduction_percentage, 0) }}%)</td>
                <td class="c-val" style="font-size:12px; color:#16A34A;">{{ $currency }} {{ number_format((float) $invoice->refund_amount, 0) }}</td>
            </tr>
        </table>
        @if($invoice->notes)
        <div style="font-size:8.5px; color:#B91C1C; margin-top:8px; padding-top:6px; border-top:1px dashed #FCA5A5;">{{ $invoice->notes }}</div>
        @endif
        @if($invoice->cancelled_at)
        <div style="font-size:8px; color:#9CA3AF; margin-top:3px;">Cancelled: {{ $invoice->cancelled_at->format('d M Y, H:i') }}</div>
        @endif
    </div>
    @endif

</div>{{-- /body-pad --}}

{{-- ══════════════ FOOTER ══════════════ --}}
<div class="footer-band">
    <table class="footer-table">
        <tr>
            <td style="width:72%; vertical-align:top;">
                <div class="footer-thank">Thank you for choosing {{ $hotel->name }}!</div>
                <div class="footer-contact">
                    @if($hotel->address){{ $hotel->address }}, {{ $hotel->city }}@if($hotel->country), {{ $hotel->country }}@endif<br>@endif
                    @if($hotel->phone)&#9990;&nbsp;{{ $hotel->phone }}@endif
                    @if($hotel->phone && $hotel->email) &nbsp;&nbsp; @endif
                    @if($hotel->email)&#9993;&nbsp;{{ $hotel->email }}@endif
                    @if($hotel->website)<br>&#127760;&nbsp;{{ $hotel->website }}@endif
                </div>
                <div class="footer-tnc">
                    {{ Str::limit($hotel->cancellation_policy ?? 'Cancellation subject to hotel policy.', 200) }}
                    &nbsp; This invoice is system-generated. For disputes contact the hotel directly.
                </div>
            </td>
            <td style="width:28%; text-align:right; vertical-align:middle;">
                {{-- Status stamp --}}
                <table style="border-collapse:collapse; margin-left:auto; width:74px; height:74px; border:2px solid rgba(201,162,39,0.45); border-radius:50%;">
                    <tr>
                        <td style="text-align:center; vertical-align:middle;">
                            @if(in_array($booking->status, ['checked_out', 'confirmed']))
                            <div style="font-size:22px; color:#C9A227; line-height:1;">&#10003;</div>
                            <div style="font-size:7.5px; font-weight:bold; text-transform:uppercase; color:#C9A227; letter-spacing:0.06em;">
                                {{ $booking->status === 'checked_out' ? 'Completed' : 'Confirmed' }}
                            </div>
                            @elseif($booking->status === 'cancelled')
                            <div style="font-size:22px; color:#EF4444; line-height:1;">&#10007;</div>
                            <div style="font-size:7.5px; font-weight:bold; text-transform:uppercase; color:#EF4444; letter-spacing:0.06em;">Cancelled</div>
                            @else
                            <div style="font-size:22px; color:#C9A227; line-height:1;">&#9733;</div>
                            <div style="font-size:7.5px; font-weight:bold; text-transform:uppercase; color:#C9A227; letter-spacing:0.06em;">{{ ucfirst(str_replace('_', ' ', $booking->status)) }}</div>
                            @endif
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</div>

</div>{{-- /page --}}
</body>
</html>
