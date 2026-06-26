<?php

namespace Database\Seeders;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\Room;
use App\Models\RoomImage;
use App\Models\RoomType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class HotelDataSeeder extends Seeder
{
    private const HOTEL_ID = 2;

    // Unsplash base — append ?w=1200&h=800&fit=crop&q=80
    private const U = 'https://images.unsplash.com/photo-';

    public function run(): void
    {
        \Illuminate\Database\Eloquent\Model::unguard();

        $this->seedRoomTypes();
        $this->seedRooms();
        $this->seedRoomImages();
        $this->seedBlog();

        \Illuminate\Database\Eloquent\Model::reguard();

        $this->command->info('HotelDataSeeder complete.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // ROOM TYPES
    // ─────────────────────────────────────────────────────────────────────────

    private function seedRoomTypes(): void
    {
        // Update the two poorly-named existing types
        RoomType::where('id', 4)->update([
            'name'        => 'King Bedroom',
            'slug'        => 'king-bedroom',
            'description' => 'Spacious room with a luxurious king-sized bed, premium cotton linen, and a modern en-suite bathroom. Perfect for couples seeking comfort and elegance.',
            'bed_type'    => 'king',
            'beds_count'  => 1,
            'max_guests'  => 2,
            'size_sqm'    => 28,
            'view_type'   => 'City View',
            'smoking'     => false,
            'status'      => 'active',
        ]);

        RoomType::where('id', 5)->update([
            'name'        => 'VIP Suite',
            'slug'        => 'vip-suite',
            'description' => 'An elevated experience with a separate living area, king bed, rain shower, and private balcony overlooking the Indian Ocean. Ideal for special occasions.',
            'bed_type'    => 'king',
            'beds_count'  => 1,
            'max_guests'  => 3,
            'size_sqm'    => 48,
            'view_type'   => 'Ocean View',
            'smoking'     => false,
            'status'      => 'active',
        ]);

        $this->command->line('  <fg=green>✓</> Updated existing room types: King Bedroom, VIP Suite');

        $newTypes = [
            [
                'hotel_id'    => self::HOTEL_ID,
                'name'        => 'Standard Room',
                'slug'        => 'standard-room',
                'description' => 'Cosy and well-appointed room featuring twin beds, a work desk, and garden views. A great choice for solo travellers and colleagues on business trips.',
                'base_price'  => 28000,
                'max_guests'  => 2,
                'bed_type'    => 'twin',
                'beds_count'  => 2,
                'size_sqm'    => 22,
                'view_type'   => 'Garden View',
                'smoking'     => false,
                'status'      => 'active',
            ],
            [
                'hotel_id'    => self::HOTEL_ID,
                'name'        => 'Deluxe Room',
                'slug'        => 'deluxe-room',
                'description' => 'Stylish room with a plush queen bed, floor-to-ceiling windows with city panoramas, premium amenities, and a writing area for business guests.',
                'base_price'  => 58000,
                'max_guests'  => 2,
                'bed_type'    => 'queen',
                'beds_count'  => 1,
                'size_sqm'    => 34,
                'view_type'   => 'City View',
                'smoking'     => false,
                'status'      => 'active',
            ],
            [
                'hotel_id'    => self::HOTEL_ID,
                'name'        => 'Junior Suite',
                'slug'        => 'junior-suite',
                'description' => 'Sophisticated suite combining a sleeping area and a cosy lounge, king bed, soaking tub, and a private balcony with sweeping ocean views.',
                'base_price'  => 95000,
                'max_guests'  => 3,
                'bed_type'    => 'king',
                'beds_count'  => 1,
                'size_sqm'    => 54,
                'view_type'   => 'Ocean View',
                'smoking'     => false,
                'status'      => 'active',
            ],
            [
                'hotel_id'    => self::HOTEL_ID,
                'name'        => 'Presidential Suite',
                'slug'        => 'presidential-suite',
                'description' => 'The pinnacle of luxury — two bedrooms, a full dining lounge, butler service, panoramic floor-to-ceiling ocean views, and an exclusive rooftop terrace.',
                'base_price'  => 185000,
                'max_guests'  => 4,
                'bed_type'    => 'king',
                'beds_count'  => 2,
                'size_sqm'    => 88,
                'view_type'   => 'Panoramic Ocean View',
                'smoking'     => false,
                'status'      => 'active',
            ],
        ];

        foreach ($newTypes as $data) {
            $rt = RoomType::firstOrCreate(['hotel_id' => self::HOTEL_ID, 'slug' => $data['slug']], $data);
            $this->command->line("  <fg=green>✓</> Room type: {$rt->name}");
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PHYSICAL ROOMS
    // ─────────────────────────────────────────────────────────────────────────

    private function seedRooms(): void
    {
        $types = RoomType::where('hotel_id', self::HOTEL_ID)
            ->pluck('id', 'slug');

        $newRooms = [
            // ── Floor 1: King Bedroom — add #106-#112 ────────────────────────
            ['room_number' => '106', 'floor' => 1, 'type_slug' => 'king-bedroom'],
            ['room_number' => '107', 'floor' => 1, 'type_slug' => 'king-bedroom'],
            ['room_number' => '108', 'floor' => 1, 'type_slug' => 'king-bedroom'],
            ['room_number' => '109', 'floor' => 1, 'type_slug' => 'king-bedroom'],
            ['room_number' => '110', 'floor' => 1, 'type_slug' => 'king-bedroom'],

            // ── Floor 2: VIP Suite — add #204-#208 ───────────────────────────
            ['room_number' => '204', 'floor' => 2, 'type_slug' => 'vip-suite'],
            ['room_number' => '205', 'floor' => 2, 'type_slug' => 'vip-suite'],
            ['room_number' => '206', 'floor' => 2, 'type_slug' => 'vip-suite'],
            ['room_number' => '207', 'floor' => 2, 'type_slug' => 'vip-suite'],
            ['room_number' => '208', 'floor' => 2, 'type_slug' => 'vip-suite'],

            // ── Floor 3: Standard Room (#301-#316) ───────────────────────────
            ['room_number' => '301', 'floor' => 3, 'type_slug' => 'standard-room'],
            ['room_number' => '302', 'floor' => 3, 'type_slug' => 'standard-room'],
            ['room_number' => '303', 'floor' => 3, 'type_slug' => 'standard-room'],
            ['room_number' => '304', 'floor' => 3, 'type_slug' => 'standard-room'],
            ['room_number' => '305', 'floor' => 3, 'type_slug' => 'standard-room'],
            ['room_number' => '306', 'floor' => 3, 'type_slug' => 'standard-room'],
            ['room_number' => '307', 'floor' => 3, 'type_slug' => 'standard-room'],
            ['room_number' => '308', 'floor' => 3, 'type_slug' => 'standard-room'],
            ['room_number' => '309', 'floor' => 3, 'type_slug' => 'standard-room'],
            ['room_number' => '310', 'floor' => 3, 'type_slug' => 'standard-room'],
            ['room_number' => '311', 'floor' => 3, 'type_slug' => 'standard-room'],
            ['room_number' => '312', 'floor' => 3, 'type_slug' => 'standard-room'],
            ['room_number' => '313', 'floor' => 3, 'type_slug' => 'standard-room'],
            ['room_number' => '314', 'floor' => 3, 'type_slug' => 'standard-room'],
            ['room_number' => '315', 'floor' => 3, 'type_slug' => 'standard-room'],
            ['room_number' => '316', 'floor' => 3, 'type_slug' => 'standard-room'],

            // ── Floor 4: Deluxe Room (#401-#410) ─────────────────────────────
            ['room_number' => '401', 'floor' => 4, 'type_slug' => 'deluxe-room'],
            ['room_number' => '402', 'floor' => 4, 'type_slug' => 'deluxe-room'],
            ['room_number' => '403', 'floor' => 4, 'type_slug' => 'deluxe-room'],
            ['room_number' => '404', 'floor' => 4, 'type_slug' => 'deluxe-room'],
            ['room_number' => '405', 'floor' => 4, 'type_slug' => 'deluxe-room'],
            ['room_number' => '406', 'floor' => 4, 'type_slug' => 'deluxe-room'],
            ['room_number' => '407', 'floor' => 4, 'type_slug' => 'deluxe-room'],
            ['room_number' => '408', 'floor' => 4, 'type_slug' => 'deluxe-room'],
            ['room_number' => '409', 'floor' => 4, 'type_slug' => 'deluxe-room'],
            ['room_number' => '410', 'floor' => 4, 'type_slug' => 'deluxe-room'],

            // ── Floor 5: Junior Suite (#501-#506) ────────────────────────────
            ['room_number' => '501', 'floor' => 5, 'type_slug' => 'junior-suite'],
            ['room_number' => '502', 'floor' => 5, 'type_slug' => 'junior-suite'],
            ['room_number' => '503', 'floor' => 5, 'type_slug' => 'junior-suite'],
            ['room_number' => '504', 'floor' => 5, 'type_slug' => 'junior-suite'],
            ['room_number' => '505', 'floor' => 5, 'type_slug' => 'junior-suite'],
            ['room_number' => '506', 'floor' => 5, 'type_slug' => 'junior-suite'],

            // ── Floor 6: Presidential Suite (#601-#603) ───────────────────────
            ['room_number' => '601', 'floor' => 6, 'type_slug' => 'presidential-suite'],
            ['room_number' => '602', 'floor' => 6, 'type_slug' => 'presidential-suite'],
            ['room_number' => '603', 'floor' => 6, 'type_slug' => 'presidential-suite'],
        ];

        $created = 0;
        foreach ($newRooms as $r) {
            $typeId = $types[$r['type_slug']] ?? null;
            if (! $typeId) continue;

            $exists = Room::where('hotel_id', self::HOTEL_ID)
                ->where('room_number', $r['room_number'])
                ->exists();

            if (! $exists) {
                Room::create([
                    'hotel_id'     => self::HOTEL_ID,
                    'room_type_id' => $typeId,
                    'room_number'  => $r['room_number'],
                    'floor'        => $r['floor'],
                    'status'       => 'available',
                ]);
                $created++;
            }
        }

        $total = Room::where('hotel_id', self::HOTEL_ID)->count();
        $this->command->line("  <fg=green>✓</> Rooms: {$created} new added — {$total} total for TRANQUILOO");
    }

    // ─────────────────────────────────────────────────────────────────────────
    // ROOM IMAGES  (Unsplash, 3-5 per room type)
    // ─────────────────────────────────────────────────────────────────────────

    private function seedRoomImages(): void
    {
        $u = self::U;
        $qs = '?w=1200&h=800&fit=crop&q=80';

        // Keyed by room type slug → [cover_url, ...extra_urls]
        $images = [
            'king-bedroom' => [
                ['url' => "{$u}1631049307264-da0ec9d70304{$qs}", 'caption' => 'King bed with premium linen', 'cover' => true],
                ['url' => "{$u}1560185127-6ac024de38cf{$qs}", 'caption' => 'In-room seating area'],
                ['url' => "{$u}1571896349842-33c89424de2d{$qs}", 'caption' => 'En-suite bathroom with rain shower'],
                ['url' => "{$u}1551882547-ff40c599fb04{$qs}", 'caption' => 'City view from the room'],
            ],
            'vip-suite' => [
                ['url' => "{$u}1578683010236-d716f9a3f461{$qs}", 'caption' => 'Lounge and sleeping area', 'cover' => true],
                ['url' => "{$u}1560185893-a55cbc8c57e8{$qs}", 'caption' => 'King bed with ocean-view balcony access'],
                ['url' => "{$u}1552321554-5fefe8c9ef14{$qs}", 'caption' => 'Luxury soaking tub'],
                ['url' => "{$u}1541123437800-1bb1317badc2{$qs}", 'caption' => 'Private balcony overlooking the ocean'],
                ['url' => "{$u}1564078516393-cf04bd966897{$qs}", 'caption' => 'Living room with ocean panorama'],
            ],
            'standard-room' => [
                ['url' => "{$u}1595576508898-0ad5c879a061{$qs}", 'caption' => 'Twin beds with fresh garden view', 'cover' => true],
                ['url' => "{$u}1587985064135-0366536eab42{$qs}", 'caption' => 'Comfortable twin configuration'],
                ['url' => "{$u}1566665200767-cddfc66b48be{$qs}", 'caption' => 'Workspace and wardrobe'],
                ['url' => "{$u}1445019980597-93fa8acb246c{$qs}", 'caption' => 'Clean and bright en-suite bathroom'],
            ],
            'deluxe-room' => [
                ['url' => "{$u}1611892440504-42a792e24d32{$qs}", 'caption' => 'Plush queen bed with city views', 'cover' => true],
                ['url' => "{$u}1582719478250-c89cae4dc85b{$qs}", 'caption' => 'Elegant room décor'],
                ['url' => "{$u}1590490360182-c33d57733427{$qs}", 'caption' => 'Modern en-suite bathroom'],
                ['url' => "{$u}1526665200767-cddfc66b48be{$qs}", 'caption' => 'Dedicated work area'],
                ['url' => "{$u}1629140727571-9b5ae6ef3f52{$qs}", 'caption' => 'Floor-to-ceiling windows'],
            ],
            'junior-suite' => [
                ['url' => "{$u}1562438668-bcf0ca6578f0{$qs}", 'caption' => 'Suite bedroom with ocean views', 'cover' => true],
                ['url' => "{$u}1549294413-26f195200bef{$qs}", 'caption' => 'Living lounge and dining area'],
                ['url' => "{$u}1584622650111-993a426fbf0a{$qs}", 'caption' => 'Soaking tub with garden outlook'],
                ['url' => "{$u}1540518614846-7eded433c457{$qs}", 'caption' => 'King bed with premium bedding'],
                ['url' => "{$u}1524057740987-55d55e25d7a6{$qs}", 'caption' => 'Private balcony with sea breeze'],
            ],
            'presidential-suite' => [
                ['url' => "{$u}1555854877-bab93c1dc9b2{$qs}", 'caption' => 'Master bedroom — presidential panorama', 'cover' => true],
                ['url' => "{$u}1631049307264-da0ec9d70304{$qs}", 'caption' => 'Second bedroom with king bed'],
                ['url' => "{$u}1520250497591-112f2f40a3f4{$qs}", 'caption' => 'Grand dining and entertainment lounge'],
                ['url' => "{$u}1564078516393-cf04bd966897{$qs}", 'caption' => 'Full ocean view from the terrace'],
                ['url' => "{$u}1584622650111-993a426fbf0a{$qs}", 'caption' => 'Spa bathroom with dual vanity'],
            ],
        ];

        $types = RoomType::where('hotel_id', self::HOTEL_ID)->get()->keyBy('slug');

        foreach ($images as $slug => $imgs) {
            $rt = $types[$slug] ?? null;
            if (! $rt) continue;

            // Skip if images already exist for this type
            if ($rt->images()->exists()) {
                $this->command->line("  <fg=yellow>–</> Images already exist for: {$rt->name}");
                continue;
            }

            foreach ($imgs as $i => $img) {
                RoomImage::create([
                    'room_type_id' => $rt->id,
                    'path'         => 'unsplash/' . Str::afterLast($img['url'], 'photo-'),
                    'url'          => $img['url'],
                    'caption'      => $img['caption'],
                    'sort_order'   => $i,
                    'is_featured'  => $img['cover'] ?? false,
                ]);
            }

            $this->command->line("  <fg=green>✓</> {$rt->name}: " . count($imgs) . " images");
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // BLOG
    // ─────────────────────────────────────────────────────────────────────────

    private function seedBlog(): void
    {
        $u = self::U;
        $qs = '?w=1400&h=700&fit=crop&q=85';

        // ── Categories ───────────────────────────────────────────────────────
        $categories = [
            ['name' => 'Travel Tips',     'slug' => 'travel-tips'],
            ['name' => 'Tanzania Guide',  'slug' => 'tanzania-guide'],
            ['name' => 'Hotel Life',      'slug' => 'hotel-life'],
            ['name' => 'Food & Culture',  'slug' => 'food-culture'],
            ['name' => 'Sustainability',  'slug' => 'sustainability'],
        ];

        $cats = [];
        foreach ($categories as $c) {
            $cats[$c['slug']] = BlogCategory::firstOrCreate(['slug' => $c['slug']], $c);
            $this->command->line("  <fg=green>✓</> Category: {$c['name']}");
        }

        // ── Posts ─────────────────────────────────────────────────────────────
        $posts = [
            [
                'category' => 'tanzania-guide',
                'title'    => 'Top 10 Things to Do in Dar es Salaam',
                'excerpt'  => 'From bustling markets to pristine beaches, Dar es Salaam is a city that rewards the curious traveller. Here is our curated list of must-do experiences.',
                'image'    => "{$u}1516026672322-bc52d61a55d5{$qs}",
                'content'  => <<<HTML
<p>Dar es Salaam — the "Haven of Peace" — is Tanzania's largest city and a gateway to some of the continent's most spectacular destinations. Whether you are here for business or leisure, there is no shortage of things to discover.</p>

<h2>1. Explore the Kariakoo Market</h2>
<p>One of East Africa's largest open-air markets, Kariakoo is a sensory overload in the best possible way. Fresh produce, Swahili spices, colourful fabrics, and the hum of commerce make this an unmissable experience. Go early in the morning for the most vibrant atmosphere.</p>

<h2>2. Visit the National Museum</h2>
<p>The National Museum of Tanzania holds fascinating exhibits tracing the country's natural and cultural history, including the famous Zinjanthropus skull discovered by Louis Leakey. Entry is affordable and the guided tours are excellent.</p>

<h2>3. Relax at Coco Beach</h2>
<p>Locals flock to Coco Beach (officially Oyster Bay Beach) on weekends for fresh coconut water, grilled corn, and a swim in the warm Indian Ocean waters. It is the perfect spot to unwind after a busy day of sightseeing.</p>

<h2>4. Take a Ferry to Zanzibar</h2>
<p>The ferry crossing to Zanzibar takes about two hours and opens up a world of white-sand beaches, Stone Town's UNESCO-listed alleyways, and the famous spice plantations. Day trips are possible, though an overnight stay is highly recommended.</p>

<h2>5. Dine at the Slipway</h2>
<p>This open-air shopping and dining complex on the waterfront is perfect for sundowners and fresh seafood. The views of the dhow harbour at sunset are truly memorable.</p>

<h2>6. Wander the Village Museum</h2>
<p>The open-air Village Museum showcases traditional homesteads from across Tanzania, complete with cultural performances and craft demonstrations. It is an educational and entertaining afternoon out.</p>

<h2>7. Shop at the Mwenge Craft Village</h2>
<p>For authentic Tanzanian crafts — Makonde carvings, Tinga Tinga paintings, beadwork, and handwoven baskets — Mwenge is the go-to destination. Bargaining is expected and part of the fun.</p>

<h2>8. Catch a Dar es Salaam Suns Game</h2>
<p>Basketball is huge in Tanzania. If you are visiting during the season, catching a Dar es Salaam Suns game at the National Stadium is a great way to experience local sports culture.</p>

<h2>9. Walk Along Ocean Road</h2>
<p>This scenic seafront promenade is popular with joggers, cyclists, and families in the early morning and evening. Stop at the historic Ocean Road Hospital, built in the late 1800s, for a glimpse of colonial architecture.</p>

<h2>10. Taste Zanzibar Pizza at Forodhani Gardens</h2>
<p>Take a day trip to Stone Town and end your evening at the famous Forodhani Night Market, where vendors serve the iconic "Zanzibar pizza" — a crepe-like street food stuffed with meat, egg, and vegetables. It is cheap, filling, and absolutely delicious.</p>

<p><em>TRANQUILOO Hotel is perfectly positioned in the heart of Dar es Salaam, putting all of these experiences within easy reach. Ask our concierge for personalised recommendations and transport arrangements.</em></p>
HTML,
            ],
            [
                'category' => 'tanzania-guide',
                'title'    => 'Zanzibar: The Spice Island Complete Guide',
                'excerpt'  => 'Crystal waters, ancient Stone Town streets, and clove-scented air — Zanzibar is one of Africa\'s most enchanting destinations. Here is everything you need to know.',
                'image'    => "{$u}1586500036706-41963de24d8b{$qs}",
                'content'  => <<<HTML
<p>Zanzibar, the semi-autonomous archipelago off Tanzania's coast, is a destination that defies easy description. Part beach paradise, part living history museum, part culinary adventure — it is a place that stays with you long after you leave.</p>

<h2>Getting There</h2>
<p>From Dar es Salaam, you can reach Zanzibar by fast ferry (approximately 2 hours) or by a short domestic flight (25 minutes). Ferries depart multiple times daily from the Kivukoni Ferry Terminal. Book tickets in advance during peak season (June–August and December–January).</p>

<h2>Stone Town — A UNESCO World Heritage Site</h2>
<p>The old quarter of Zanzibar City is a labyrinth of narrow streets, carved wooden doors, and coral-stone buildings. Must-see sites include:</p>
<ul>
<li><strong>The Old Fort (Ngome Kongwe)</strong> — A 17th-century Arab fortification that now hosts art exhibitions and cultural events.</li>
<li><strong>The House of Wonders (Beit el Ajaib)</strong> — The former ceremonial palace of the Zanzibari Sultans.</li>
<li><strong>The Slave Market Memorial</strong> — A powerful and sobering tribute to the victims of the East African slave trade.</li>
<li><strong>Forodhani Night Market</strong> — The best street food in Tanzania, hands down.</li>
</ul>

<h2>The Beaches</h2>
<p>Zanzibar's beaches vary dramatically by location and tide. The best include:</p>
<ul>
<li><strong>Nungwi</strong> (north) — Minimal tidal variation, vibrant nightlife, excellent snorkelling.</li>
<li><strong>Kendwa</strong> (north-west) — Quieter than Nungwi, famous for its full-moon beach parties.</li>
<li><strong>Paje</strong> (south-east) — Kitesurfing capital, turquoise lagoons, relaxed vibe.</li>
<li><strong>Matemwe</strong> (east) — Remote, unspoilt, and absolutely breathtaking at low tide.</li>
</ul>

<h2>Spice Tours</h2>
<p>No visit to Zanzibar is complete without a spice farm tour. You will encounter cloves, vanilla, cardamom, black pepper, and turmeric growing in lush tropical gardens. Tours typically last 2–3 hours and include a traditional Swahili lunch.</p>

<h2>Diving and Snorkelling</h2>
<p>The waters around Zanzibar are teeming with marine life. The best diving spots include Mnemba Atoll (for dolphins and sea turtles), Pange Reef, and Leven Bank. Most resorts and dive centres offer PADI courses for beginners.</p>

<h2>Best Time to Visit</h2>
<p>The best weather falls in the dry seasons: June to October and December to February. Avoid the long rains (March to May) and short rains (November).</p>

<p><em>TRANQUILOO's concierge team can arrange your complete Zanzibar excursion — ferry tickets, accommodation, and guided tours. Just ask at the front desk.</em></p>
HTML,
            ],
            [
                'category' => 'tanzania-guide',
                'title'    => 'Planning Your First Tanzania Safari: A Beginner\'s Guide',
                'excerpt'  => 'Wildlife, wide open plains, and the Big Five — a Tanzania safari is one of the world\'s great travel experiences. Here is how to plan it right.',
                'image'    => "{$u}1547471080-7cc2caa01a7e{$qs}",
                'content'  => <<<HTML
<p>Tanzania is home to the Serengeti — the world's greatest wildlife spectacle — as well as the Ngorongoro Crater, Tarangire, and Lake Manyara national parks. A safari here is genuinely life-changing. But for first-timers, the planning can feel overwhelming. This guide breaks it down simply.</p>

<h2>Choose Your Parks Wisely</h2>
<p>Each park offers a different experience:</p>
<ul>
<li><strong>Serengeti National Park</strong> — The gold standard. Witness the Great Migration (wildebeest and zebra moving between Tanzania and Kenya) between July and October. Year-round wildlife is exceptional.</li>
<li><strong>Ngorongoro Crater</strong> — A collapsed volcanic caldera sheltering one of the densest concentrations of wildlife on Earth. Black rhino sightings are possible here.</li>
<li><strong>Tarangire National Park</strong> — Famous for its ancient baobab trees and elephant herds. Excellent in the dry season (June–October).</li>
<li><strong>Lake Manyara</strong> — Tree-climbing lions and flamingo flocks make this compact park a rewarding stop on the Northern Circuit.</li>
</ul>

<h2>Decide on Your Safari Style</h2>
<p>Tanzania offers everything from budget group safaris (sharing a vehicle with other travellers) to exclusive private mobile camps with dedicated game rangers. Budget safaris start around USD 150 per person per day; luxury options run USD 500–1,500 per person per day. Mid-range lodge safaris are the sweet spot for most travellers at USD 250–500 per day.</p>

<h2>Best Time to Go</h2>
<p>The dry season (late June to October) is widely considered the best time for wildlife viewing — animals congregate around water sources and vegetation is low, making spotting easier. However, this is also peak season, so book accommodation well in advance.</p>

<h2>What to Pack</h2>
<ul>
<li>Neutral-coloured clothing (khaki, olive, beige) — avoid white and very bright colours</li>
<li>Warm layers for early morning game drives (it gets cold in open vehicles)</li>
<li>High-factor sunscreen and insect repellent with DEET</li>
<li>Binoculars — a good pair makes a huge difference</li>
<li>A zoom lens camera if photography is important to you</li>
<li>Enough cash in USD for tipping (USD 10–15 per day per guide is standard)</li>
</ul>

<h2>Health Considerations</h2>
<p>Consult your doctor 6–8 weeks before departure. Anti-malaria medication is strongly recommended for most safari areas. Ensure your routine vaccinations are up to date, and consider a yellow fever vaccination (required if arriving from certain countries).</p>

<p><em>TRANQUILOO Hotel is an ideal pre-safari base, located just 45 minutes from Julius Nyerere International Airport. We partner with trusted safari operators and can arrange your complete Northern Circuit itinerary.</em></p>
HTML,
            ],
            [
                'category' => 'food-culture',
                'title'    => 'Tanzania on a Plate: The Local Dishes You Must Try',
                'excerpt'  => 'Tanzanian cuisine is a delicious fusion of Swahili, Arab, Indian, and African flavours. From coconut-infused pilau to grilled mishkaki, discover what to eat and where.',
                'image'    => "{$u}1414235077428-338989a2e8c0{$qs}",
                'content'  => <<<HTML
<p>Tanzanian food is hearty, flavourful, and deeply tied to the country's coastal Swahili heritage and spice trade history. Whether you are eating at a roadside mama lishe (local eatery) or a fine-dining restaurant, the food tells a story.</p>

<h2>Ugali — The National Staple</h2>
<p>Ugali is a stiff porridge made from white maize flour and water. It is the cornerstone of Tanzanian cuisine — eaten with stews, grilled meats, sukuma wiki (collard greens), or fish. Do not leave Tanzania without trying it. Eat it as locals do: pinch off a piece, shape it into a small ball, and use it to scoop up your accompaniment.</p>

<h2>Pilau</h2>
<p>Zanzibar's crowning culinary achievement, pilau is a richly spiced rice dish cooked with whole spices (cumin, cardamom, cloves, cinnamon, black pepper), meat, and onions. The smell alone is enough to draw you in from a distance. Eat it with a tangy kachumbari salad of tomato, onion, and coriander.</p>

<h2>Mishkaki</h2>
<p>Mishkaki are marinated meat skewers (usually beef or goat) grilled over charcoal. You will find mishkaki vendors on street corners throughout Dar es Salaam from late afternoon, serving them hot with bread and chilli sauce. They are irresistible.</p>

<h2>Urojo — Zanzibar Mix</h2>
<p>A uniquely Zanzibari street food, Urojo is a tangy soup-like concoction filled with fried bhajias (lentil fritters), boiled potato, mango, crispy cassava, and a squeeze of lime. It looks chaotic but tastes extraordinary.</p>

<h2>Nyama Choma</h2>
<p>Swahili for "roasted meat", nyama choma is the East African equivalent of a barbecue. Goat is most commonly used, roasted slowly over an open fire and served with kachumbari, ugali, and ice-cold Safari Lager. It is a social institution.</p>

<h2>Samaki wa Kupaka</h2>
<p>A Swahili classic: whole fish marinated in coconut milk, tamarind, and spices then grilled or baked. The creamy, slightly sour coconut marinade is unlike anything else. Served along the coast and at Zanzibar restaurants.</p>

<h2>Mandazi</h2>
<p>Tanzania's answer to the doughnut, mandazi are lightly sweet, triangular fried dough flavoured with coconut and cardamom. They are eaten at breakfast with chai (spiced tea) or as a snack throughout the day.</p>

<h2>Zanzibar Pizza</h2>
<p>Despite the name, this has nothing to do with Italian pizza. It is a thin crepe cooked on a hot griddle and folded around fillings such as minced beef, egg, mayonnaise, and vegetables. The sweet version with Nutella and banana is equally popular.</p>

<h2>Where to Eat in Dar es Salaam</h2>
<p>For authentic local food at budget prices, head to Kariakoo or the Posta area. For upscale Swahili cuisine, try The Slipway restaurants or the waterfront dining options in Oyster Bay. For street food after dark, Mnazi Mmoja and Kariakoo night stalls are unmissable.</p>

<p><em>TRANQUILOO's restaurant serves a daily breakfast buffet featuring both Tanzanian favourites and international options. Our evening menu showcases authentic Swahili coastal cuisine — ask the chef about the catch of the day.</em></p>
HTML,
            ],
            [
                'category' => 'hotel-life',
                'title'    => 'Why Business Travellers Choose Dar es Salaam (and TRANQUILOO)',
                'excerpt'  => 'Dar es Salaam is East Africa\'s fastest-growing commercial hub. Here\'s why it is attracting international business travellers — and how to make the most of your trip.',
                'image'    => "{$u}1497366216548-37526070297c{$qs}",
                'content'  => <<<HTML
<p>For years, Nairobi dominated East Africa's business travel landscape. But Dar es Salaam is closing the gap rapidly. The city's port — the busiest in the region — combined with a booming finance sector, growing tech ecosystem, and improved infrastructure makes it an increasingly important commercial destination.</p>

<h2>The Economic Case for Dar es Salaam</h2>
<p>Tanzania has maintained GDP growth of 6–7% annually for over a decade. Key sectors attracting foreign investment include natural resources (gold, tanzanite, natural gas), agriculture, tourism, and infrastructure development. The Tanzania Investment Centre actively facilitates foreign direct investment, making Dar es Salaam a practical destination for business negotiations and market entry.</p>

<h2>Business Travel Essentials</h2>
<ul>
<li><strong>Currency:</strong> Tanzanian Shilling (TZS). USD is widely accepted in hotels and larger establishments. ATMs are readily available in the city centre.</li>
<li><strong>SIM Cards:</strong> Purchase an Airtel or Vodacom SIM at the airport for affordable local data. 4G coverage is good in central Dar es Salaam.</li>
<li><strong>Transport:</strong> Use Uber or Bolt for safe, metered rides. The Dar Rapid Transit (DART) bus is efficient for cross-city travel during off-peak hours.</li>
<li><strong>Business Hours:</strong> Most offices operate 8am–5pm Monday to Friday. Government offices may close for Friday prayer from 12:30–2pm.</li>
<li><strong>Language:</strong> Kiswahili is the national language; English is widely spoken in the business community.</li>
</ul>

<h2>Meeting and Conference Facilities</h2>
<p>TRANQUILOO offers a dedicated business centre with high-speed fibre internet, printing and scanning services, and two fully-equipped conference rooms accommodating 8 and 25 delegates respectively. Our events team handles catering, AV setup, and accommodation arrangements for delegations of any size.</p>

<h2>After-Business Entertainment</h2>
<p>When the meetings are done, Dar es Salaam offers excellent options for client entertainment. The Slipway waterfront complex is ideal for informal dinners. For a uniquely local experience, take clients to Coco Beach at sunset or to a nyama choma restaurant in the Sinza neighbourhood.</p>

<p><em>TRANQUILOO offers corporate rates for regular business guests. Contact our reservations team to set up a corporate account with preferential pricing, invoicing, and priority room assignment.</em></p>
HTML,
            ],
            [
                'category' => 'travel-tips',
                'title'    => '10 Travel Tips for First-Time Visitors to Tanzania',
                'excerpt'  => 'Arriving in Tanzania for the first time? From visa requirements to safety tips and cultural etiquette, here is everything you need to know before you land.',
                'image'    => "{$u}1506905925346-21bda4d32df4{$qs}",
                'content'  => <<<HTML
<p>Tanzania is a welcoming, safe, and endlessly rewarding destination. But like any new country, it helps to arrive informed. Here are the ten most important things to know before your first visit.</p>

<h2>1. Sort Your Visa in Advance</h2>
<p>Most nationalities require a visa to enter Tanzania. You can apply online at the Tanzania Immigration Portal (eservices.immigration.go.tz) for an e-visa before departure — this saves time at the airport. Single-entry tourist visas cost USD 50 for most nationalities. East African citizens typically enter visa-free.</p>

<h2>2. Yellow Fever Certificate May Be Required</h2>
<p>If you are arriving from a yellow fever-endemic country (most of sub-Saharan Africa and parts of South America), you will need a valid yellow fever vaccination certificate. Check the current requirements with the Tanzanian embassy in your country before travel.</p>

<h2>3. Take Malaria Precautions</h2>
<p>Tanzania is a malaria zone. Start anti-malaria medication before arrival (consult your doctor for the right option), sleep under a mosquito net, use DEET insect repellent from dusk, and wear long sleeves and trousers in the evenings.</p>

<h2>4. Carry Small Denomination Notes</h2>
<p>Large USD notes (especially USD 100 bills) are accepted at hotels and larger shops, but street vendors and tuk-tuk drivers will struggle to make change. Carry a mix of TZS and small USD bills for daily expenses.</p>

<h2>5. Bargaining is Normal — but Not Everywhere</h2>
<p>At craft markets, with taxi drivers (without a meter), and at some guesthouses, bargaining is expected and part of the culture. However, do not try to bargain in restaurants, supermarkets, or shops with fixed prices displayed. Read the situation.</p>

<h2>6. Dress Modestly Outside Resorts</h2>
<p>Tanzania is a predominantly Muslim country on the coast and a culturally conservative society inland. Outside beach resorts and tourist zones, women should cover their shoulders and wear skirts or trousers that fall below the knee. This is especially important in Zanzibar's Stone Town.</p>

<h2>7. Greetings Matter</h2>
<p>Tanzanians place great importance on greetings. Always say "Habari?" ("How are you?") before getting to business. The response is "Nzuri" (good) or "Salama" (peaceful). Learning a few words of Swahili will earn you enormous goodwill.</p>

<h2>8. Drink Bottled or Treated Water</h2>
<p>Tap water in Dar es Salaam is not reliably safe for drinking. Stick to bottled water, or use a water purification tablet/filter. Ice in reputable hotels and restaurants is fine.</p>

<h2>9. Use Bolt or Uber for Safe Transport</h2>
<p>Both Bolt and Uber operate in Dar es Salaam and are significantly safer than unmarked taxis. Always share your trip details with a companion, sit in the back seat, and confirm the driver's name and plate before getting in.</p>

<h2>10. Tip Generously — It Matters</h2>
<p>Tanzania's service industry relies heavily on gratuities. A 10% tip at restaurants, TZS 5,000 per bag for hotel porters, and USD 10–15 per day for safari guides is standard. It is genuinely meaningful to the people who work hard to make your trip unforgettable.</p>
HTML,
            ],
            [
                'category' => 'sustainability',
                'title'    => 'Our Commitment to Sustainable Tourism in Tanzania',
                'excerpt'  => 'At TRANQUILOO we believe great hospitality and environmental responsibility go hand in hand. Here is what we are doing to protect the ecosystems our guests come to experience.',
                'image'    => "{$u}1499988921418-b361d096c068{$qs}",
                'content'  => <<<HTML
<p>Tanzania's extraordinary natural heritage — from the Serengeti to the Zanzibar coral reefs — is both the reason travellers visit and the resource most at risk from tourism itself. At TRANQUILOO, we take our responsibility seriously.</p>

<h2>Energy and Water Conservation</h2>
<p>We have installed solar panels on our rooftop to supply 40% of the hotel's electricity needs. All guest rooms feature motion-sensor lighting and energy-efficient LED fixtures. Our laundry operations use a water recycling system that reduces water consumption by 30% compared to conventional methods.</p>

<p>Guests are invited to opt out of daily towel and linen changes via the "Green Stay" card in their room — for every night a guest participates, we donate TZS 2,000 to the Tanzania Forest Services Agency's tree-planting programme.</p>

<h2>Plastic Reduction</h2>
<p>We eliminated single-use plastic water bottles in 2023, replacing them with in-room glass bottles filled from our filtered water system. Our restaurant uses paper straws, and all takeaway packaging is either compostable or recyclable.</p>

<h2>Supporting Local Communities</h2>
<p>Over 80% of our staff are from Dar es Salaam and surrounding communities. We source produce from local farms in the Coastal Region, reducing food miles and supporting smallholder agriculture. Our gift shop stocks only Tanzania-made crafts from verified artisans who receive fair prices.</p>

<h2>Wildlife Conservation Partnerships</h2>
<p>We partner with the Wildlife Conservation Society of Tanzania to offer guests the option to contribute to anti-poaching patrols in the Selous Game Reserve. A voluntary TZS 5,000 contribution per night is added to guest folios (fully refundable on request) and transferred directly to conservation operations.</p>

<h2>Our 2027 Goals</h2>
<ul>
<li>Achieve carbon-neutral operations through renewable energy and verified offsets</li>
<li>Zero food waste to landfill through composting and food-bank partnerships</li>
<li>100% local and organic sourcing for the restaurant's fruit and vegetable supply</li>
<li>Achieve Green Globe certification</li>
</ul>

<p><em>Sustainable travel does not mean sacrificing comfort. It means making choices that ensure the places we love remain extraordinary for future generations. We are glad you are here — and we are committed to doing this right.</em></p>
HTML,
            ],
            [
                'category' => 'tanzania-guide',
                'title'    => 'Climbing Kilimanjaro: What Nobody Tells You',
                'excerpt'  => 'Mount Kilimanjaro is Africa\'s highest peak and the world\'s tallest free-standing mountain. Here is the honest guide to what the climb is actually like.',
                'image'    => "{$u}1533105079780-92b9be4f5e22{$qs}",
                'content'  => <<<HTML
<p>At 5,895 metres above sea level, Mount Kilimanjaro dominates the Tanzanian sky — visible on a clear day from as far as 200 kilometres away. Every year, approximately 35,000 people attempt to summit. Around 65% make it to Uhuru Peak. Here is what the guidebooks do not always tell you.</p>

<h2>You Do Not Need to Be an Elite Athlete</h2>
<p>Kilimanjaro is a non-technical climb — no ropes, crampons, or ice axes required. The challenge is altitude, not terrain. Reasonably fit individuals with no prior high-altitude experience successfully summit every day. However, underestimating the climb is the most common reason for failure. Prepare properly.</p>

<h2>Altitude Sickness is the Biggest Risk</h2>
<p>Acute Mountain Sickness (AMS) affects most climbers above 3,000 metres to some degree. Symptoms include headache, nausea, dizziness, and fatigue. More serious forms — High Altitude Pulmonary Oedema (HAPE) or High Altitude Cerebral Oedema (HACE) — are medical emergencies. The single best preventative is slow ascent ("climb high, sleep low"). Diamox (acetazolamide) is commonly used to prevent AMS — consult a doctor before the climb.</p>

<h2>Choose Your Route Carefully</h2>
<ul>
<li><strong>Machame Route (6–7 days)</strong> — The most popular route; scenic, challenging, good acclimatisation profile. Recommended for most climbers.</li>
<li><strong>Marangu Route (5–6 days)</strong> — The only route with hut accommodation instead of tents; often called "the Coca-Cola route." Lower summit success rate due to insufficient acclimatisation time.</li>
<li><strong>Lemosho Route (7–8 days)</strong> — Quieter, longer, and with the best acclimatisation profile. Highest summit success rate. Recommended for first-timers with time flexibility.</li>
<li><strong>Rongai Route (6–7 days)</strong> — Approaches from the Kenyan border. Drier and less crowded than western routes.</li>
</ul>

<h2>The Summit Night is Brutal</h2>
<p>The final push to Uhuru Peak typically begins at midnight and takes 6–8 hours. Temperatures at the crater rim can drop to –20°C. The pace is glacially slow — your guide will say "pole pole" (slowly slowly) constantly. Many climbers describe summit night as the hardest thing they have ever done. The sunrise from Stella Point, however, is indescribable.</p>

<h2>What to Budget</h2>
<p>Budget climbers should expect to pay at least USD 1,800–2,500 for a 7-day guided climb including park fees, accommodation, and crew. Be wary of operators significantly below this — corners are cut on safety and crew welfare. Premium operators charge USD 3,500–5,000 and deliver a notably better experience.</p>

<h2>The People Who Carry Your Mountain</h2>
<p>Every Kilimanjaro climb relies on an army of porters, guides, and cooks. A typical group of 4 trekkers may have a support crew of 15–20 people. Tip generously (the recommended minimum is USD 20 per day for porters, USD 25 for the head guide) and choose operators who pay above the KPAP-recommended minimum wage.</p>

<p><em>TRANQUILOO can connect you with vetted Kilimanjaro operators and arrange your Moshi or Arusha pre-climb accommodation. Ask our concierge for details.</em></p>
HTML,
            ],
        ];

        foreach ($posts as $postData) {
            $cat = $cats[$postData['category']] ?? null;
            if (! $cat) continue;

            $slug = Str::slug($postData['title']);

            if (BlogPost::where('slug', $slug)->exists()) {
                $this->command->line("  <fg=yellow>–</> Post already exists: {$postData['title']}");
                continue;
            }

            BlogPost::create([
                'category_id'    => $cat->id,
                'title'          => $postData['title'],
                'slug'           => $slug,
                'excerpt'        => $postData['excerpt'],
                'content'        => $postData['content'],
                'featured_image' => $postData['image'],
                'status'         => 'published',
            ]);

            $this->command->line("  <fg=green>✓</> Post: {$postData['title']}");
        }
    }
}
