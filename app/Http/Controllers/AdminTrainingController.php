<?php

namespace App\Http\Controllers;

use App\Models\Player;
use App\Models\Training;
use Illuminate\Http\Request;

class AdminTrainingController extends Controller
{
    // دریافت تمرین‌های یک بازیکن
    public function index($playerId)
    {
        $player = Player::findOrFail($playerId);

        $trainings = Training::where('player_id', $player->id)
            ->latest()
            ->get();

        return response()->json([
            'player_id' => $player->id,
            'trainings' => $trainings,
        ]);
    }

    // افزودن تمرین برای بازیکن
    public function store(Request $request, $playerId)
    {
        $player = Player::findOrFail($playerId);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $training = Training::create([
            'player_id' => $player->id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
        ]);

        return response()->json([
            'message' => 'تمرین با موفقیت اضافه شد',
            'training' => $training,
        ], 201);
    }

    // ویرایش تمرین
    public function update(
        Request $request,
        $playerId,
        $trainingId
    ) {
        $training = Training::where('player_id', $playerId)
            ->findOrFail($trainingId);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $training->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
        ]);

        return response()->json([
            'message' => 'تمرین با موفقیت ویرایش شد',
            'training' => $training,
        ]);
    }

    // حذف تمرین
    public function destroy($playerId, $trainingId)
    {
        $training = Training::where('player_id', $playerId)
            ->findOrFail($trainingId);

        $training->delete();

        return response()->json([
            'message' => 'تمرین با موفقیت حذف شد',
        ]);
    }
}