<!DOCTYPE html><html><body style="font-family:Arial,sans-serif;background:#f4f4f4;padding:30px">
<div style="max-width:500px;margin:0 auto;background:white;border-radius:8px;padding:30px">
  <h2 style="color:#1a1a2e">Leave Request {{ ucfirst($leaveRequest->status) }}</h2>
  <p>Dear {{ $leaveRequest->employee->user->name }},</p>
  <p>Your leave request has been <strong style="color:{{ $leaveRequest->status==='approved'?'#28a745':'#dc3545' }}">{{ $leaveRequest->status }}</strong>.</p>
  <table style="width:100%;border-collapse:collapse;margin:15px 0">
    <tr><td style="padding:6px;color:#555">Type</td><td style="padding:6px;font-weight:bold">{{ $leaveRequest->leaveType->name }}</td></tr>
    <tr><td style="padding:6px;color:#555">Period</td><td style="padding:6px;font-weight:bold">{{ $leaveRequest->start_date->format('d M Y') }} – {{ $leaveRequest->end_date->format('d M Y') }}</td></tr>
    <tr><td style="padding:6px;color:#555">Days</td><td style="padding:6px;font-weight:bold">{{ $leaveRequest->total_days }}</td></tr>
    @if($leaveRequest->admin_note)<tr><td style="padding:6px;color:#555">Note</td><td style="padding:6px">{{ $leaveRequest->admin_note }}</td></tr>@endif
  </table>
</div></body></html>
