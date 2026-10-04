<?php

namespace App\Http\Controllers;

use App\Models\GamesCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GamesCategoryController extends Controller
{
    public function index()
    {
        $categories = GamesCategory::latest()->paginate(10);
        return view('games_categories.index', compact('categories'));
    }

    public function create()
    {
        return view('games_categories.create');
    }

    public function store(Request $request)
    {
        $slug = Str::slug($request->name);
        $count = GamesCategory::where('slug', $slug)->count();
        if ($count) {
            $slug .= '-' . ($count + 1);
        }

        $request->validate([
            'title'     => 'nullable|string|max:255',
            'subtitle'  => 'nullable|string|max:255',
            'name'  => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'home_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('games_categories', 'public');
        }
        
        $homeImagePath = null;
        if ($request->hasFile('home_image')) {
            $homeImagePath = $request->file('home_image')->store('games_categories', 'public');
        }


        GamesCategory::create([
            'title'    => $request->title,
            'subtitle' => $request->subtitle,
            'name'  => $request->name,
            'slug'        => $slug,
            'image' => $imagePath,
            'home_image' => $homeImagePath,
        ]);

        return redirect()->route('admin.games_categories.index')->with('success', 'Category created successfully.');
    }

    public function edit($id)
    {
        $category = GamesCategory::findOrFail($id);
        return view('games_categories.edit', compact('category'));
    }

    public function update(Request $request, $id)
    {
        $category = GamesCategory::findOrFail($id);

        $slug = Str::slug($request->name);
        $count = GamesCategory::where('slug', $slug)->count();
        if ($count) {
            $slug .= '-' . ($count + 1);
        }

        $request->validate([
            'title'     => 'nullable|string|max:255',
            'subtitle'  => 'nullable|string|max:255',
            'name'  => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'home_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $imagePath = $category->image;
        if ($request->hasFile('image')) {
            if ($category->image && Storage::disk('public')->exists($category->image)) {
                Storage::disk('public')->delete($category->image);
            }
            $imagePath = $request->file('image')->store('games_categories', 'public');
        }
        
        $homeImagePath = $category->home_image;
        if ($request->hasFile('home_image')) {
            if ($category->home_image && Storage::disk('public')->exists($category->home_image)) {
                Storage::disk('public')->delete($category->home_image);
            }
            $homeImagePath = $request->file('home_image')->store('games_categories', 'public');
        }

        $category->update([
            'title'    => $request->title,
            'subtitle' => $request->subtitle,
            'name'  => $request->name,
            'slug'        => $slug,
            'image' => $imagePath,
            'home_image' => $homeImagePath,
        ]);

        return redirect()->route('admin.games_categories.index')->with('success', 'Category updated successfully.');
    }

    public function destroy($id)
    {
        $category = GamesCategory::findOrFail($id);

        // Delete image if exists
        if ($category->image && Storage::disk('public')->exists($category->image)) {
            Storage::disk('public')->delete($category->image);
        }

        $category->delete();

        return redirect()->route('admin.games_categories.index')->with('success', 'Category deleted successfully.');
    }
}
