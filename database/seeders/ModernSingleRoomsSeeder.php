<?php

namespace Database\Seeders;

use App\Models\Room;
use App\Models\RoomImage;
use App\Models\RoomType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ModernSingleRoomsSeeder extends Seeder
{
    private const HOTEL_ID = 1;
    private const U        = 'https://images.unsplash.com/photo-';
    private const QS       = '?w=1200&h=800&fit=crop&q=80';

    public function run(): void
    {
        \Illuminate\Database\Eloquent\Model::unguard();

        $roomType = RoomType::firstOrCreate(
            ['hotel_id' => self::HOTEL_ID, 'slug' => 'modern-single'],
            [
                'name'        => 'Modern Single',
                'description' => 'A sleek, 3-D-accented single room designed for the modern solo traveller. '
                               . 'Features a premium single bed with memory-foam mattress, sculpted geometric '
                               . 'wall panels, smart ambient lighting, 43″ 4K display, rain shower, and a '
                               . 'dedicated work nook — everything you need, nothing you don\'t.',
                'base_price'  => 55000,
                'max_guests'  => 1,
                'bed_type'    => 'single',
                'beds_count'  => 1,
                'size_sqm'    => 18,
                'view_type'   => 'City View',
                'smoking'     => false,
                'status'      => 'active',
            ]
        );

        // ── Rooms ─────────────────────────────────────────────────────────────

        $rooms = [
            ['room_number' => '401', 'floor' => 4],
            ['room_number' => '402', 'floor' => 4],
            ['room_number' => '403', 'floor' => 4],
            ['room_number' => '404', 'floor' => 4],
            ['room_number' => '405', 'floor' => 4],
        ];

        foreach ($rooms as $data) {
            Room::firstOrCreate(
                ['hotel_id' => self::HOTEL_ID, 'room_number' => $data['room_number']],
                ['room_type_id' => $roomType->id, 'floor' => $data['floor'], 'status' => 'available']
            );
        }

        // ── Images ────────────────────────────────────────────────────────────

        if ($roomType->images()->exists()) {
            $this->command->line('  <fg=yellow>–</> Images already exist for Modern Single — skipping.');
        } else {
            $u  = self::U;
            $qs = self::QS;

            $images = [
                [
                    'url'     => "{$u}1618221195710-98949ad5ce69{$qs}",
                    'caption' => '3-D sculptured headboard with warm-glow ambient lighting',
                    'cover'   => true,
                ],
                [
                    'url'     => "{$u}1616594039964-ae9021a400a0{$qs}",
                    'caption' => 'Geometric accent wall — matte-black & brushed-gold panels',
                ],
                [
                    'url'     => "{$u}1586023492125-27272f1144ad{$qs}",
                    'caption' => 'Floating single bed with under-glow LED strip, city backdrop',
                ],
                [
                    'url'     => "{$u}1617806691029-7e2d40ea3b31{$qs}",
                    'caption' => 'Smart work nook — wireless charging pad and 4K display',
                ],
                [
                    'url'     => "{$u}1598928636135-d146006ff4be{$qs}",
                    'caption' => 'Rain-head shower enclosure with midnight-blue tile surround',
                ],
            ];

            foreach ($images as $i => $img) {
                RoomImage::create([
                    'room_type_id' => $roomType->id,
                    'path'         => 'unsplash/' . Str::afterLast($img['url'], 'photo-'),
                    'url'          => $img['url'],
                    'caption'      => $img['caption'],
                    'sort_order'   => $i,
                    'is_featured'  => ! empty($img['cover']),
                ]);
            }

            $this->command->line('  <fg=green>✓</> 5 images seeded for Modern Single.');
        }

        \Illuminate\Database\Eloquent\Model::reguard();

        $this->command->info('ModernSingleRoomsSeeder complete.');
    }
}
