<?php
namespace App\Mail;

use App\Models\Payslip;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PayslipReady extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Payslip $payslip) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Your Payslip is Ready — ' . $this->payslip->payroll->periodLabel());
    }

    public function content(): Content
    {
        return new Content(view: 'emails.payslip-ready');
    }
}
