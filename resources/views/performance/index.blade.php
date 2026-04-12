@extends('layouts.app')

@section('title', 'Performance Reviews')
@section('page-title', 'Performance Reviews')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Performance Reviews</h3>
            @if(auth()->user()->isAdminOrManager())
                <div class="btn-group">
                    <a href="{{ route('performance.create') }}" class="btn btn-primary btn-sm">+ New Review</a>
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('performance.kpi') }}" class="btn btn-secondary btn-sm">KPI Categories</a>
                    @endif
                </div>
            @endif
        </div>

        @if(auth()->user()->isAdminOrManager())
            {{-- Admin / Manager Filters --}}
            <form method="GET" action="{{ route('performance.index') }}" style="display: flex; gap: 0.75rem; flex-wrap: wrap; margin-bottom: 1.5rem;">
                @if($employees->isNotEmpty())
                <select name="employee_id" class="form-control" style="width: auto; min-width: 180px;">
                    <option value="">All Employees</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>
                            {{ $emp->user->name }}
                        </option>
                    @endforeach
                </select>
                @endif
                <select name="period" class="form-control" style="width: auto;">
                    <option value="">All Periods</option>
                    @foreach(['Q1', 'Q2', 'Q3', 'Q4', 'annual'] as $p)
                        <option value="{{ $p }}" {{ request('period') === $p ? 'selected' : '' }}>{{ $p }}</option>
                    @endforeach
                </select>
                <select name="year" class="form-control" style="width: auto;">
                    <option value="">All Years</option>
                    @for($y = date('Y'); $y >= date('Y') - 3; $y--)
                        <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
                <select name="status" class="form-control" style="width: auto;">
                    <option value="">All Status</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="submitted" {{ request('status') === 'submitted' ? 'selected' : '' }}>Submitted</option>
                    <option value="acknowledged" {{ request('status') === 'acknowledged' ? 'selected' : '' }}>Acknowledged</option>
                </select>
                <button type="submit" class="btn btn-secondary btn-sm">Filter</button>
                <a href="{{ route('performance.index') }}" class="btn btn-secondary btn-sm">Clear</a>
            </form>
        @endif

        @if($reviews->count() > 0)
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Period</th>
                            <th>Year</th>
                            <th>Score</th>
                            <th>Increment</th>
                            <th>Status</th>
                            @if(auth()->user()->isAdmin())
                                <th>Reviewer</th>
                            @endif
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reviews as $review)
                            <tr>
                                <td class="font-bold">{{ $review->employee->user->name }}</td>
                                <td>
                                    <span class="badge badge-info">{{ $review->review_period }}</span>
                                </td>
                                <td>{{ $review->period_year }}</td>
                                <td>
                                    @php
                                        $score = (float) $review->overall_score;
                                        $scoreColor = $score >= 7 ? 'var(--success)' : ($score >= 5 ? 'var(--warning)' : 'var(--danger)');
                                    @endphp
                                    <span style="font-weight: 700; font-size: 1.05rem; color: {{ $scoreColor }};">
                                        {{ number_format($score, 1) }}/10
                                    </span>
                                </td>
                                <td>
                                    @if($review->salary_increment_pct > 0)
                                        <span class="badge badge-success">+{{ $review->salary_increment_pct }}%</span>
                                    @else
                                        <span style="color: var(--text-muted);">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($review->status === 'acknowledged')
                                        <span class="badge badge-success">Acknowledged</span>
                                    @elseif($review->status === 'submitted')
                                        <span class="badge badge-info">Submitted</span>
                                    @else
                                        <span class="badge badge-warning">Draft</span>
                                    @endif
                                </td>
                                @if(auth()->user()->isAdmin())
                                    <td style="font-size: 0.85rem; color: var(--text-secondary);">{{ $review->reviewer->name }}</td>
                                @endif
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('performance.show', $review) }}" class="btn btn-sm btn-secondary">View</a>
                                        @if(auth()->user()->isAdmin() && $review->status === 'draft')
                                            <form action="{{ route('performance.submit', $review) }}" method="POST" style="display:inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-primary">Submit</button>
                                            </form>
                                        @endif
                                        @if(!auth()->user()->isAdmin() && $review->status === 'submitted')
                                            <form action="{{ route('performance.acknowledge', $review) }}" method="POST" style="display:inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success">Acknowledge</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="pagination-wrapper">
                {{ $reviews->links() }}
            </div>
        @else
            <div class="empty-state">
                <div style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.4;">📊</div>
                <h3>No performance reviews found</h3>
                @if(auth()->user()->isAdmin())
                    <p>Create performance reviews to evaluate employee performance.</p>
                    <a href="{{ route('performance.create') }}" class="btn btn-primary" style="margin-top: 1rem;">Create Review</a>
                @else
                    <p>You have no performance reviews yet.</p>
                @endif
            </div>
        @endif
    </div>
@endsection
