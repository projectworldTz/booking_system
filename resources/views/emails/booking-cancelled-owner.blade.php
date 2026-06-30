<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Guest Cancellation — Action Required</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif; font-size: 15px; line-height: 1.6; color: #334155; background: #f1f5f9; }
        .wrapper { max-width: 600px; margin: 32px auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 16px rgba(0,0,0,0.08); }
        .header { background: linear-gradient(135deg, #92400e 0%, #d97706 100%); padding: 40px 40px 32px; text-align: center; }
        .header h1 { color: #ffffff; font-size: 24px; font-weight: 700; margin-bottom: 4px; }
        .header p { color: rgba(255,255,255,0.85); font-size: 14px; }
        .badge-number { display: inline-block; background: rgba(255,255,255,0.2); color: #fff; padding: 4px 14px; border-radius: 999px; font-size: 13px; font-weight: 700; font-family: monospace; margin-top: 10px; }
        .body { padding: 36px 40px; }
        .greeting { font-size: 17px; font-weight: 600; color: #1e293b; margin-bottom: 12px; }
        .para { color: #475569; margin-bottom: 20px; font-size: 14px; }
        .section-title { font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: #94a3b8; margin-bottom: 12px; }
        .detail-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 20px; margin-bottom: 24px; }
        .detail-row { display: flex; justify-content: space-between; align-items: center; padding: 6px 0; border-bottom: 1px solid #e2e8f0; font-size: 14px; }
        .detail-row:last-child { border-bottom: none; padding-bottom: 0; }
        .detail-label { color: #64748b; }
        .detail-value { font-weight: 600; color: #1e293b; text-align: right; }
        .action-box { background: #fff7ed; border: 2px solid #f59e0b; border-radius: 10px; padding: 20px 24px; margin-bottom: 24px; }
        .action-box h3 { color: #92400e; font-size: 15px; font-weight: 700; margin-bottom: 8px; }
        .action-box p { color: #78350f; font-size: 14px; margin-bottom: 8px; }
        .action-box ul { color: #78350f; font-size: 14px; padding-left: 20px; }
        .action-box ul li { margin-bottom: 4px; }
        .footer { background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 24px 40px; text-align: center; font-size: 12px; color: #94a3b8; }
        .footer a { color: #1B3A6B; text-decoration: none; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="header">
        <div style="width:56px;height:56px;background:rgba(255,255,255,0.2);border-radius:50%;margin:0 auto 16px;display:flex;align-items:center;justify-content:center;">
            <svg width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M12 3a9 9 0 110 18A9 9 0 0112 3z"/>
            </svg>
        </div>
        <h1>Guest Cancellation — Action Required</h1>
        <p>A guest has cancelled their booking and a refund is due</p>
        <div class="badge-number">#{{ $booking->booking_number }}</div>
    </div>

    <div class="body">
        <p class="greeting">Hi {{ $booking->hotel->owner->name ?? 'Property Manager' }},</p>
        <p class="para">
            A guest has cancelled their booking at <strong>{{ $booking->hotel->name }}</strong>.
            Based on our platform cancellation policy, a refund must be issued manually via mobile money.
        </p>

        @if($booking->refund_amount > 0)
        <div class="action-box">
            <h3>&#9888; Manual Refund Required</h3>
            <p>Please transfer the following amount to the guest's mobile money account:</p>
            <ul>
                <li><strong>Amount:</strong> {{ $booking->currency }} {{ number_format($booking->refund_amount, 2) }}</li>
                <li><strong>Guest Name:</strong> {{ $booking->user->name }}</li>
                <li><strong>Payment Method:</strong> {{ $booking->payment?->method_label ?? 'N/A' }}</li>
                <li><strong>Guest Phone:</strong> {{ $booking->payment?->metadata['phone'] ?? 'Check payment records' }}</li>
                <li><strong>Reference:</strong> Booking #{{ $booking->booking_number }}</li>
            </ul>
            <p style="margin-top: 10px; font-size: 13px;">
                Please complete the transfer within <strong>2–3 business days</strong> and notify us
                once done by replying to this email or updating the booking in your dashboard.
            </p>
        </div>
        @endif

        <p class="section-title">Cancelled Booking Details</p>
        <div class="detail-box">
            <div class="detail-row">
                <span class="detail-label">Booking #</span>
                <span class="detail-value">{{ $booking->booking_number }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Guest</span>
                <span class="detail-value">{{ $booking->user->name }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Check-in</span>
                <span class="detail-value">{{ \Carbon\Carbon::parse($booking->check_in)->format('D, d M Y') }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Check-out</span>
                <span class="detail-value">{{ \Carbon\Carbon::parse($booking->check_out)->format('D, d M Y') }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Total Booking Value</span>
                <span class="detail-value">{{ $booking->currency }} {{ number_format($booking->grand_total, 2) }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Refund to Guest</span>
                <span class="detail-value" style="color:{{ $booking->refund_amount > 0 ? '#dc2626' : '#16a34a' }}">
                    {{ $booking->currency }} {{ number_format($booking->refund_amount ?? 0, 2) }}
                    ({{ $booking->refund_amount > 0 ? 'manual transfer required' : 'no refund — within 24h' }})
                </span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Cancelled On</span>
                <span class="detail-value">{{ ($booking->cancelled_at ?? now())->format('d M Y, H:i') }}</span>
            </div>
            @if($booking->cancellation_reason)
            <div class="detail-row">
                <span class="detail-label">Reason</span>
                <span class="detail-value" style="max-width:300px;word-break:break-word;">{{ $booking->cancellation_reason }}</span>
            </div>
            @endif
        </div>

        <p class="para" style="font-size: 13px; color: #64748b;">
            The room has been automatically released and is now available for rebooking.
            If you have questions about this cancellation, contact us at
            <a href="mailto:{{ config('mail.from.address') }}" style="color: #1B3A6B;">{{ config('mail.from.address') }}</a>.
        </p>
    </div>

    <div class="footer">
        <p style="margin-bottom: 8px;">
            <strong style="color: #1B3A6B;">{{ config('app.name') }}</strong>
        </p>
        <p>This notification was sent because a guest cancelled a booking at your property.</p>
    </div>
</div>
</body>
</html>
