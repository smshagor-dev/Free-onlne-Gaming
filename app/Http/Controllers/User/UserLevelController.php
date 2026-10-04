<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Level;

class UserLevelController extends Controller
{
    public function getUserLevel($userId = null)
    {
        // Get the user
        $user = $userId ? User::findOrFail($userId) : Auth::user();

        // Get levels ordered by points ascending
        $levels = Level::orderBy('points', 'asc')->get();

        $userLevel = null;

        // Determine the user's level based on points
        foreach ($levels as $level) {
            if ($user->points >= $level->points) {
                $userLevel = $level;
            }
        }

        if ($userLevel) {
            // Update user's level_id automatically if different
            if ($user->level_id !== $userLevel->id) {
                $user->level_id = $userLevel->id;
                $user->save();
            }

            return response()->json([
                'level' => $userLevel->level,
                'achievements' => $userLevel->achievements,
                'points' => $user->points,
            ]);
        }

        // Default if no level matched
        return response()->json([
            'level' => 0,
            'achievements' => 'No level yet',
            'points' => $user->points,
        ]);
    }
}
