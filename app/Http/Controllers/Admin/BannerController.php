<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Banner;
use Illuminate\Support\Facades\Storage;

class BannerController extends Controller
{
    public function bannerindex()
    {
        $banners = Banner::latest()->get();
        return view('admin.settings.index', compact('banners'));
    }

    public function bannercreate()
    {
        return view('admin.settings.create');
    }

    public function bannerstore(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'image' => 'required|image|max:20048',
            'link' => 'nullable|string'
        ]);

        $imagePath = $request->file('image')->store('banners', 'public');

        $link = $request->input('link');

        if ($link) {
            if (!str_starts_with($link, 'http')) {
                $link = url($link);
            }
        }

        Banner::create([
            'title' => $request->title,
            'image' => $imagePath,
            'link' => $request->link,
        ]);

        return redirect()->route('admin.banners.index')->with('success', 'Banner created successfully!');
    }

    public function banneredit(Banner $banner)
    {
        return view('admin.settings.banneredit', compact('banner'));
    }

    public function bannerupdate(Request $request, Banner $banner)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'image' => 'nullable|image|max:20048',
            'link' => 'nullable|string'
        ]);

        if ($request->hasFile('image')) {
            // Delete old image
            Storage::disk('public')->delete($banner->image);
            $banner->image = $request->file('image')->store('banners', 'public');
        }

        $link = $request->input('link');
        if ($link) {
            
            if (!str_starts_with($link, 'http')) {
                $link = url($link); 
            }
        }

        $banner->title = $request->title;
        $banner->link = $request->link;
        $banner->save();

        return redirect()->route('admin.banners.index')->with('success', 'Banner updated successfully!');
    }

    public function bannerdestroy(Banner $banner)
    {
        Storage::disk('public')->delete($banner->image);
        $banner->delete();

        return redirect()->route('admin.banners.index')->with('success', 'Banner deleted successfully!');
    }
}
