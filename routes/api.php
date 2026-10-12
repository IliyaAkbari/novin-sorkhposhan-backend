<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;

use App\Models\User;

use App\Http\Controllers\AdminPlayerController;
use App\Http\Controllers\AdminAttendanceController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminTrainingController;
use App\Http\Controllers\PlayerAnalysisController;
use App\Http\Controllers\AdminChatController;
use App\Http\Controllers\PlayerChatController;

// =========================
// Login
// =========================

Route::post('/login', function (Request $request) {

    $request->validate([
        'username' => 'required',
        'password' => 'required',
    ]);

    // =========================
    // Admin Login
    // =========================

    $user = User::where(
        'name',
        $request->username
    )->first();

    if (
        $user &&
        Hash::check(
            $request->password,
            $user->password
        )
    ) {

        $token = $user
            ->createToken('auth-token')
            ->plainTextToken;

        return response()->json([
            'user' => $user,
            'role' => 'admin',
            'token' => $token,
            'redirect' => '/admin',
        ]);
    }


    // =========================
    // Player Login
    // =========================

    $player = \App\Models\Player::where(
        'username',
        $request->username
    )->first();

    if (
        $player &&
        Hash::check(
            $request->password,
            $player->password
        )
    ) {

        if (!$player->is_active) {
            return response()->json([
                'message' => 'حساب بازیکن غیرفعال است',
            ], 403);
        }

        $token = $player
            ->createToken('auth-token')
            ->plainTextToken;

        return response()->json([
            'player' => $player,
            'role' => 'player',
            'token' => $token,
            'redirect' => '/player',
        ]);
    }


    // =========================
    // Invalid Login
    // =========================

    return response()->json([
        'message' => 'نام کاربری یا رمز عبور اشتباه است',
    ], 401);
});


// =========================
// Auth Test
// =========================

Route::middleware('auth:sanctum')->get(
    '/me',
    function (Request $request) {

        return response()->json([
            'user' => $request->user(),
        ]);
    }
);
// =========================
// Player - Analyses
// =========================

Route::middleware('auth:sanctum')->get(
    '/player/analyses',
    function (Request $request) {

        $player = $request->user();

        $analyses = \App\Models\PlayerAnalysis::where(
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
);
// =========================
// Player - Me
// =========================

Route::middleware('auth:sanctum')->get(
    '/player/me',
    function (Request $request) {

        $player = $request->user();

        return response()->json([
            'player' => [
                'id' => $player->id,
                'name' => $player->name,
                'username' => $player->username,
                'player_code' => $player->player_code,
                'age_group' => $player->age_group,
                'position' => $player->position,
                'phone' => $player->phone,

                'photo_path' => $player->photo_path,

                'photo_url' => $player->photo_path
    ? url(
        '/player-photo/' .
        basename($player->photo_path)
    )
    : null,

                'subscription_start' =>
                    $player->subscription_start,

                'subscription_end' =>
                    $player->subscription_end,

                'is_active' =>
                    $player->is_active,

                'present_count' =>
                    $player->present_count,

                'absent_count' =>
                    $player->absent_count,

                'coach_rating' =>
                    $player->coach_rating,

                'created_at' =>
                    $player->created_at,

                'updated_at' =>
                    $player->updated_at,
            ],
        ]);
    }
);


// =========================
// Player - Trainings
// =========================

Route::middleware('auth:sanctum')->get(
    '/player/trainings',
    function (Request $request) {

        $player = $request->user();

        return response()->json([
            'player_id' => $player->id,

            'trainings' => $player
                ->trainings()
                ->latest()
                ->get(),
        ]);
    }
);


// =========================
// Public API Test
// =========================

Route::get('/test', function () {

    return response()->json([
        'status' => 'ok',
        'message' => 'API works',
    ]);
});


Route::get(
    '/test-auth-real',
    function (Request $request) {

        return response()->json([
            'status' => 'ok',
            'user' => $request->user(),
        ]);
    }
)->middleware('auth:sanctum');


Route::get(
    '/test-auth',
    function (Request $request) {

        return response()->json([
            'status' => 'ok',
            'message' => 'Admin route works',
            'user' => $request->user(),
        ]);
    }
);


// =========================
// Admin Routes
// =========================

Route::middleware('auth:sanctum')
    ->prefix('admin')
    ->group(function () {
Route::get(
    '/unread-messages',
    [AdminChatController::class, 'unreadMessages']
);
        // =========================
        // Auth Test
        // =========================

        Route::get(
            '/test-auth',
            function (Request $request) {

                return response()->json([
                    'status' => 'authenticated',
                    'user' => $request->user(),
                ]);
            }
        );


        // =========================
        // Admin Check
        // =========================

        Route::get(
            '/check',
            function (Request $request) {

                if (
                    $request->user()->role !== 'admin'
                ) {

                    return response()->json([
                        'message' => 'Access denied',
                    ], 403);
                }

                return response()->json([
                    'message' => 'Admin OK',
                    'user' => $request->user(),
                ]);
            }
        );


        // =========================
        // Dashboard
        // =========================

        Route::get(
            '/dashboard',
            [AdminDashboardController::class, 'index']
        );


        // =========================
        // Players
        // =========================

        Route::get(
            '/players',
            [AdminPlayerController::class, 'index']
        );

        Route::post(
            '/players',
            [AdminPlayerController::class, 'store']
        );

        Route::get(
            '/players/{id}',
            [AdminPlayerController::class, 'show']
        );

        Route::put(
            '/players/{id}',
            [AdminPlayerController::class, 'update']
        );
        Route::delete(
    '/players/{id}',
    [AdminPlayerController::class, 'destroy']
);


        // =========================
        // Attendance
        // =========================

        Route::get(
            '/players/{playerId}/attendance',
            [AdminAttendanceController::class, 'index']
        );

        Route::post(
            '/players/{playerId}/attendance/present',
            [AdminAttendanceController::class, 'storePresent']
        );

        Route::post(
            '/players/{playerId}/attendance/absent',
            [AdminAttendanceController::class, 'storeAbsent']
        );

        Route::put(
            '/players/{playerId}/attendance',
            [AdminAttendanceController::class, 'update']
        );


        // =========================
        // Trainings - Admin
        // =========================

        Route::get(
            '/players/{playerId}/trainings',
            [AdminTrainingController::class, 'index']
        );

        Route::post(
            '/players/{playerId}/trainings',
            [AdminTrainingController::class, 'store']
        );

        Route::put(
            '/players/{playerId}/trainings/{trainingId}',
            [AdminTrainingController::class, 'update']
        );

        Route::delete(
            '/players/{playerId}/trainings/{trainingId}',
            [AdminTrainingController::class, 'destroy']
        );


        // =========================
        // Player Analyses / PDF
        // =========================

        Route::get(
            '/players/{playerId}/analyses',
            [PlayerAnalysisController::class, 'index']
        );

        Route::post(
            '/players/{playerId}/analyses',
            [PlayerAnalysisController::class, 'store']
        );

        Route::delete(
            '/players/{playerId}/analyses/{analysisId}',
            [PlayerAnalysisController::class, 'destroy']
        );


        // =========================
        // Chat - Admin
        // =========================

        Route::get(
            '/players/{playerId}/messages',
            [AdminChatController::class, 'index']
        );

        Route::post(
            '/players/{playerId}/messages',
            [AdminChatController::class, 'store']
        );

        Route::put(
            '/players/{playerId}/messages/{messageId}/read',
            [AdminChatController::class, 'markAsRead']
        );
    });
// =========================
// Player - Chat
// =========================

Route::middleware('auth:sanctum')->group(function () {

    Route::get(
        '/player/messages',
        [PlayerChatController::class, 'index']
    );

    Route::post(
        '/player/messages',
        [PlayerChatController::class, 'store']
    );

});
Route::middleware('auth:sanctum')->get(
    '/player/me',
    function (Request $request) {

        $player = $request->user();

        return response()->json([
            'player' => [
                'id' => $player->id,
                'name' => $player->name,
                'username' => $player->username,
                'player_code' => $player->player_code,
                'age_group' => $player->age_group,
                'position' => $player->position,
                'phone' => $player->phone,

                'photo_path' => $player->photo_path,

                'photo_url' => $player->photo_path
                    ? url(
                        '/player-photo/' .
                        basename($player->photo_path)
                    )
                    : null,

                'subscription_start' =>
                    $player->subscription_start,

                'subscription_end' =>
                    $player->subscription_end,

                'is_active' =>
                    $player->is_active,

                'present_count' =>
                    $player->present_count,

                'absent_count' =>
                    $player->absent_count,

                'coach_rating' =>
                    $player->coach_rating,
            ],
        ]);
    }
);
