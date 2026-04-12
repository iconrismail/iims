<?php

namespace App\Notifications;

use App\Models\Employee;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ContractExpiryNotification extends Notification
{
    use Queueable;

    /**
     * @param Employee $employee  The employee whose contract/probation is expiring
     * @param string   $type      'contract' or 'probation'
     * @param int      $daysLeft  Days remaining until expiry
     */
    public function __construct(
        public readonly Employee $employee,
        public readonly string   $type,      // 'contract' or 'probation'
        public readonly int      $daysLeft,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $name      = $this->employee->user?->name ?? 'An employee';
        $expiryDate = $this->type === 'contract'
            ? $this->employee->contract_end_date?->format('d M Y')
            : $this->employee->probation_end_date?->format('d M Y');

        $label = $this->type === 'contract' ? 'fixed-term contract' : 'probation period';

        // Personalise the message depending on whether the notifiable IS the employee
        $isEmployee = $notifiable instanceof \App\Models\User
            && $notifiable->employee?->id === $this->employee->id;

        if ($isEmployee) {
            return [
                'title'   => 'Your ' . ucfirst($this->type) . ' Expiring Soon',
                'message' => "Your {$label} expires on {$expiryDate} ({$this->daysLeft} day(s) remaining). Please speak with HR about renewal or transition.",
                'url'     => route('dashboard'),   // employees cannot access employees.show (admin-only)
                'icon'    => 'warning',
            ];
        }

        return [
            'title'   => ucfirst($this->type) . ' Expiry Alert',
            'message' => "{$name}'s {$label} expires on {$expiryDate} ({$this->daysLeft} day(s) remaining).",
            'url'     => route('employees.show', $this->employee->id),  // admins & managers can access this
            'icon'    => 'warning',
        ];
    }
}
