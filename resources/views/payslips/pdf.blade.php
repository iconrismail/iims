<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Payslip</title>
<style>
  body { font-family: Arial, sans-serif; font-size: 13px; color: #222; margin: 0; padding: 30px; }
  .header { text-align: center; border-bottom: 2px solid #1a1a2e; padding-bottom: 15px; margin-bottom: 20px; }
  .header h1 { margin: 0; font-size: 22px; color: #1a1a2e; }
  .header p { margin: 4px 0; color: #555; font-size: 12px; }
  .badge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: bold; }
  .badge-paid { background: #d4edda; color: #155724; }
  .badge-processed { background: #cce5ff; color: #004085; }
  .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; background: #f8f9fa; padding: 15px; border-radius: 6px; margin-bottom: 20px; }
  .info-item label { font-size: 10px; text-transform: uppercase; color: #888; display: block; }
  .info-item span { font-weight: bold; font-size: 13px; }
  h3 { margin: 0 0 10px; font-size: 14px; color: #1a1a2e; border-bottom: 1px solid #eee; padding-bottom: 6px; }
  .row { display: flex; justify-content: space-between; padding: 7px 0; border-bottom: 1px solid #f0f0f0; }
  .row .label { color: #555; }
  .row .value { font-weight: bold; }
  .positive { color: #28a745; }
  .negative { color: #dc3545; }
  .total-box { background: #1a1a2e; color: white; padding: 15px 20px; border-radius: 6px; margin-top: 15px; display: flex; justify-content: space-between; align-items: center; }
  .total-box .label { font-size: 14px; opacity: 0.8; }
  .total-box .value { font-size: 20px; font-weight: bold; }
  .attendance-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 15px; }
  .att-box { text-align: center; padding: 12px; border-radius: 6px; }
  .att-box.present { background: #d4edda; }
  .att-box.absent { background: #f8d7da; }
  .att-box .num { font-size: 24px; font-weight: bold; }
  .att-box .lbl { font-size: 11px; color: #555; }
  .footer { text-align: center; margin-top: 30px; font-size: 10px; color: #aaa; border-top: 1px solid #eee; padding-top: 10px; }
</style>
</head>
<body>
  <div class="header">
    <h1>IIMS Payroll System</h1>
    <p>Official Payslip — {{ $payslip->payroll->periodLabel() }}</p>
    <span class="badge {{ $payslip->payroll->status === 'paid' ? 'badge-paid' : 'badge-processed' }}">
      {{ ucfirst($payslip->payroll->status) }}
    </span>
  </div>

  <div class="info-grid">
    <div class="info-item"><label>Employee Name</label><span>{{ $payslip->employee->user->name }}</span></div>
    <div class="info-item"><label>Employee ID</label><span>{{ $payslip->employee->employee_id }}</span></div>
    <div class="info-item"><label>Department</label><span>{{ $payslip->employee->department->name }}</span></div>
    <div class="info-item"><label>Position</label><span>{{ $payslip->employee->position }}</span></div>
  </div>

  <h3>Salary Breakdown</h3>
  <div class="row"><span class="label">Basic Salary</span><span class="value">NLE {{ number_format($payslip->basic_salary, 2) }}</span></div>
  <div class="row"><span class="label">Allowances</span><span class="value positive">+ NLE {{ number_format($payslip->allowances, 2) }}</span></div>
  <div class="row"><span class="label">Deductions</span><span class="value negative">- NLE {{ number_format($payslip->deductions, 2) }}</span></div>
  @if(($payslip->overtime_pay ?? 0) > 0)
  <div class="row"><span class="label">Overtime Pay</span><span class="value positive">+ NLE {{ number_format($payslip->overtime_pay, 2) }}</span></div>
  @endif
  @if(($payslip->bonus ?? 0) > 0)
  <div class="row"><span class="label">Bonus</span><span class="value positive">+ NLE {{ number_format($payslip->bonus, 2) }}</span></div>
  @endif

  <div class="total-box">
    <span class="label">NET SALARY</span>
    <span class="value">NLE {{ number_format($payslip->net_salary, 2) }}</span>
  </div>

  <div class="attendance-grid">
    <div class="att-box present">
      <div class="num">{{ $payslip->days_worked }}</div>
      <div class="lbl">Days Worked</div>
    </div>
    <div class="att-box absent">
      <div class="num">{{ $payslip->days_absent }}</div>
      <div class="lbl">Days Absent</div>
    </div>
  </div>

  <div class="footer">Generated on {{ now()->format('d M Y, H:i') }} — IIMS Payroll System</div>
</body>
</html>
