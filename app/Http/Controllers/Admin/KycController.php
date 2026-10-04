<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admin\KycField;
use App\Models\User;
use App\Models\UserKycSubmission;
use App\Models\Notification;
use App\Mail\KycApprovedMail;
use App\Mail\KycRejectedMail;
use Illuminate\Support\Facades\Mail;

class KycController extends Controller
{
    public function index()
    {
        $kycFields = KycField::latest()->get();
        return view('admin.kyc.index', compact('kycFields'));
    }

    public function create()
    {
        return view('admin.kyc.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_required' => 'nullable|boolean',
            'input_type' => 'required|in:text,file,date',
        ]);

        KycField::create([
            'title' => $request->title,
            'description' => $request->description,
            'is_required' => $request->is_required ? 1 : 0,
            'input_type' => $request->input_type,
        ]);

        return redirect()->route('admin.kyc.index')->with('success', 'KYC field created successfully.');
    }

    public function edit(KycField $kycField)
    {
        return view('admin.kyc.edit', compact('kycField'));
    }

    public function update(Request $request, KycField $kycField)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_required' => 'nullable|boolean',
            'input_type' => 'required|in:text,file,date',
        ]);

        $kycField->update([
            'title' => $request->title,
            'description' => $request->description,
            'is_required' => $request->is_required ? 1 : 0,
            'input_type' => $request->input_type,
        ]);

        return redirect()->route('admin.kyc.index')->with('success', 'KYC field updated successfully.');
    }

    public function destroy(KycField $kycField)
    {
        $kycField->delete();
        return redirect()->route('admin.kyc.index')->with('success', 'KYC field deleted successfully.');
    }

    public function kycUserList()
    {
        $users = User::whereHas('kycSubmissions', function ($query) {
            $query->whereIn('status', ['pending', 'rejected']);
        })
            ->latest()
            ->paginate(15);

        return view('admin.kyc.user', compact('users'));
    }

    public function kycApprovedUserList()
    {
        $users = User::whereHas('kycSubmissions', function ($query) {
            $query->where('status', 'approved');
        })
            ->where('kyc_verified', 1) 
            ->latest()
            ->paginate(15);

        return view('admin.kyc.approved_users', compact('users'));
    }




    public function viewUserSubmissions($userId)
    {
        $user = User::findOrFail($userId);

        // Get all submissions for this user
        $submissions = UserKycSubmission::where('user_id', $userId)->get();

        // Get all KYC fields
        $kycFields = KycField::all(); // assuming you have a KycField model

        // Prepare matches array
        $matches = [];

        foreach ($submissions as $submission) {
            // Get the field info
            $field = $kycFields->firstWhere('id', $submission->field_id);

            if ($field && in_array($field->input_type, ['text', 'date'])) {
                // Find other users with the same value for this field
                $otherUsers = UserKycSubmission::where('field_id', $field->id)
                    ->where('field_value', $submission->field_value)
                    ->where('user_id', '!=', $userId)
                    ->with('user')
                    ->get();

                if ($otherUsers->count()) {
                    $matches[$submission->id] = $otherUsers;
                }
            }
        }

        return view('admin.kyc.view_submissions', compact('user', 'submissions', 'matches', 'kycFields'));
    }


    public function updateSubmissions(Request $request, $userId)
    {
        $user = User::findOrFail($userId);

        $statuses = $request->input('status', []);
        $comments = $request->input('comments', []);

        $allApproved = true;

        foreach ($statuses as $submissionId => $status) {
            $submission = UserKycSubmission::findOrFail($submissionId);
            $submission->status = $status;
            $submission->comments = $comments[$submissionId] ?? null;
            $submission->save();

            if ($status !== 'approved') {
                $allApproved = false; // If any rejected, allApproved becomes false
            }
        }
        // If all submissions are approved, update user's KYC status
        if ($allApproved) {
            $user->kyc_verified = 1;
            $user->save();

            Notification::create([
                'user_id' => $user->id,
                'title' => 'KYC Verified',
                'message' => 'Congratulations! Your KYC has been successfully verified.',
            ]);

            Mail::to($user->email)->send(new KycApprovedMail($user));
        } else {
            Notification::create([
                'user_id' => $user->id,
                'title' => 'KYC Rejected',
                'message' => 'Some of your KYC submissions were rejected. Please review the comments.',
            ]);

            Mail::to($user->email)->send(new KycRejectedMail($user));
        }

        return redirect()->back()->with('success', 'KYC submissions updated successfully.');
    }
}
