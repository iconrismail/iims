<?php

namespace App\Notifications;

use App\Models\LeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LeaveStatusNotification extends Notification
{
    use Queueable;

    public function __construct(public readonly LeaveRequest $leaveRequest) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $status = ucfirst($this->leaveRequest->status);
        $type   = $this->leaveRequest->leaveType->name;

        return [
            'title'   => "Leave Request {$status}",
            'message' => "Your {$type} request has been {$this->leaveRequest->status}.",
            'url'     => route('leaves.show', $this->leaveRequest->id),
            'icon'    => $this->leaveRequest->status === 'approved' ? 'success' : 'danger',
        ];
    }
}
