<?php

namespace App\Http\Controllers;

use App\Enums\Feature;
use App\Models\Hotel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ChatController extends Controller
{
    private const MAX_HISTORY  = 10;   // messages kept in session per hotel
    private const MAX_TOKENS   = 600;

    public function message(Request $request): JsonResponse
    {
        $data = $request->validate([
            'message'  => ['required', 'string', 'max:1000'],
            'hotel_id' => ['nullable', 'integer', 'exists:hotels,id'],
        ]);

        $apiKey = config('services.anthropic.key');
        if (!$apiKey) {
            return response()->json([
                'reply' => 'The chat service is not configured yet. Please contact the hotel directly.',
            ]);
        }

        $hotelId = $data['hotel_id'] ?? null;

        // Feature gate: hotel-specific chat requires the AI_CONCIERGE feature
        if ($hotelId) {
            $hotel = Hotel::find($hotelId);
            if (!$hotel || !$hotel->hasFeature(Feature::AI_CONCIERGE)) {
                return response()->json([
                    'reply' => 'The AI concierge is not available for this hotel.',
                ], 403);
            }
        }
        $sessionKey = 'chat_' . ($hotelId ?? 'general');
        $history    = session($sessionKey, []);

        // Append user message
        $history[] = ['role' => 'user', 'content' => $data['message']];

        // Trim to last N messages
        if (count($history) > self::MAX_HISTORY) {
            $history = array_slice($history, -self::MAX_HISTORY);
        }

        $response = Http::withHeaders([
            'x-api-key'         => $apiKey,
            'anthropic-version' => '2023-06-01',
            'content-type'      => 'application/json',
        ])->timeout(30)->post('https://api.anthropic.com/v1/messages', [
            'model'      => config('services.anthropic.model', 'claude-haiku-4-5-20251001'),
            'max_tokens' => self::MAX_TOKENS,
            'system'     => $this->buildSystemPrompt($hotelId),
            'messages'   => $history,
        ]);

        if ($response->failed()) {
            return response()->json([
                'reply' => 'Sorry, I\'m having trouble connecting right now. Please try again in a moment.',
            ], 200); // 200 so the widget still renders it as a message
        }

        $reply = $response->json('content.0.text', 'I\'m sorry, I couldn\'t generate a response. Please try again.');

        // Save assistant reply to session
        $history[] = ['role' => 'assistant', 'content' => $reply];
        session([$sessionKey => $history]);

        return response()->json(['reply' => $reply]);
    }

    public function clear(Request $request): JsonResponse
    {
        $hotelId    = $request->input('hotel_id');
        $sessionKey = 'chat_' . ($hotelId ?? 'general');
        session()->forget($sessionKey);

        return response()->json(['ok' => true]);
    }

    private function buildSystemPrompt(?int $hotelId): string
    {
        $platform = config('app.name');

        if (!$hotelId) {
            return "You are a friendly and helpful travel concierge for {$platform}, a hotel booking platform in Tanzania. "
                . "Help guests discover hotels, answer travel questions, and guide them through the booking process. "
                . "Be warm, concise, and professional. Keep replies under 3 sentences. "
                . "If asked about a specific hotel's details, invite them to browse the hotel's page.";
        }

        $hotel = Hotel::with(['roomTypes' => fn ($q) => $q->where('status', 'active'), 'amenities'])
            ->find($hotelId);

        if (!$hotel) {
            return "You are a friendly hotel concierge. Be warm and helpful.";
        }

        $roomLines = $hotel->roomTypes->map(function ($rt) {
            $price = number_format((float) $rt->base_price, 0);
            $info  = ["{$rt->name}: TZS {$price}/night"];
            if ($rt->max_guests)  $info[] = "{$rt->max_guests} guests max";
            if ($rt->bed_type)    $info[] = $rt->bed_type;
            if ($rt->size_sqm)    $info[] = "{$rt->size_sqm} m²";
            if ($rt->view_type)   $info[] = "{$rt->view_type} view";
            return '  • ' . implode(', ', $info);
        })->join("\n");

        $amenities    = $hotel->amenities->pluck('name')->join(', ') ?: 'Contact hotel for details';
        $location     = implode(', ', array_filter([$hotel->address, $hotel->city, $hotel->state, $hotel->country]));
        $stars        = $hotel->star_rating ? "{$hotel->star_rating}-star " : '';
        $checkIn      = $hotel->check_in_time  ?? '14:00';
        $checkOut     = $hotel->check_out_time ?? '11:00';
        $cancelPolicy = $hotel->cancellation_policy ?? 'Contact the hotel for cancellation details.';

        return <<<PROMPT
You are an AI concierge for {$hotel->name}, a {$stars}hotel in {$location}.

Help guests with room inquiries, bookings, local tips, and hotel policies.
Be warm, professional, and concise — keep each reply to 2-4 sentences.
Never fabricate information; if unsure, direct the guest to call or email the hotel.

== HOTEL PROFILE ==
Name        : {$hotel->name}
Location    : {$location}
Stars       : {$stars}hotel
Check-in    : {$checkIn}  |  Check-out: {$checkOut}
Phone       : {$hotel->phone}
Email       : {$hotel->email}
Website     : {$hotel->website}

== ROOMS & RATES ==
{$roomLines}

== AMENITIES ==
{$amenities}

== POLICIES ==
{$cancelPolicy}
Currency: TZS (Tanzanian Shilling)
PROMPT;
    }
}
