<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\BookingRoom;
use App\Models\Coupon;
use App\Models\Invoice;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TranquilooSeeder extends Seeder
{
    private const HOTEL_ID   = 2;
    private const TAX_RATE   = 0;
    private const CURRENCY   = 'TZS';

    // Rooms: KING1 = TZS 45,000  |  VIP = TZS 35,000
    private const ROOMS = [
        22 => ['number' => '101', 'type_id' => 4, 'rate' => 45000],
        23 => ['number' => '102', 'type_id' => 4, 'rate' => 45000],
        24 => ['number' => '103', 'type_id' => 4, 'rate' => 45000],
        25 => ['number' => '104', 'type_id' => 4, 'rate' => 45000],
        26 => ['number' => '105', 'type_id' => 4, 'rate' => 45000],
        27 => ['number' => '201', 'type_id' => 5, 'rate' => 35000],
        28 => ['number' => '202', 'type_id' => 5, 'rate' => 35000],
        29 => ['number' => '203', 'type_id' => 5, 'rate' => 35000],
    ];

    // Guest user IDs
    private const GUEST_1 = 3;  // Test Guest
    private const GUEST_2 = 5;  // kelvin kelvin
    private const GUEST_3 = 7;  // KELVIN

    public function run(): void
    {
        $today = Carbon::today();

        // ── 1. Coupon ─────────────────────────────────────────────────────────
        $coupon = Coupon::firstOrCreate(
            ['code' => 'TRAN-SUMMER'],
            [
                'hotel_id'           => self::HOTEL_ID,
                'room_type_id'       => null,
                'type'               => 'percentage',
                'value'              => 10,
                'min_booking_amount' => 50000,
                'uses'               => 3,
                'max_uses'           => 50,
                'expires_at'         => $today->copy()->addMonths(3),
                'active'             => true,
            ]
        );

        // ── 2. Booking scenarios ──────────────────────────────────────────────
        $scenarios = [

            // ── PENDING ──────────────────────────────────────────────────────
            [
                'user_id'    => self::GUEST_1,
                'status'     => Booking::STATUS_PENDING,
                'check_in'   => $today->copy()->addDays(5),
                'nights'     => 2,
                'room_id'    => 22,
                'adults'     => 2, 'children' => 0,
                'requests'   => 'Early check-in if possible.',
                'coupon'     => null,
                'pay_method' => null,
            ],
            [
                'user_id'    => self::GUEST_3,
                'status'     => Booking::STATUS_PENDING,
                'check_in'   => $today->copy()->addDays(10),
                'nights'     => 3,
                'room_id'    => 28,
                'adults'     => 1, 'children' => 1,
                'requests'   => 'Cot required for child.',
                'coupon'     => null,
                'pay_method' => null,
            ],

            // ── CONFIRMED ────────────────────────────────────────────────────
            [
                'user_id'    => self::GUEST_2,
                'status'     => Booking::STATUS_CONFIRMED,
                'check_in'   => $today->copy()->addDays(3),
                'nights'     => 4,
                'room_id'    => 23,
                'adults'     => 2, 'children' => 0,
                'requests'   => null,
                'coupon'     => null,
                'pay_method' => 'bank_transfer',
            ],
            [
                // Confirmed, arriving TODAY — should appear in arrivals badge
                'user_id'    => self::GUEST_1,
                'status'     => Booking::STATUS_CONFIRMED,
                'check_in'   => $today->copy(),
                'nights'     => 2,
                'room_id'    => 24,
                'adults'     => 3, 'children' => 0,
                'requests'   => 'High floor preferred.',
                'coupon'     => null,
                'pay_method' => 'airtel_money',
            ],

            // ── CONFIRMED with COUPON ─────────────────────────────────────────
            [
                'user_id'    => self::GUEST_3,
                'status'     => Booking::STATUS_CONFIRMED,
                'check_in'   => $today->copy()->addDays(14),
                'nights'     => 2,
                'room_id'    => 29,
                'adults'     => 2, 'children' => 2,
                'requests'   => 'Baby cot and extra towels.',
                'coupon'     => $coupon,
                'pay_method' => 'mpesa',
            ],

            // ── CHECKED IN ───────────────────────────────────────────────────
            [
                'user_id'    => self::GUEST_2,
                'status'     => Booking::STATUS_CHECKED_IN,
                'check_in'   => $today->copy()->subDay(),
                'nights'     => 3,
                'room_id'    => 25,
                'adults'     => 2, 'children' => 0,
                'requests'   => null,
                'coupon'     => null,
                'pay_method' => 'cash',
            ],

            // ── CHECKED OUT ──────────────────────────────────────────────────
            [
                'user_id'    => self::GUEST_1,
                'status'     => Booking::STATUS_CHECKED_OUT,
                'check_in'   => $today->copy()->subDays(7),
                'nights'     => 3,
                'room_id'    => 26,
                'adults'     => 1, 'children' => 0,
                'requests'   => null,
                'coupon'     => null,
                'pay_method' => 'bank_transfer',
            ],
            [
                'user_id'    => self::GUEST_3,
                'status'     => Booking::STATUS_CHECKED_OUT,
                'check_in'   => $today->copy()->subDays(5),
                'nights'     => 2,
                'room_id'    => 28,
                'adults'     => 2, 'children' => 1,
                'requests'   => 'Late check-out requested.',
                'coupon'     => null,
                'pay_method' => 'airtel_money',
            ],

            // ── CANCELLED ────────────────────────────────────────────────────
            [
                'user_id'    => self::GUEST_2,
                'status'     => Booking::STATUS_CANCELLED,
                'check_in'   => $today->copy()->addDays(8),
                'nights'     => 2,
                'room_id'    => 29,
                'adults'     => 2, 'children' => 0,
                'requests'   => null,
                'coupon'     => null,
                'pay_method' => null,
                'cancel_reason' => 'Change of travel plans.',
            ],
            [
                'user_id'    => self::GUEST_1,
                'status'     => Booking::STATUS_CANCELLED,
                'check_in'   => $today->copy()->subDays(3),
                'nights'     => 2,
                'room_id'    => 23,
                'adults'     => 1, 'children' => 0,
                'requests'   => null,
                'coupon'     => null,
                'pay_method' => null,
                'cancel_reason' => 'Medical emergency — full refund requested.',
            ],

            // ── NO SHOW ──────────────────────────────────────────────────────
            [
                'user_id'    => self::GUEST_3,
                'status'     => Booking::STATUS_NO_SHOW,
                'check_in'   => $today->copy()->subDays(2),
                'nights'     => 1,
                'room_id'    => 24,
                'adults'     => 1, 'children' => 0,
                'requests'   => null,
                'coupon'     => null,
                'pay_method' => null,
            ],
        ];

        $seq = Booking::max('id') + 1;

        foreach ($scenarios as $s) {
            $room     = self::ROOMS[$s['room_id']];
            $nights   = $s['nights'];
            $checkIn  = $s['check_in'];
            $checkOut = $checkIn->copy()->addDays($nights);
            $rate     = $room['rate'];

            $subTotal      = $rate * $nights;
            $discountTotal = 0;

            if ($s['coupon']) {
                $discountTotal = (int) round($subTotal * ($s['coupon']->value / 100));
            }

            $taxable   = $subTotal - $discountTotal;
            $taxTotal  = (int) round($taxable * self::TAX_RATE / 100);
            $grandTotal = $taxable + $taxTotal;

            $bookingNumber = 'BK-' . now()->format('Ymd') . '-' . str_pad($seq++, 5, '0', STR_PAD_LEFT);

            // Timestamps based on status
            $confirmedAt  = null;
            $checkedInAt  = null;
            $checkedOutAt = null;
            $cancelledAt  = null;

            if (in_array($s['status'], [
                Booking::STATUS_CONFIRMED,
                Booking::STATUS_CHECKED_IN,
                Booking::STATUS_CHECKED_OUT,
                Booking::STATUS_NO_SHOW,
            ])) {
                $confirmedAt = $checkIn->copy()->subDays(rand(1, 3));
            }

            if (in_array($s['status'], [Booking::STATUS_CHECKED_IN, Booking::STATUS_CHECKED_OUT])) {
                $checkedInAt = $checkIn->copy()->setTime(14, rand(0, 59));
            }

            if ($s['status'] === Booking::STATUS_CHECKED_OUT) {
                $checkedOutAt = $checkOut->copy()->setTime(11, rand(0, 30));
            }

            if ($s['status'] === Booking::STATUS_CANCELLED) {
                $cancelledAt = now()->subDays(rand(0, 2));
            }

            $booking = Booking::create([
                'booking_number'               => $bookingNumber,
                'user_id'                      => $s['user_id'],
                'hotel_id'                     => self::HOTEL_ID,
                'coupon_id'                    => $s['coupon']?->id,
                'coupon_code'                  => $s['coupon']?->code,
                'status'                       => $s['status'],
                'check_in'                     => $checkIn,
                'check_out'                    => $checkOut,
                'nights'                       => $nights,
                'guests_adults'                => $s['adults'],
                'guests_children'              => $s['children'],
                'sub_total'                    => $subTotal,
                'tax_total'                    => $taxTotal,
                'tax_rate'                     => self::TAX_RATE,
                'discount_total'               => $discountTotal,
                'grand_total'                  => $grandTotal,
                'currency'                     => self::CURRENCY,
                'special_requests'             => $s['requests'],
                'cancellation_reason'          => $s['cancel_reason'] ?? null,
                'cancellation_policy_snapshot' => 'Free cancellation up to 48 hours before check-in.',
                'confirmed_at'                 => $confirmedAt,
                'checked_in_at'                => $checkedInAt,
                'checked_out_at'               => $checkedOutAt,
                'cancelled_at'                 => $cancelledAt,
            ]);

            // BookingRoom
            BookingRoom::create([
                'booking_id'   => $booking->id,
                'room_id'      => $s['room_id'],
                'room_type_id' => $room['type_id'],
                'check_in'     => $checkIn,
                'check_out'    => $checkOut,
                'nightly_rate' => $rate,
                'nights'       => $nights,
                'sub_total'    => $subTotal,
            ]);

            // Invoice
            $invoiceStatus = match ($s['status']) {
                Booking::STATUS_CHECKED_IN,
                Booking::STATUS_CHECKED_OUT  => 'paid',
                Booking::STATUS_CANCELLED    => 'void',
                Booking::STATUS_NO_SHOW      => 'overdue',
                default                      => 'issued',
            };

            $invoiceNumber = 'INV-' . now()->format('Y') . '-' . str_pad($booking->id, 6, '0', STR_PAD_LEFT);

            $invoice = Invoice::create([
                'booking_id'     => $booking->id,
                'invoice_number' => $invoiceNumber,
                'subtotal'       => $subTotal,
                'tax_total'      => $taxTotal,
                'discount_total' => $discountTotal,
                'grand_total'    => $grandTotal,
                'currency'       => self::CURRENCY,
                'status'         => $invoiceStatus,
                'issued_at'      => $confirmedAt ?? now(),
                'due_at'         => $checkIn->copy()->subDay(),
                'paid_at'        => in_array($invoiceStatus, ['paid']) ? $checkedInAt ?? now() : null,
            ]);

            // Payment record for paid/confirmed bookings
            if ($s['pay_method'] && in_array($s['status'], [
                Booking::STATUS_CONFIRMED,
                Booking::STATUS_CHECKED_IN,
                Booking::STATUS_CHECKED_OUT,
            ])) {
                $payStatus = in_array($s['status'], [
                    Booking::STATUS_CHECKED_IN,
                    Booking::STATUS_CHECKED_OUT,
                ]) ? 'paid' : 'pending';

                Payment::create([
                    'booking_id'     => $booking->id,
                    'order_id'       => $booking->id * 1000 + rand(1, 999),
                    'method'         => $s['pay_method'],
                    'status'         => $payStatus,
                    'transaction_id' => $payStatus === 'paid' ? strtoupper(Str::random(12)) : null,
                    'amount'         => $grandTotal,
                    'currency'       => self::CURRENCY,
                    'metadata'       => null,
                ]);
            }

            $this->command->line(
                "  <fg=green>✓</> {$booking->booking_number}  {$s['status']}  "
                . "#" . $room['number'] . "  {$nights}N  TZS " . number_format($grandTotal)
            );
        }

        $this->command->info('TRANQUILOO seeding complete. ' . count($scenarios) . ' bookings created.');
    }
}
