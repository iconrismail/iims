@extends('layouts.app')

@section('title', 'Payroll')
@section('page-title', 'Payroll Management')

@section('content')
    {{-- Queue status banner --}}
    @if($pendingJobs > 0)
        <div class="alert" style="background:rgba(108,99,255,0.12);border:1px solid rgba(108,99,255,0.3);color:var(--text-primary);display:flex;align-items:center;gap:0.75rem;margin-bottom:1rem">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#6c63ff" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            <span><strong>{{ $pendingJobs }}</strong> payroll job(s) queued. Run <code style="background:rgba(0,0,0,0.3);padding:2px 6px;border-radius:3px">php artisan queue:work</code> to process them.</span>
            <a href="{{ route('queue.monitor') }}" class="btn btn-sm btn-secondary" style="margin-left:auto">View Queue</a>
        </div>
    @endif

    {{-- Filter --}}
    <div class="card mb-3">
        <form method="GET" action="{{ route('payroll.index') }}" style="display:flex;gap:0.75rem;flex-wrap:wrap;align-items:flex-end">
            <div class="form-group" style="margin:0;min-width:120px">
                <label class="form-label">Year</label>
                <select name="year" class="form-control">
                    <option value="">All Years</option>
                    @for($y = date('Y')-3; $y <= date('Y')+1; $y++)
                        <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <div class="form-group" style="margin:0;min-width:140px">
                <label class="form-label">Status</label>
                <select name="status" class="form-control">
                    <option value="">All Statuses</option>
                    <option value="draft"     {{ request('status') === 'draft'     ? 'selected' : '' }}>Draft</option>
                    <option value="processed" {{ request('status') === 'processed' ? 'selected' : '' }}>Processed</option>
                    <option value="paid"      {{ request('status') === 'paid'      ? 'selected' : '' }}>Paid</option>
                </select>
            </div>
            <div style="display:flex;gap:0.5rem;padding-bottom:1px">
                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                @if(request()->hasAny(['year','status']))
                    <a href="{{ route('payroll.index') }}" class="btn btn-secondary btn-sm">Clear</a>
                @endif
            </div>
        </form>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Payroll Periods</h3>
            <a href="{{ route('payroll.create') }}" class="btn btn-primary btn-sm">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Process Payroll
            </a>
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Period</th>
                        <th>Status</th>
                        <th>Payslips</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payrolls as $payroll)
                        <tr>
                            <td class="font-bold">{{ $payroll->periodLabel() }}</td>
                            <td>
                                @if($payroll->status === 'paid')
                                    <span class="badge badge-success">Paid</span>
                                @elseif($payroll->status === 'processed')
                                    <span class="badge badge-info">Processed</span>
                                @else
                                    <span class="badge badge-warning">Draft</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-info">{{ $payroll->payslips_count }}</span>
                            </td>
                            <td class="text-secondary">{{ $payroll->created_at->format('M d, Y') }}</td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('payroll.show', $payroll) }}" class="btn btn-sm btn-secondary">View</a>
                                    @if($payroll->status === 'processed')
                                        <form action="{{ route('payroll.markPaid', $payroll) }}" method="POST"
                                              onsubmit="return confirm('Mark this payroll as paid?')">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-success">Mark Paid</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted" style="padding: 2rem">No payroll periods found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($payrolls->hasPages())
            <div class="pagination-wrapper">
                {{ $payrolls->links() }}
            </div>
        @endif
    </div>
@endsection
