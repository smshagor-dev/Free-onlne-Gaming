<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use App\Models\GamesCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TagController extends Controller
{
    public function index()
    {
        $tags = Tag::with('category')->latest()->paginate(10);
        return view('tags.index', compact('tags'));
    }

    public function create()
    {
        $categories = GamesCategory::all();
        return view('tags.create', compact('categories'));
    }

    public function store(Request $request)
    {

        $slug = Str::slug($request->name);
        $count = Tag::where('slug', $slug)->count();
        if ($count) {
            $slug .= '-' . ($count + 1);
        }

        $request->validate([
            'category_id' => 'required|exists:games_categories,id',
            'tag_name'    => 'required|string|max:255',
            'photo'       => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'title'       => 'nullable|string|max:255',
            'subtitle'    => 'nullable|string|max:255',
        ]);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('tags', 'public');
        }

        Tag::create([
            'category_id' => $request->category_id,
            'tag_name'    => $request->tag_name,
            'slug'        => $slug,
            'photo'       => $photoPath,
            'title'       => $request->title,
            'subtitle'    => $request->subtitle,
        ]);

        return redirect()->route('admin.tags.index')->with('success', 'Tag created successfully.');
    }

    public function edit($id)
    {
        $tag = Tag::findOrFail($id);
        $categories = GamesCategory::all();
        return view('tags.edit', compact('tag', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $tag = Tag::findOrFail($id);

        $slug = Str::slug($request->name);
        $count = Tag::where('slug', $slug)->count();
        if ($count) {
            $slug .= '-' . ($count + 1);
        }

        $request->validate([
            'category_id' => 'required|exists:games_categories,id',
            'tag_name'    => 'required|string|max:255',
            'photo'       => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'title'       => 'nullable|string|max:255',
            'subtitle'    => 'nullable|string|max:255',
        ]);

        $photoPath = $tag->photo;
        if ($request->hasFile('photo')) {
            if ($tag->photo && Storage::disk('public')->exists($tag->photo)) {
                Storage::disk('public')->delete($tag->photo);
            }
            $photoPath = $request->file('photo')->store('tags', 'public');
        }

        $tag->update([
            'category_id' => $request->category_id,
            'tag_name'    => $request->tag_name,
            'slug'        => $slug,
            'photo'       => $photoPath,
            'title'       => $request->title,
            'subtitle'    => $request->subtitle,
        ]);

        return redirect()->route('admin.tags.index')->with('success', 'Tag updated successfully.');
    }

    public function destroy($id)
    {
        $tag = Tag::findOrFail($id);

        if ($tag->photo && Storage::disk('public')->exists($tag->photo)) {
            Storage::disk('public')->delete($tag->photo);
        }

        $tag->delete();

        return redirect()->route('admin.tags.index')->with('success', 'Tag deleted successfully.');
    }
}
