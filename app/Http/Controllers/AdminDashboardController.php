<?php

namespace App\Http\Controllers;

use App\Models\Player;
use App\Models\Attendance;
use Carbon\Carbon;

class AdminDashboardController extends Controller
{
    public function index()
    {
        // کل بازیکنان
        $totalPlayers = Player::count();

        // بازیکنان فعال
        $activePlayers = Player::where('is_active', true)->count();

        // بازیکنانی که اشتراکشان در 7 روز آینده تمام می‌شود
        $expiringSubscriptions = Player::where('is_active', true)
            ->whereNotNull('subscription_end')
            ->whereBetween('subscription_end', [
                Carbon::today(),
                Carbon::today()->addDays(7),
            ])
            ->count();

        // تعداد غیبت‌های امروز
        $todayAbsence = Attendance::whereDate('date', Carbon::today())
            ->where('status', 'absent')
            ->count();

        return response()->json([
            'total_players' => $totalPlayers,
            'active_players' => $activePlayers,
            'expiring_subscriptions' => $expiringSubscriptions,
            'today_absence' => $todayAbsence,
        ]);
    }
}