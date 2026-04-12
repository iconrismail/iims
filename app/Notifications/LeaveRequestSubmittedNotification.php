<?php

namespace App\Notifications;

use App\Models\LeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LeaveRequestSubmittedNotification extends Notification
{
    use Queueable;

    public function __construct(public readonly LeaveRequest $leaveRequest) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $employee = $this->leaveRequest->employee->user->name;
        $type     = $this->leaveRequest->leaveType->name;
        $days     = $this->leaveRequest->total_days;

        return [
            'title'   => 'New Leave Request',
            'message' => "{$employee} submitted a {$type} request for {$days} day(s).",
            'url'     => route('leaves.show', $this->leaveRequest->id),
            'icon'    => 'leave',
        ];
    }
}
