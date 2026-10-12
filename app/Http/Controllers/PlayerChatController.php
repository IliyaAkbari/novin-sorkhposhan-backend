<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Player;
use Illuminate\Http\Request;

class PlayerChatController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | دریافت پیام‌های بازیکن لاگین‌شده
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $player = $request->user();

        if (!$player instanceof Player) {
            return response()->json([
                'message' => 'دسترسی فقط برای بازیکن مجاز است',
            ], 403);
        }

        $messages = Message::where(
            'player_id',
            $player->id
        )
            ->orderBy('created_at', 'asc')
            ->get();

        /*
         * پیام‌های ارسال‌شده توسط Admin را بعد از مشاهده
         * به عنوان خوانده‌شده ثبت می‌کنیم.
         */
        Message::where('player_id', $player->id)
            ->where('sender_type', 'admin')
            ->where('is_read', false)
            ->update([
                'is_read' => true,
            ]);

        return response()->json([
            'player_id' => $player->id,
            'messages' => $messages,
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | ارسال پیام توسط بازیکن
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $player = $request->user();

        if (!$player instanceof Player) {
            return response()->json([
                'message' => 'دسترسی فقط برای بازیکن مجاز است',
            ], 403);
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
            'sender_type' => 'player',
            'message' => $validated['message'],
            'is_read' => false,
        ]);

        return response()->json([
            'message' => 'پیام با موفقیت ارسال شد',
            'data' => $message,
        ], 201);
    }
}