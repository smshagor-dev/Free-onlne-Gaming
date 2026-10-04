<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contact;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function create()
    {
        return view('contact.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'priority' => 'required|in:low,medium,high',
            'message' => 'required|string',
            'file' => 'nullable|file|mimetypes:image/jpeg,image/png,application/pdf,text/plain|max:10240',
        ]);

        $filePath = null;
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('contact_files', 'local');
        }

        $contact = Contact::create([
            'user_id' => Auth::check() ? Auth::id() : null, // link if logged in
            'name' => $request->name,
            'email' => $request->email,
            'subject' => $request->subject,
            'priority' => $request->priority,
            'message' => $request->message,
            'file' => $filePath,
        ]);

        // store guest message id in session to show on messages page
        if (!Auth::check()) {
            $guestMessages = Session::get('guest_messages', []);
            $guestMessages[] = $contact->id;
            Session::put('guest_messages', $guestMessages);
        }

        return redirect()->route('contacts.index')->with('success', 'Message sent successfully!');
    }

    public function index()
    {
        if (Auth::check()) {
            $messages = Contact::where('user_id', Auth::id())->latest()->paginate(10);
        } else {
            $guestMessages = Session::get('guest_messages', []);
            $messages = Contact::whereIn('id', $guestMessages)->latest()->paginate(10);
        }

        return view('contact.index', compact('messages'));
    }

    public function adminview()
    {
        // Get all messages, latest first
        $messages = Contact::latest()->paginate(10);

        return view('admin.contacts.index', compact('messages'));
    }

    public function show($id)
    {
        $message = Contact::findOrFail($id);

        return view('admin.contacts.show', compact('message'));
    }

    public function destroy($id)
    {
        $message = Contact::findOrFail($id);
        $message->delete();
        return redirect()->route('admin.contacts.index')->with('success', 'Message deleted successfully!');
    }

    public function reply(Request $request, $id)
    {
        $request->validate([
            'comment' => 'required|string|max:2000',
        ]);

        $message = Contact::findOrFail($id);

        $message->read = true;
        $message->reply = $request->comment;
        $message->replied_at = now();
        $message->save();

        // Send email to user
        Mail::raw("Hello {$message->name},\n\nWe have replied to your message:\n\nYour message: {$message->message}\n\nOur reply: {$message->reply}\n\nThank you,\nSupport Team", function ($mail) use ($message) {
            $mail->to($message->email)
                ->subject('Reply to your message');
        });

        return redirect()->back()->with('success', 'Reply sent and email delivered to user.');
    }

    public function updateStatus($id)
    {
        $message = Contact::findOrFail($id);

        $message->status = !$message->status;
        $message->save();

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully.',
            'status' => $message->status
        ]);
    }
}
