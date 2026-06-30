<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\BookingRoom;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Role;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class BookingSeeder extends Seeder
{
    private const HOTEL_ID  = 1;
    private const TAX_RATE  = 18;   // %
    private const CURRENCY  = 'TZS';

    private int $bookingSeq = 1;

    // Rooms keyed by room_id => [room_type_id, nightly_rate]
    private array $rooms = [
        1  => [1, 120000], 2  => [1, 120000], 3  => [1, 120000],
        4  => [1, 120000], 5  => [1, 120000], 6  => [1, 120000],
        7  => [1, 120000], 8  => [1, 120000], 9  => [1, 120000],
        10 => [1, 120000], 11 => [2, 250000], 12 => [2, 250000],
        13 => [2, 250000], 14 => [2, 250000], 15 => [2, 250000],
        16 => [3, 180000], 17 => [3, 180000], 18 => [3, 180000],
        19 => [3, 180000], 20 => [3, 180000], 21 => [3, 180000],
        22 => [4,  55000], 23 => [4,  55000], 24 => [4,  55000],
        25 => [4,  55000], 26 => [4,  55000],
    ];

    public function run(): void
    {
        \Illuminate\Database\Eloquent\Model::unguard();

        $customers  = $this->seedCustomers();
        $roomIds    = array_keys($this->rooms);
        $customerRole = Role::where('name', 'customer')->firstOrFail();

        // Status distribution: 60 total
        $scenarios = [
            // [status, check_in_offset_days, nights, count]
            ['checked_out', -180, 2, 5],
            ['checked_out', -150, 3, 5],
            ['checked_out', -120, 2, 4],
            ['checked_out', -90,  4, 4],
            ['checked_out', -60,  2, 4],
            ['cancelled',   -140, 2, 3],
            ['cancelled',   -100, 3, 3],
            ['cancelled',   -40,  2, 2],
            ['cancelled',   -15,  2, 2],
            ['refunded',    -80,  3, 3],
            ['no_show',     -50,  2, 2],
            ['no_show',     -30,  1, 2],
            ['confirmed',   +5,   3, 5],
            ['confirmed',   +15,  2, 4],
            ['confirmed',   +30,  4, 3],
            ['checked_in',  -1,   3, 4],
            ['pending',     +3,   2, 5],
        ];

        $count = 0;
        foreach ($scenarios as [$status, $offset, $nights, $times]) {
            for ($i = 0; $i < $times; $i++) {
                $roomId   = $roomIds[array_rand($roomIds)];
                $user     = $customers[array_rand($customers)];
                $checkIn  = Carbon::today()->addDays($offset + ($i * 1))->toDateString();
                $checkOut = Carbon::parse($checkIn)->addDays($nights)->toDateString();

                $this->createBooking($user, $roomId, $checkIn, $checkOut, $nights, $status);
                $count++;
            }
        }

        \Illuminate\Database\Eloquent\Model::reguard();

        $this->command->info("BookingSeeder: {$count} bookings created.");
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function seedCustomers(): array
    {
        $customerRole = Role::where('name', 'customer')->firstOrFail();

        $names = [
            'Alice Mwangi', 'Brian Oduya', 'Carol Ndege', 'David Kamau',
            'Eve Mutua',    'Frank Otieno','Grace Wanjiru','Henry Kipchoge',
            'Irene Achieng','James Maina', 'Karen Njeri',  'Leo Omondi',
            'Mary Wambui',  'Nick Osei',   'Olivia Hamisi',
        ];

        $users = [];
        foreach ($names as $i => $name) {
            $slug = Str::slug($name);
            $user = User::firstOrCreate(
                ['email' => "{$slug}@example.com"],
                ['name' => $name, 'password' => Hash::make('password123')]
            );
            if (! $user->roles->contains('id', $customerRole->id)) {
                $user->roles()->attach($customerRole->id);
            }
            $users[] = $user;
        }

        $this->command->line('  <fg=green>✓</> 15 customer accounts ready.');
        return $users;
    }

    private function createBooking(User $user, int $roomId, string $checkIn, string $checkOut, int $nights, string $status): void
    {
        [$roomTypeId, $rate] = $this->rooms[$roomId];

        $subtotal  = $rate * $nights;
        $taxTotal  = round($subtotal * self::TAX_RATE / 100);
        $grand     = $subtotal + $taxTotal;
        $createdAt = Carbon::parse($checkIn)->subDays(rand(3, 30));

        $booking = Booking::create([
            'booking_number'               => 'BK-SEED-' . str_pad($this->bookingSeq++, 5, '0', STR_PAD_LEFT),
            'user_id'                      => $user->id,
            'hotel_id'                     => self::HOTEL_ID,
            'status'                       => $status,
            'check_in'                     => $checkIn,
            'check_out'                    => $checkOut,
            'nights'                       => $nights,
            'guests_adults'                => rand(1, 2),
            'guests_children'              => rand(0, 1),
            'sub_total'                    => $subtotal,
            'tax_total'                    => $taxTotal,
            'tax_rate'                     => self::TAX_RATE,
            'discount_total'               => 0,
            'grand_total'                  => $grand,
            'currency'                     => self::CURRENCY,
            'cancellation_policy_snapshot' => json_encode(['type' => 'moderate']),
            'confirmed_at'                 => in_array($status, ['confirmed','checked_in','checked_out']) ? $createdAt->copy()->addHours(1) : null,
            'checked_in_at'                => in_array($status, ['checked_in','checked_out']) ? Carbon::parse($checkIn)->setHour(14) : null,
            'checked_out_at'               => $status === 'checked_out' ? Carbon::parse($checkOut)->setHour(11) : null,
            'cancelled_at'                 => in_array($status, ['cancelled','refunded']) ? $createdAt->copy()->addDays(rand(1, 3)) : null,
            'created_at'                   => $createdAt,
            'updated_at'                   => $createdAt,
        ]);

        // BookingRoom
        BookingRoom::create([
            'booking_id'   => $booking->id,
            'room_id'      => $roomId,
            'room_type_id' => $roomTypeId,
            'check_in'     => $checkIn,
            'check_out'    => $checkOut,
            'nightly_rate' => $rate,
            'nights'       => $nights,
            'sub_total'    => $subtotal,
        ]);

        // Room availability block (only for active/future bookings)
        if (in_array($status, ['confirmed', 'checked_in', 'pending'])) {
            $day = Carbon::parse($checkIn);
            while ($day->lt(Carbon::parse($checkOut))) {
                DB::table('room_availability')->insertOrIgnore([
                    'room_id'    => $roomId,
                    'booking_id' => $booking->id,
                    'date'       => $day->toDateString(),
                    'status'     => 'blocked',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $day->addDay();
            }
        }

        // Payment
        $payMethod   = ['airtel_money','mpesa','halotel','mix_by_yas'][rand(0,3)];
        $payStatus   = match($status) {
            'pending'    => 'pending',
            'cancelled'  => 'failed',
            'refunded'   => 'refunded',
            default      => 'confirmed',
        };
        Payment::create([
            'booking_id'     => $booking->id,
            'order_id'       => rand(100000, 999999),
            'method'         => $payMethod,
            'status'         => $payStatus,
            'transaction_id' => $payStatus === 'pending' ? null : 'TXN-' . strtoupper(Str::random(12)),
            'amount'         => $grand,
            'refund_amount'  => $status === 'refunded' ? $grand : 0,
            'currency'       => self::CURRENCY,
            'created_at'     => $createdAt,
            'updated_at'     => $createdAt,
        ]);

        // Invoice
        $invStatus = match($status) {
            'pending'            => 'pending',
            'cancelled','refunded' => 'cancelled',
            default              => 'paid',
        };
        Invoice::create([
            'booking_id'      => $booking->id,
            'invoice_number'  => 'INV-' . $booking->booking_number,
            'subtotal'        => $subtotal,
            'tax_total'       => $taxTotal,
            'discount_total'  => 0,
            'grand_total'     => $grand,
            'refund_amount'   => $status === 'refunded' ? $grand : 0,
            'currency'        => self::CURRENCY,
            'status'          => $invStatus,
            'issued_at'       => $createdAt,
            'due_at'          => $createdAt->copy()->addDays(1),
            'paid_at'         => $invStatus === 'paid' ? $createdAt->copy()->addHours(2) : null,
            'created_at'      => $createdAt,
            'updated_at'      => $createdAt,
        ]);
    }
}
