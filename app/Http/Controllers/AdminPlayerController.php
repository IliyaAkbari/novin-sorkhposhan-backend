<?php

namespace App\Http\Controllers;

use App\Models\Player;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AdminPlayerController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Players List
    |--------------------------------------------------------------------------
    */

  public function index()
{
    $players = Player::orderBy('id', 'desc')
        ->get()
        ->map(function ($player) {
            return $this->playerResponse($player);
        });

    return response()->json($players);
}


    /*
    |--------------------------------------------------------------------------
    | Create Player
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',

            'username' => [
                'required',
                'string',
                'max:255',
                'unique:players,username',
            ],

            'password' => 'required|string|min:6',

            'player_code' => [
                'nullable',
                'string',
                'max:50',
                'unique:players,player_code',
            ],

            'age_group' => 'nullable|string|max:20',

            'position' => 'nullable|string|max:100',

            'phone' => 'nullable|string|max:30',

            'subscription_end' => 'nullable|date',

            'photo' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
        ]);


        /*
        |--------------------------------------------------------------------------
        | Generate Player Code
        |--------------------------------------------------------------------------
        */

        $playerCode = $validated['player_code'] ?? null;

        if (!$playerCode) {
            $nextId = (Player::max('id') ?? 0) + 1;

            $playerCode = 'PL-' . str_pad(
                $nextId,
                4,
                '0',
                STR_PAD_LEFT
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Upload Photo
        |--------------------------------------------------------------------------
        */

        $photoPath = null;

        if ($request->hasFile('photo')) {
            $photoPath = $request
                ->file('photo')
                ->store('players', 'public');
        }


        /*
        |--------------------------------------------------------------------------
        | Create Player
        |--------------------------------------------------------------------------
        */

        $player = Player::create([
            'name' => $validated['full_name'],

            'username' => $validated['username'],

            'password' => Hash::make(
                $validated['password']
            ),

            'player_code' => $playerCode,

            'age_group' => $validated['age_group'] ?? null,

            'position' => $validated['position'] ?? null,

            'phone' => $validated['phone'] ?? null,

            'photo_path' => $photoPath,

            'subscription_start' => now(),

            'subscription_end' =>
                $validated['subscription_end'] ?? null,

            'is_active' => true,
        ]);


        return response()->json([
            'message' => 'بازیکن با موفقیت ساخته شد',

            'player' => $this->playerResponse($player),
        ], 201);
    }


    /*
    |--------------------------------------------------------------------------
    | Show Player
    |--------------------------------------------------------------------------
    */

    public function show($id)
    {
        $player = Player::find($id);

        if (!$player) {
            return response()->json([
                'message' => 'بازیکن پیدا نشد',
            ], 404);
        }

        return response()->json([
            'player' => $this->playerResponse($player),
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Update Player
    |--------------------------------------------------------------------------
    */

    public function update(Request $request, $id)
    {
        $player = Player::find($id);

        if (!$player) {
            return response()->json([
                'message' => 'بازیکن پیدا نشد',
            ], 404);
        }


        /*
        |--------------------------------------------------------------------------
        | Validation
        |--------------------------------------------------------------------------
        */

        $validated = $request->validate([
            'full_name' => 'sometimes|required|string|max:255',

            'username' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                'unique:players,username,' . $player->id,
            ],

            'password' => 'nullable|string|min:6',

            'player_code' => [
                'sometimes',
                'nullable',
                'string',
                'max:50',
                'unique:players,player_code,' . $player->id,
            ],

            'age_group' => 'sometimes|nullable|string|max:20',

            'position' => 'sometimes|nullable|string|max:100',

            'phone' => 'sometimes|nullable|string|max:30',

            'subscription_end' => 'sometimes|nullable|date',

            'is_active' => 'sometimes|boolean',

            'coach_rating' => [
                'sometimes',
                'integer',
                'min:0',
                'max:100',
            ],

            'photo' => [
                'sometimes',
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
        ]);


        /*
        |--------------------------------------------------------------------------
        | Basic Information
        |--------------------------------------------------------------------------
        */

        if (array_key_exists('full_name', $validated)) {
            $player->name = $validated['full_name'];
        }

        if (array_key_exists('username', $validated)) {
            $player->username = $validated['username'];
        }

        if (array_key_exists('player_code', $validated)) {
            $player->player_code =
                $validated['player_code'];
        }

        if (array_key_exists('age_group', $validated)) {
            $player->age_group =
                $validated['age_group'];
        }

        if (array_key_exists('position', $validated)) {
            $player->position =
                $validated['position'];
        }

        if (array_key_exists('phone', $validated)) {
            $player->phone =
                $validated['phone'];
        }

        if (array_key_exists('subscription_end', $validated)) {
            $player->subscription_end =
                $validated['subscription_end'];
        }

        if (array_key_exists('is_active', $validated)) {
            $player->is_active =
                $validated['is_active'];
        }

        if (array_key_exists('coach_rating', $validated)) {
            $player->coach_rating =
                $validated['coach_rating'];
        }


        /*
        |--------------------------------------------------------------------------
        | Password
        |--------------------------------------------------------------------------
        |
        | اگر password ارسال نشده باشد، رمز قبلی دست‌نخورده می‌ماند.
        |
        */

        if (!empty($validated['password'])) {
            $player->password = Hash::make(
                $validated['password']
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Photo
        |--------------------------------------------------------------------------
        */

        if ($request->hasFile('photo')) {
            $player->photo_path = $request
                ->file('photo')
                ->store('players', 'public');
        }


        /*
        |--------------------------------------------------------------------------
        | Save
        |--------------------------------------------------------------------------
        */

        $player->save();


        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'message' => 'اطلاعات بازیکن با موفقیت ویرایش شد',

            'player' => $this->playerResponse($player),
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Player Response
    |--------------------------------------------------------------------------
    */

    private function playerResponse(Player $player)
    {
        return [
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

            'coach_rating' =>
                $player->coach_rating,
        ];
    }

/*
|--------------------------------------------------------------------------
| Delete Player
|--------------------------------------------------------------------------
*/

public function destroy($id)
{
    $player = Player::find($id);

    if (!$player) {
        return response()->json([
            'message' => 'بازیکن پیدا نشد',
        ], 404);
    }

    /*
    |--------------------------------------------------------------------------
    | Delete Player Photo
    |--------------------------------------------------------------------------
    */

    if ($player->photo_path) {
        Storage::disk('public')->delete(
            $player->photo_path
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Delete Related Data
    |--------------------------------------------------------------------------
    */

    $player->trainings()->delete();

    \App\Models\PlayerAnalysis::where(
        'player_id',
        $player->id
    )->delete();

    \App\Models\Message::where(
        'player_id',
        $player->id
    )->delete();

    /*
    |--------------------------------------------------------------------------
    | Delete Player
    |--------------------------------------------------------------------------
    */

    $player->delete();

    return response()->json([
        'message' => 'بازیکن با موفقیت حذف شد',
    ]);
}
}