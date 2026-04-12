<?php
namespace App\Mail;

use App\Models\LeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LeaveStatusUpdated extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public LeaveRequest $leaveRequest) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Leave Request ' . ucfirst($this->leaveRequest->status));
    }

    public function content(): Content
    {
        return new Content(view: 'emails.leave-status-updated');
    }
}
