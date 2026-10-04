<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use App\Events\NotificationCreated;

class NotificationController extends Controller
{
    public function store(Request $request)
    {
        $notification = Notification::create([
            'user_id' => $request->user_id ?? Auth::id(), 
            'title'   => $request->title,
            'message' => $request->message,
        ]);
    
        broadcast(new NotificationCreated($notification))->toOthers();

        return response()->json(['success' => 'Notification created successfully!']);
    }

    public function index()
    {
        $notifications = Notification::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $unreadCount = Notification::where('user_id', Auth::id())
            ->where('is_read', 0)
            ->count();

        return view('notifications.index', compact('notifications', 'unreadCount'));
    }

    public function markAsRead($id)
    {
        $notification = Notification::where('user_id', Auth::id())->findOrFail($id);
        $notification->update(['is_read' => 1]);

        return back()->with('success', 'Notification marked as read.');
    }

    public function markAllAsRead()
    {
        Notification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return back()->with('success', 'All notifications marked as read.');
    }
}
