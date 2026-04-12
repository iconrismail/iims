<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileUpdateController extends Controller
{
    /** Employee: show their own update request form */
    public function create(Request $request): View
    {
        $employee = $request->user()->employee;
        abort_if(!$employee, 403, 'No employee profile linked to your account.');

        // Load the employee's most recent pending request (if any)
        $pending = ProfileUpdateRequest::where('employee_id', $employee->id)
            ->where('status', 'pending')
            ->latest()
            ->first();

        return view('profile-updates.create', compact('employee', 'pending'));
    }

    /** Employee: submit a profile update request */
    public function store(Request $request): RedirectResponse
    {
        $employee = $request->user()->employee;
        abort_if(!$employee, 403);

        $validated = $request->validate([
            'phone'             => 'nullable|string|max:30',
            'address'           => 'nullable|string|max:500',
            'emergency_contact' => 'nullable|string|max:200',
            'bank_name'         => 'nullable|string|max:100',
            'bank_account'      => 'nullable|string|max:50',
        ]);

        // Only include fields that have actually changed
        $requested = [];
        foreach ($validated as $field => $value) {
            if ($value !== null && $value !== '' && $value !== (string) $employee->$field) {
                $requested[$field] = $value;
            }
        }

        if (empty($requested)) {
            return back()->with('error', 'No changes detected. Please modify at least one field.');
        }

        // Cancel any prior pending request first
        ProfileUpdateRequest::where('employee_id', $employee->id)
            ->where('status', 'pending')
            ->update(['status' => 'rejected', 'admin_note' => 'Superseded by a newer request.']);

        ProfileUpdateRequest::create([
            'employee_id'      => $employee->id,
            'requested_fields' => $requested,
            'status'           => 'pending',
        ]);

        return redirect()->route('dashboard')->with('success', 'Profile update request submitted. An administrator will review it shortly.');
    }

    /** Admin: list all pending (and recent) profile update requests */
    public function index(Request $request): View
    {
        $status = $request->get('status', 'pending');
        $requests = ProfileUpdateRequest::with('employee.user', 'reviewer')
            ->when($status !== 'all', fn($q) => $q->where('status', $status))
            ->latest()
            ->paginate(20);

        $pendingCount = ProfileUpdateRequest::where('status', 'pending')->count();

        return view('profile-updates.index', compact('requests', 'status', 'pendingCount'));
    }

    /** Admin: approve and apply the requested changes */
    public function approve(Request $request, ProfileUpdateRequest $profileRequest): RedirectResponse
    {
        abort_if(!$request->user()->isAdmin(), 403);
        abort_if(!$profileRequest->isPending(), 422, 'This request is not pending.');

        $employee = $profileRequest->employee;
        $allowed  = array_keys(ProfileUpdateRequest::FIELD_LABELS);
        $changes  = array_intersect_key($profileRequest->requested_fields, array_flip($allowed));

        $employee->update($changes);

        $profileRequest->update([
            'status'      => 'approved',
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        // Notify the employee
        try {
            $employee->user->notify(new \App\Notifications\ProfileUpdateDecisionNotification($profileRequest));
        } catch (\Exception $e) {}

        return back()->with('success', "Profile update for {$employee->user?->name} approved and applied.");
    }

    /** Admin: reject the request */
    public function reject(Request $request, ProfileUpdateRequest $profileRequest): RedirectResponse
    {
        abort_if(!$request->user()->isAdmin(), 403);
        abort_if(!$profileRequest->isPending(), 422, 'This request is not pending.');

        $request->validate(['admin_note' => 'nullable|string|max:500']);

        $profileRequest->update([
            'status'      => 'rejected',
            'admin_note'  => $request->admin_note,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        try {
            $profileRequest->employee->user->notify(
                new \App\Notifications\ProfileUpdateDecisionNotification($profileRequest)
            );
        } catch (\Exception $e) {}

        return back()->with('success', 'Request rejected.');
    }
}
