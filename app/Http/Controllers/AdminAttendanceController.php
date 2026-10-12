<?php

namespace App\Http\Controllers;

use App\Models\Player;
use Illuminate\Http\Request;

class AdminAttendanceController extends Controller
{
    // دریافت تعداد حضور و غیاب بازیکن
    public function index($playerId)
    {
        $player = Player::findOrFail($playerId);

        return response()->json([
            'player_id' => $player->id,
            'present_count' => (int) $player->present_count,
            'absent_count' => (int) $player->absent_count,
        ]);
    }

    // ثبت حضور
    public function storePresent($playerId)
    {
        $player = Player::findOrFail($playerId);

        $player->increment('present_count');

        return response()->json([
            'message' => 'حضور با موفقیت ثبت شد',
            'present_count' => (int) $player->present_count,
            'absent_count' => (int) $player->absent_count,
        ]);
    }

    // ثبت غیبت
    public function storeAbsent($playerId)
    {
        $player = Player::findOrFail($playerId);

        $player->increment('absent_count');

        return response()->json([
            'message' => 'غیبت با موفقیت ثبت شد',
            'present_count' => (int) $player->present_count,
            'absent_count' => (int) $player->absent_count,
        ]);
    }

    // ویرایش مستقیم تعداد حضور و غیاب
    public function update(Request $request, $playerId)
    {
        $player = Player::findOrFail($playerId);

        $validated = $request->validate([
            'present_count' => 'required|integer|min:0',
            'absent_count' => 'required|integer|min:0',
        ]);

        $player->update([
            'present_count' => $validated['present_count'],
            'absent_count' => $validated['absent_count'],
        ]);

        return response()->json([
            'message' => 'حضور و غیاب با موفقیت به‌روزرسانی شد',
            'present_count' => (int) $player->present_count,
            'absent_count' => (int) $player->absent_count,
        ]);
    }
}