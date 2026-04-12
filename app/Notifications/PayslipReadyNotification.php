<?php

namespace App\Notifications;

use App\Models\Payslip;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PayslipReadyNotification extends Notification
{
    use Queueable;

    public function __construct(public readonly Payslip $payslip) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title'   => 'Payslip Ready',
            'message' => "Your payslip for {$this->payslip->payroll->periodLabel()} is ready. Net salary: NLE " . number_format($this->payslip->net_salary, 2),
            'url'     => route('payslips.show', $this->payslip->id),
            'icon'    => 'payslip',
        ];
    }
}
