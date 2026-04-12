<?php

namespace App\Notifications;

use App\Models\ProfileUpdateRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ProfileUpdateDecisionNotification extends Notification
{
    use Queueable;

    public function __construct(public readonly ProfileUpdateRequest $profileRequest) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $status  = $this->profileRequest->status;
        $fields  = implode(', ', array_map(
            fn($f) => \App\Models\ProfileUpdateRequest::FIELD_LABELS[$f] ?? $f,
            array_keys($this->profileRequest->requested_fields)
        ));

        if ($status === 'approved') {
            return [
                'title'   => 'Profile Update Approved',
                'message' => "Your request to update {$fields} has been approved and applied.",
                'url'     => route('profile-updates.create'),
                'icon'    => 'success',
            ];
        }

        return [
            'title'   => 'Profile Update Rejected',
            'message' => "Your request to update {$fields} was rejected."
                . ($this->profileRequest->admin_note ? " Reason: {$this->profileRequest->admin_note}" : ''),
            'url'     => route('profile-updates.create'),
            'icon'    => 'danger',
        ];
    }
}
