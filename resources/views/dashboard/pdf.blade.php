<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Dashboard Report</title>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: Arial, sans-serif; font-size: 12px; color: #1a1a2e; padding: 30px; background: #fff; }

  .header { text-align: center; border-bottom: 3px solid #1a1a2e; padding-bottom: 16px; margin-bottom: 24px; }
  .header h1 { font-size: 22px; font-weight: bold; color: #1a1a2e; }
  .header .subtitle { font-size: 12px; color: #555; margin-top: 4px; }
  .header .generated { font-size: 10px; color: #888; margin-top: 6px; }

  h2 { font-size: 14px; font-weight: bold; color: #1a1a2e; border-bottom: 1px solid #ddd; padding-bottom: 6px; margin-bottom: 12px; margin-top: 20px; }

  .stats-grid { display: table; width: 100%; border-collapse: separate; border-spacing: 10px; margin-bottom: 4px; }
  .stats-row { display: table-row; }
  .stat-cell { display: table-cell; width: 25%; background: #f4f6fb; border-radius: 6px; padding: 12px 14px; vertical-align: top; border: 1px solid #e2e6ef; }
  .stat-cell .value { font-size: 20px; font-weight: bold; color: #1a1a2e; }
  .stat-cell .label { font-size: 10px; color: #666; margin-top: 3px; text-transform: uppercase; letter-spacing: 0.4px; }

  table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
  table th { background: #1a1a2e; color: #fff; padding: 7px 10px; font-size: 10px; text-align: left; text-transform: uppercase; letter-spacing: 0.5px; }
  table td { padding: 7px 10px; border-bottom: 1px solid #eee; font-size: 11px; }
  table tr:last-child td { border-bottom: none; }
  table tr:nth-child(even) td { background: #f9f9f9; }

  .badge { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 10px; font-weight: bold; }
  .badge-success { background: #d4edda; color: #155724; }
  .badge-warning { background: #fff3cd; color: #856404; }
  .badge-info    { background: #cce5ff; color: #004085; }
  .badge-danger  { background: #f8d7da; color: #721c24; }

  .two-col { display: table; width: 100%; border-collapse: separate; border-spacing: 10px; }
  .two-col-left  { display: table-cell; width: 50%; vertical-align: top; }
  .two-col-right { display: table-cell; width: 50%; vertical-align: top; }

  .section-box { background: #f4f6fb; border: 1px solid #e2e6ef; border-radius: 6px; padding: 12px 14px; margin-bottom: 10px; }
  .section-box .row { display: flex; justify-content: space-between; padding: 4px 0; border-bottom: 1px solid #eee; font-size: 11px; }
  .section-box .row:last-child { border-bottom: none; }
  .section-box .row .lbl { color: #555; }
  .section-box .row .val { font-weight: bold; }

  .payroll-highlight { background: #1a1a2e; color: #fff; padding: 14px 18px; border-radius: 6px; margin-bottom: 14px; }
  .payroll-highlight .ph-label { font-size: 10px; opacity: 0.7; text-transform: uppercase; letter-spacing: 0.5px; }
  .payroll-highlight .ph-value { font-size: 22px; font-weight: bold; margin-top: 2px; }
  .payroll-highlight .ph-sub   { font-size: 11px; opacity: 0.8; margin-top: 4px; }

  .timeline-item { padding: 6px 0 6px 14px; border-left: 2px solid #1a1a2e; margin-left: 6px; margin-bottom: 4px; font-size: 11px; }
  .timeline-item .t-who   { font-weight: bold; }
  .timeline-item .t-when  { color: #888; font-size: 10px; }
  .timeline-item .t-event { color: #1a1a2e; }

  .footer { text-align: center; margin-top: 30px; font-size: 10px; color: #aaa; border-top: 1px solid #eee; padding-top: 10px; }

  .text-green  { color: #28a745; font-weight: bold; }
  .text-red    { color: #dc3545; font-weight: bold; }
  .text-muted  { color: #888; }
  .text-right  { text-align: right; }
</style>
</head>
<body>

  {{-- ── Header ──────────────────────────────────────────────── --}}
  <div class="header">
    <h1>IIMS Payroll &amp; HR — Dashboard Report</h1>
    <div class="subtitle">{{ $now->format('F Y') }} Snapshot</div>
    <div class="generated">Generated on {{ $now->format('d M Y, H:i') }} by {{ auth()->user()->name }}</div>
  </div>

  {{-- ── Key Metrics ─────────────────────────────────────────── --}}
  <h2>Key Metrics</h2>
  <div style="margin-bottom:14px;">
    <table style="width:100%;border-collapse:collapse;">
      <tr>
        <td style="width:25%;padding:10px 12px;background:#f4f6fb;border:1px solid #e2e6ef;border-radius:4px;">
          <div style="font-size:20px;font-weight:bold;">{{ $totalEmployees }}</div>
          <div style="font-size:10px;color:#666;text-transform:uppercase;margin-top:2px;">Active Employees</div>
        </td>
        <td style="width:4px;"></td>
        <td style="width:25%;padding:10px 12px;background:#f4f6fb;border:1px solid #e2e6ef;border-radius:4px;">
          <div style="font-size:20px;font-weight:bold;">{{ $totalDepartments }}</div>
          <div style="font-size:10px;color:#666;text-transform:uppercase;margin-top:2px;">Departments</div>
        </td>
        <td style="width:4px;"></td>
        <td style="width:25%;padding:10px 12px;background:#f4f6fb;border:1px solid #e2e6ef;border-radius:4px;">
          <div style="font-size:20px;font-weight:bold;">{{ $todayPresent }}</div>
          <div style="font-size:10px;color:#666;text-transform:uppercase;margin-top:2px;">Present Today</div>
        </td>
        <td style="width:4px;"></td>
        <td style="width:25%;padding:10px 12px;background:#f4f6fb;border:1px solid #e2e6ef;border-radius:4px;">
          <div style="font-size:20px;font-weight:bold;">{{ $pendingLeaves }}</div>
          <div style="font-size:10px;color:#666;text-transform:uppercase;margin-top:2px;">Pending Leaves</div>
        </td>
      </tr>
    </table>
  </div>

  {{-- ── Payroll This Month ───────────────────────────────────── --}}
  <h2>Payroll — {{ $now->format('F Y') }}</h2>
  <div class="payroll-highlight">
    <div class="ph-label">Total Net Salary Paid</div>
    <div class="ph-value">NLE {{ number_format($totalPayrollThisMonth, 2) }}</div>
    @if($currentPayroll)
    <div class="ph-sub">Status:
      @if($currentPayroll->status === 'paid') Paid
      @elseif($currentPayroll->status === 'processed') Processed — awaiting payment
      @else Draft
      @endif
    </div>
    @else
    <div class="ph-sub">No payroll processed yet this month.</div>
    @endif
  </div>

  {{-- ── Attendance & Headcount ───────────────────────────────── --}}
  <h2>Attendance &amp; Headcount</h2>
  <table>
    <thead>
      <tr><th>Metric</th><th>Value</th></tr>
    </thead>
    <tbody>
      <tr><td>Present Today</td><td>{{ $todayPresent }}</td></tr>
      <tr><td>Absent Today</td><td>{{ $todayAbsent }}</td></tr>
      <tr><td>On Leave Today</td><td>{{ $onLeaveToday }}</td></tr>
      <tr><td>Pending Overtime Requests</td><td>{{ $pendingOvertimes }}</td></tr>
    </tbody>
  </table>

  {{-- ── Department Headcount ─────────────────────────────────── --}}
  <h2>Department Headcount</h2>
  <table>
    <thead>
      <tr><th>Department</th><th class="text-right">Active Employees</th></tr>
    </thead>
    <tbody>
      @foreach($deptData as $dept)
      <tr>
        <td>{{ $dept->name }}</td>
        <td class="text-right">{{ $dept->employees_count }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>

  {{-- ── Pending Leave Requests ───────────────────────────────── --}}
  <h2>Pending Leave Requests</h2>
  @if($pendingLeaveRequests->isEmpty())
    <p class="text-muted" style="font-size:11px;padding:8px 0;">No pending leave requests.</p>
  @else
  <table>
    <thead>
      <tr><th>Employee</th><th>Type</th><th>From</th><th>To</th><th>Days</th></tr>
    </thead>
    <tbody>
      @foreach($pendingLeaveRequests as $leave)
      <tr>
        <td>{{ $leave->employee->user->name ?? '—' }}</td>
        <td>{{ $leave->leaveType->name ?? '—' }}</td>
        <td>{{ $leave->start_date->format('d M Y') }}</td>
        <td>{{ $leave->end_date->format('d M Y') }}</td>
        <td>{{ $leave->total_days }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>
  @endif

  {{-- ── Upcoming Events ──────────────────────────────────────── --}}
  <h2>Upcoming Events (Next 7 Days)</h2>
  @if($upcomingEvents->isEmpty())
    <p class="text-muted" style="font-size:11px;padding:8px 0;">No upcoming events in the next 7 days.</p>
  @else
  <table>
    <thead>
      <tr><th>Employee</th><th>Event</th><th>Date</th></tr>
    </thead>
    <tbody>
      @foreach($upcomingEvents as $event)
      <tr>
        <td>{{ $event['name'] }}</td>
        <td>
          @if($event['type'] === 'birthday')
            <span class="badge badge-info">Birthday</span>
          @elseif($event['type'] === 'anniversary')
            <span class="badge badge-success">Work Anniversary — {{ $event['detail'] }}</span>
          @else
            <span class="badge badge-warning">Doc Expiry: {{ $event['detail'] }}</span>
          @endif
        </td>
        <td>{{ \Carbon\Carbon::parse($event['date'])->format('d M Y') }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>
  @endif

  {{-- ── Recent Activity ──────────────────────────────────────── --}}
  <h2>Recent Activity</h2>
  @if($recentActivity->isEmpty())
    <p class="text-muted" style="font-size:11px;padding:8px 0;">No recent activity.</p>
  @else
  <table>
    <thead>
      <tr><th>When</th><th>User</th><th>Event</th><th>Subject</th></tr>
    </thead>
    <tbody>
      @foreach($recentActivity as $activity)
      <tr>
        <td style="white-space:nowrap;">{{ $activity->created_at->format('d M Y, H:i') }}</td>
        <td>{{ $activity->causer?->name ?? 'System' }}</td>
        <td>{{ ucfirst($activity->event ?? 'log') }}</td>
        <td>{{ class_basename($activity->subject_type ?? '—') }}@if($activity->subject_id) #{{ $activity->subject_id }}@endif</td>
      </tr>
      @endforeach
    </tbody>
  </table>
  @endif

  <div class="footer">IIMS Payroll &amp; HR System — Confidential — {{ $now->format('d M Y') }}</div>
</body>
</html>
