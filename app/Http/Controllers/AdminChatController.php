<?php

namespace App\Http\Controllers;

use App\Models\Player;
use App\Models\Message;
use Illuminate\Http\Request;

class AdminChatController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | دریافت پیام‌های یک بازیکن
    |--------------------------------------------------------------------------
    */

    public function index($playerId)
    {
        $player = Player::find($playerId);

        if (!$player) {
            return response()->json([
                'message' => 'بازیکن پیدا نشد',
            ], 404);
        }

        $messages = Message::where('player_id', $player->id)
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'player_id' => $player->id,
            'messages' => $messages,
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | ارسال پیام توسط Admin
    |--------------------------------------------------------------------------
    */

    public function store(Request $request, $playerId)
    {
        $player = Player::find($playerId);

        if (!$player) {
            return response()->json([
                'message' => 'بازیکن پیدا نشد',
            ], 404);
        }

        $validated = $request->validate([
            'message' => [
                'required',
                'string',
                'max:5000',
            ],
        ]);

        $message = Message::create([
            'player_id' => $player->id,
            'sender_type' => 'admin',
            'message' => $validated['message'],
            'is_read' => false,
        ]);

        return response()->json([
            'message' => 'پیام با موفقیت ارسال شد',
            'data' => $message,
        ], 201);
    }


        /*
    |--------------------------------------------------------------------------
    | خوانده شدن پیام
    |--------------------------------------------------------------------------
    */

   public function markAsRead($playerId, $messageId)
{
    $message = Message::where(
        'player_id',
        $playerId
    )
        ->where(
            'sender_type',
            'player'
        )
        ->find($messageId);

    if (!$message) {
        return response()->json([
            'message' => 'پیام پیدا نشد',
        ], 404);
    }

    $message->is_read = true;
    $message->save();

    return response()->json([
        'message' => 'پیام به عنوان خوانده شده ثبت شد',
        'data' => $message,
    ]);
}

    /*
    |--------------------------------------------------------------------------
    | پیام‌های خوانده نشده برای Admin
    |--------------------------------------------------------------------------
    */

    public function unreadMessages()
    {
        $messages = Message::with('player')
            ->where('sender_type', 'player')
            ->where('is_read', false)
            ->latest()
            ->get();

        return response()->json([
            'count' => $messages->count(),

            'messages' => $messages->map(function ($message) {
                return [
                    'id' => $message->id,
                    'player_id' => $message->player_id,
                    'player_name' => $message->player?->name,
                    'message' => $message->message,
                    'created_at' => $message->created_at,
                ];
            }),
        ]);
    }
}