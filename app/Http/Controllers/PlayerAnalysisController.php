<?php

namespace App\Http\Controllers;

use App\Models\Player;
use App\Models\PlayerAnalysis;
use Illuminate\Http\Request;

class PlayerAnalysisController extends Controller
{
    // دریافت فایل‌های تحلیل یک بازیکن
    public function index($playerId)
    {
        $player = Player::findOrFail($playerId);

        $analyses = PlayerAnalysis::where(
            'player_id',
            $player->id
        )
            ->latest()
            ->get()
            ->map(function ($analysis) {

                return [
                    'id' => $analysis->id,
                    'player_id' => $analysis->player_id,
                    'file_name' => $analysis->file_name,
                    'file_path' => $analysis->file_path,
                    'file_size' => $analysis->file_size,
                    'url' => url($analysis->file_path),
                    'created_at' => $analysis->created_at,
                    'updated_at' => $analysis->updated_at,
                ];
            });

        return response()->json([
            'player_id' => $player->id,
            'analyses' => $analyses,
        ]);
    }


    // آپلود PDF برای بازیکن
    public function store(Request $request, $playerId)
    {
        $player = Player::findOrFail($playerId);

        $validated = $request->validate([
            'file' => [
                'required',
                'file',
                'mimes:pdf',
                'max:20480',
            ],
        ]);

        $file = $validated['file'];

        // اطلاعات فایل را قبل از انتقال می‌گیریم
        $fileSize = $file->getSize();
        $originalFileName = $file->getClientOriginalName();

        // مسیر پوشه داخل public
        $folder = public_path(
            'uploads/player-analyses/' . $player->id
        );

        // اگر پوشه وجود نداشت، بساز
        if (!is_dir($folder)) {
            mkdir(
                $folder,
                0755,
                true
            );
        }

        // اسم تصادفی برای جلوگیری از تداخل فایل‌ها
        $fileName = uniqid('', true) . '.pdf';

        // انتقال فایل به public
        $file->move(
            $folder,
            $fileName
        );

        // مسیر قابل دسترسی از طریق مرورگر
        $filePath =
            'uploads/player-analyses/' .
            $player->id .
            '/' .
            $fileName;

        // ثبت اطلاعات در دیتابیس
        $analysis = PlayerAnalysis::create([
            'player_id' => $player->id,
            'file_name' => $originalFileName,
            'file_path' => $filePath,
            'file_size' => $fileSize,
        ]);

        return response()->json([
            'message' => 'فایل تحلیل با موفقیت آپلود شد',
            'analysis' => $analysis,
            'url' => url($filePath),
        ], 201);
    }


    // حذف فایل تحلیل
    public function destroy($playerId, $analysisId)
    {
        $analysis = PlayerAnalysis::where(
            'player_id',
            $playerId
        )->findOrFail($analysisId);

        // حذف فایل از public
        $fullPath = public_path(
            $analysis->file_path
        );

        if (
            $analysis->file_path &&
            file_exists($fullPath)
        ) {
            unlink($fullPath);
        }

        // حذف رکورد دیتابیس
        $analysis->delete();

        return response()->json([
            'message' => 'فایل تحلیل با موفقیت حذف شد',
        ]);
    }
}