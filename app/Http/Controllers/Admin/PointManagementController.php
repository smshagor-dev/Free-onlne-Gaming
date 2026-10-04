<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GamePoint;
use App\Models\Level;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class PointManagementController extends Controller
{

    public function create()
    {
        return view('admin.points.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'played_games' => 'required|integer|min:0',
            'points' => 'required|integer|min:0',
            'get_balance' => 'required|integer|min:0',
            'points_amount' => 'required|integer|min:0',
        ]);

        GamePoint::create($request->only(['played_games', 'points', 'get_balance', 'points_amount']));

        return redirect()->route('admin.points.index')->with('success', 'Record created successfully.');
    }
    public function index()
    {
        $records = GamePoint::latest()->paginate(30);
        return view('admin.points.index', compact('records'));
    }

    public function edit($id)
    {
        $record = GamePoint::findOrFail($id);
        return view('admin.points.edit', compact('record'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'played_games' => 'required|integer|min:0',
            'points' => 'required|integer|min:0',
            'get_balance' => 'required|integer|min:0',
            'points_amount' => 'required|integer|min:0',
        ]);

        $record = GamePoint::findOrFail($id);
        $record->update($request->only(['played_games', 'points', 'get_balance', 'points_amount']));

        return redirect()->route('admin.points.index')->with('success', 'Record updated successfully.');
    }
    public function delete($id)
    {
        GamePoint::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Record deleted.');
    }


    // ==========================
    // LEVEL FUNCTIONS
    // ==========================

    public function levelIndex()
    {
        $levels = Level::with('points')->get();
        return view('admin.levels.index', compact('levels'));
    }

    public function levelCreate()
    {
        $pointsList = GamePoint::all();
        return view('admin.levels.create', compact('pointsList'));
    }

    public function levelStore(Request $request)
    {
        $request->validate([
            'levels' => 'required|array',
            'levels.*.points' => 'required|integer|min:0',
            'levels.*.level' => 'required|integer|min:1',
            'levels.*.achievements' => 'nullable|string',
            'levels.*.photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        foreach ($request->levels as $index => $levelData) {
            if (isset($levelData['photo']) && $levelData['photo'] instanceof \Illuminate\Http\UploadedFile) {
                // Store photo
                $photoPath = $levelData['photo']->store('levels', 'public');
                $levelData['photo'] = $photoPath;
            } else {
                $levelData['photo'] = null;
            }

            Level::create($levelData);
        }

        return redirect()->route('admin.levels.index')->with('success', 'Levels created successfully.');
    }

    public function levelEdit($id)
    {
        $level = Level::findOrFail($id);
        $pointsList = GamePoint::all();
        return view('admin.levels.edit', compact('level', 'pointsList'));
    }

    public function levelupdate(Request $request, $id)
    {
        $request->validate([
            'points' => 'required|integer|min:0',
            'level'        => 'required|string|max:255',
            'achievements' => 'nullable|string|max:255',
            'photo'        => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'remove_photo' => 'nullable|boolean',
        ]);

        $level = Level::findOrFail($id);

        $data = [
            'points_id'    => $request->points_id,
            'level'        => $request->level,
            'achievements' => $request->achievements,
        ];

        // Handle photo upload
        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($level->photo && Storage::disk('public')->exists($level->photo)) {
                Storage::disk('public')->delete($level->photo);
            }

            // Store new photo
            $data['photo'] = $request->file('photo')->store('levels', 'public');
        }

        $level->update($data);

        return redirect()->route('admin.levels.index')
            ->with('success', 'Level updated successfully.');
    }



    public function levelDelete($id)
    {
        Level::findOrFail($id)->delete();
        return redirect()->route('admin.levels.index')->with('success', 'Level deleted successfully.');
    }


    public function userAchievement()
    {
        $user = auth::user();

        if (is_null($user->level_id)) {
            return view('user.profile.achievement', [
                'level' => null,
                'next_level' => null,
                'progress' => 0,
                'message' => 'User has no level assigned.'
            ]);
        }
    
        $level = Level::find($user->level_id);
    
        if (!$level) {
            return view('user.profile.achievement', [
                'level' => null,
                'next_level' => null,
                'progress' => 0,
                'message' => 'Level not found.'
            ]);
        }
    
        // Get next level
        $next_level = Level::where('points', '>', $level->points)
            ->orderBy('points', 'asc')
            ->first();

        if ($next_level) {
            $progress = (($user->points - $level->points) / ($next_level->points - $level->points)) * 100;
            $progress = min(max($progress, 0), 100); 
        } else {
            $progress = 100; 
        }
    
        return view('user.profile.achievement', [
            'level' => $level,
            'next_level' => $next_level,
            'progress' => $progress,
            'message' => null
        ]);
    }

    public function allLevelsWithUserLevel()
    {
        $user = Auth::user();
        $levels = Level::orderBy('points', 'asc')->get();
        $userPoints = $user->points ?? 0;

        // Initialize variables
        $currentLevel = null;
        $nextLevel = null;
        $progress = 0;

        // Find the highest level the user has unlocked
        foreach ($levels as $level) {
            if ($userPoints >= $level->points) {
                $currentLevel = $level;
            }
        }

        // Find the next level (if not at max level)
        if ($currentLevel) {
            $nextLevel = Level::where('points', '>', $currentLevel->points)
                ->orderBy('points', 'asc')
                ->first();
        } else {
            // If user hasn't unlocked any level, next level is first level
            $nextLevel = $levels->first();
        }

        // Calculate progress
        if ($currentLevel && $nextLevel) {
            $progress = $this->calculateLevelProgress($user, $currentLevel);
        } elseif ($currentLevel && !$nextLevel) {
            $progress = 100; // Max level reached
        } else {
            // New user with 0 points
            $progress = min(($userPoints / ($levels->first()->points ?? 1)) * 100, 100);
        }

        // Prepare levels data
        $levels = $levels->map(function ($level) use ($user, $currentLevel, $nextLevel) {
            return [
                'id' => $level->id,
                'points' => $level->points,
                'level' => $level->level,
                'achievements' => $level->achievements,
                'photo' => $level->photo ? asset('storage/' . $level->photo) : null,
                'is_user_level' => $currentLevel && $currentLevel->id === $level->id,
                'is_unlocked' => ($user->points ?? 0) >= $level->points,
                'is_next_level' => $nextLevel && $nextLevel->id === $level->id
            ];
        });

        return view('user.profile.levels', [
            'levels' => $levels,
            'current_level' => $currentLevel,
            'next_level' => $nextLevel,
            'progress' => $progress,
            'user_points' => $userPoints
        ]);
    }

    protected function calculateLevelProgress($user, $currentLevel)
    {
        $nextLevel = Level::where('points', '>', $currentLevel->points)
            ->orderBy('points', 'asc')
            ->first();

        if (!$nextLevel) {
            return 100; // Max level reached
        }

        $pointsNeeded = $nextLevel->points - $currentLevel->points;
        $pointsAchieved = ($user->points ?? 0) - $currentLevel->points;

        return min(max(0, ($pointsAchieved / $pointsNeeded) * 100), 100);
    }
}
