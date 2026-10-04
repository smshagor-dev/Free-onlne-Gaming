<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admin\KycField;
use App\Models\UserKycSubmission;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Notification;
use App\Mail\KycSubmittedMail;
use Illuminate\Support\Facades\Mail;

class KycSubmissionController extends Controller
{
    public function create()
    {
        $fields = KycField::all();

        $submissions = UserKycSubmission::where('user_id', Auth::id())->pluck('status', 'kyc_field_id')->toArray();

        return view('user.kyc.create', compact('fields', 'submissions'));
    }

    public function store(Request $request)
    {
        $fields = KycField::all();
        $rules = [];

        foreach ($fields as $field) {
            $rules['kyc_' . $field->id] = $field->input_type === 'file'
                ? 'nullable|file|mimetypes:image/jpeg,image/png,application/pdf|max:5120'
                : 'nullable|string|max:10000';
        }

        $request->validate($rules);

        foreach ($fields as $field) {
            $submission = UserKycSubmission::where('user_id', Auth::id())
                ->where('kyc_field_id', $field->id)
                ->first();

            // Only allow update if no submission or rejected
            if (!$submission || $submission->status === 'rejected') {

                $value = null;

                if ($field->input_type === 'file' && $request->hasFile('kyc_' . $field->id)) {
                    $file = $request->file('kyc_' . $field->id);
                    $value = $file->store('kyc_files', 'local');
                } else {
                    $value = $request->input('kyc_' . $field->id);
                }

                UserKycSubmission::updateOrCreate(
                    [
                        'user_id' => Auth::id(),
                        'kyc_field_id' => $field->id,
                    ],
                    [
                        'value' => $value,
                        'status' => 'pending',
                    ]
                );
            }
        }

        Notification::create([
            'user_id' => Auth::id(),
            'title'   => 'KYC Submitted',
            'message' => 'Your KYC submission has been received and is pending verification.',
        ]);

        Mail::to(Auth::user()->email)->send(new KycSubmittedMail(Auth::user()));

        return redirect()->route('user.kyc.index')->with('success', 'KYC submitted successfully. Status: Pending.');

    }

    public function index()
    {
        $submissions = UserKycSubmission::with('kycField')->where('user_id', Auth::id())->get();

        $allApproved = $submissions->count() > 0 && $submissions->every(function ($submission) {
            return $submission->status === 'approved';
        });

        $verifiedDate = $allApproved ? $submissions->max('updated_at') : null;

        $hasRejected = $submissions->contains(fn($submission) => $submission->status === 'rejected');

        return view('user.kyc.index', compact('submissions', 'allApproved', 'verifiedDate','hasRejected'));
    }
}
