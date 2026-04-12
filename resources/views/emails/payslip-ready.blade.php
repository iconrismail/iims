<!DOCTYPE html><html><body style="font-family:Arial,sans-serif;background:#f4f4f4;padding:30px">
<div style="max-width:500px;margin:0 auto;background:white;border-radius:8px;padding:30px">
  <h2 style="color:#1a1a2e">Your Payslip is Ready</h2>
  <p>Dear {{ $payslip->employee->user->name }},</p>
  <p>Your payslip for <strong>{{ $payslip->payroll->periodLabel() }}</strong> has been processed.</p>
  <table style="width:100%;border-collapse:collapse;margin:20px 0">
    <tr><td style="padding:8px;border-bottom:1px solid #eee;color:#555">Basic Salary</td><td style="padding:8px;border-bottom:1px solid #eee;font-weight:bold">NLE {{ number_format($payslip->basic_salary,2) }}</td></tr>
    <tr><td style="padding:8px;border-bottom:1px solid #eee;color:#555">Allowances</td><td style="padding:8px;border-bottom:1px solid #eee;color:#28a745;font-weight:bold">+ NLE {{ number_format($payslip->allowances,2) }}</td></tr>
    <tr><td style="padding:8px;border-bottom:1px solid #eee;color:#555">Deductions</td><td style="padding:8px;border-bottom:1px solid #eee;color:#dc3545;font-weight:bold">- NLE {{ number_format($payslip->deductions,2) }}</td></tr>
    <tr><td style="padding:10px 8px;font-weight:bold;font-size:16px">Net Salary</td><td style="padding:10px 8px;font-weight:bold;font-size:16px;color:#1a1a2e">NLE {{ number_format($payslip->net_salary,2) }}</td></tr>
  </table>
  <p style="color:#888;font-size:12px">Login to IIMS to download your PDF payslip.</p>
</div></body></html>
