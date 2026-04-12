@extends('layouts.app')

@section('title', 'Performance Review')
@section('page-title', 'Performance Review')

@section('content')
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem;">
        {{-- Main Review Card --}}
        <div class="card">
            <div class="card-header">
                <div>
                    <h3 class="card-title">{{ $review->review_period }} {{ $review->period_year }} Review</h3>
                    <div style="font-size: 0.85rem; color: var(--text-secondary); margin-top: 0.25rem;">
                        {{ $review->employee->user->name }} &bull; Reviewed by {{ $review->reviewer->name }}
                    </div>
                </div>
                @if($review->status === 'acknowledged')
                    <span class="badge badge-success">Acknowledged</span>
                @elseif($review->status === 'submitted')
                    <span class="badge badge-info">Submitted</span>
                @else
                    <span class="badge badge-warning">Draft</span>
                @endif
            </div>

            {{-- Overall Score --}}
            @php
                $score = (float) $review->overall_score;
                $scoreColor = $score >= 7 ? 'var(--success)' : ($score >= 5 ? 'var(--warning)' : 'var(--danger)');
                $scorePct = ($score / 10) * 100;
            @endphp
            <div style="text-align: center; padding: 1.5rem; background: var(--bg-secondary); border-radius: var(--radius); margin-bottom: 1.5rem;">
                <div style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 0.5rem;">Overall Score</div>
                <div style="font-size: 3.5rem; font-weight: 700; color: {{ $scoreColor }}; line-height: 1;">{{ number_format($score, 1) }}</div>
                <div style="font-size: 1rem; color: var(--text-muted);">out of 10</div>
                <div style="background: var(--bg-primary); border-radius: 99px; height: 8px; margin: 1rem auto; max-width: 300px; overflow: hidden;">
                    <div style="width: {{ $scorePct }}%; height: 100%; background: {{ $scoreColor }}; border-radius: 99px; transition: width 0.5s ease;"></div>
                </div>
            </div>

            {{-- KPI Breakdown --}}
            <h4 class="card-title" style="margin-bottom: 1rem;">KPI Breakdown</h4>
            @foreach($categories as $cat)
                @php
                    $catScore = isset($review->scores[$cat->id]) ? (float) $review->scores[$cat->id] : 0;
                    $catColor = $catScore >= 7 ? 'var(--success)' : ($catScore >= 5 ? 'var(--warning)' : 'var(--danger)');
                    $catPct = ($catScore / 10) * 100;
                @endphp
                <div style="margin-bottom: 1rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.4rem;">
                        <div>
                            <span style="font-weight: 600; font-size: 0.9rem;">{{ $cat->name }}</span>
                            <span style="color: var(--text-muted); font-size: 0.78rem; margin-left: 0.5rem;">{{ $cat->weight }}%</span>
                        </div>
                        <span style="font-weight: 700; color: {{ $catColor }};">{{ $catScore }}/10</span>
                    </div>
                    <div style="background: var(--bg-primary); border-radius: 99px; height: 6px; overflow: hidden;">
                        <div style="width: {{ $catPct }}%; height: 100%; background: {{ $catColor }}; border-radius: 99px;"></div>
                    </div>
                </div>
            @endforeach

            @if($review->comments)
                <div style="margin-top: 1.5rem; background: var(--bg-secondary); border-radius: var(--radius); padding: 1rem 1.25rem;">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.5rem;">
                        <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted);">Comments</div>
                        @if(auth()->user()->isAdmin())
                            <button id="ai-summary-btn" type="button"
                                style="font-size:0.72rem;background:var(--accent)18;color:var(--accent);border:1px solid var(--accent)44;border-radius:6px;padding:0.2rem 0.6rem;cursor:pointer;">
                                ✦ AI Summary
                            </button>
                        @endif
                    </div>
                    <p style="color: var(--text-secondary); line-height: 1.6; margin: 0;">{{ $review->comments }}</p>
                    @if(auth()->user()->isAdmin())
                        <div id="ai-summary-box" style="display:none;margin-top:0.75rem;padding:0.65rem 0.85rem;background:var(--accent)0d;border-left:3px solid var(--accent);border-radius:0 6px 6px 0;font-size:0.82rem;color:var(--text-primary);line-height:1.5;font-style:italic;">
                        </div>
                    @endif
                </div>
            @endif
        </div>

        {{-- Sidebar --}}
        <div>
            {{-- Info Card --}}
            <div class="card" style="margin-bottom: 1.5rem;">
                <div class="card-header">
                    <h3 class="card-title">Review Info</h3>
                </div>
                <div class="payslip-row">
                    <span class="label">Employee</span>
                    <span class="value">{{ $review->employee->user->name }}</span>
                </div>
                <div class="payslip-row">
                    <span class="label">Employee ID</span>
                    <span class="value text-accent">{{ $review->employee->employee_id }}</span>
                </div>
                <div class="payslip-row">
                    <span class="label">Period</span>
                    <span class="value">{{ $review->review_period }} {{ $review->period_year }}</span>
                </div>
                <div class="payslip-row">
                    <span class="label">Reviewer</span>
                    <span class="value">{{ $review->reviewer->name }}</span>
                </div>
                @if($review->salary_increment_pct > 0)
                    <div class="payslip-row">
                        <span class="label">Salary Increment</span>
                        <span class="value" style="color: var(--success);">+{{ $review->salary_increment_pct }}%</span>
                    </div>
                @endif
                @if($review->acknowledged_at)
                    <div class="payslip-row">
                        <span class="label">Acknowledged</span>
                        <span class="value">{{ $review->acknowledged_at->format('M d, Y') }}</span>
                    </div>
                @endif
            </div>

            {{-- Status Timeline --}}
            <div class="card" style="margin-bottom: 1.5rem;">
                <div class="card-header">
                    <h3 class="card-title">Status Timeline</h3>
                </div>
                <div style="padding: 0.5rem 0;">
                    @foreach(['draft' => 'Created', 'submitted' => 'Submitted', 'acknowledged' => 'Acknowledged'] as $step => $label)
                        @php
                            $steps = ['draft', 'submitted', 'acknowledged'];
                            $currentIdx = array_search($review->status, $steps);
                            $stepIdx = array_search($step, $steps);
                            $isDone = $stepIdx <= $currentIdx;
                        @endphp
                        <div style="display: flex; align-items: center; gap: 0.75rem; padding: 0.5rem 0;">
                            <div style="width: 24px; height: 24px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 700; flex-shrink: 0;
                                background: {{ $isDone ? 'var(--accent)' : 'var(--bg-secondary)' }};
                                color: {{ $isDone ? 'var(--bg-primary)' : 'var(--text-muted)' }};">
                                {{ $stepIdx + 1 }}
                            </div>
                            <span style="color: {{ $isDone ? 'var(--text-primary)' : 'var(--text-muted)' }}; font-size: 0.9rem;">{{ $label }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- AI: Increment Suggestion --}}
            <div class="card" style="margin-bottom:1.5rem;border-color:var(--accent)33;">
                <div class="card-header">
                    <h3 class="card-title" style="font-size:0.85rem;">✦ AI Increment Insight</h3>
                </div>
                <div style="padding:0.25rem 0;">
                    <div class="payslip-row">
                        <span class="label">Score Band</span>
                        <span class="value" style="font-size:0.8rem;">{{ $incrementSuggestion['band'] }}</span>
                    </div>
                    <div class="payslip-row">
                        <span class="label">AI Suggested</span>
                        <span class="value" style="color:var(--accent);font-weight:700;">{{ $incrementSuggestion['suggested'] }}%</span>
                    </div>
                    @if($incrementSuggestion['avg_given'] !== null)
                        <div class="payslip-row">
                            <span class="label">Company Avg</span>
                            <span class="value" style="font-size:0.8rem;">{{ $incrementSuggestion['avg_given'] }}% <span style="color:var(--text-muted);font-size:0.72rem;">({{ $incrementSuggestion['basis'] }} reviews)</span></span>
                        </div>
                    @endif
                </div>
            </div>

            @if($reviewerBias)
                <div style="background:#f59e0b12;border:1px solid #f59e0b44;border-radius:var(--radius);padding:0.75rem 1rem;margin-bottom:1.5rem;">
                    <div style="font-size:0.72rem;color:#f59e0b;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:0.4rem;">Reviewer Bias Detected</div>
                    <div style="font-size:0.8rem;color:var(--text-secondary);line-height:1.5;">
                        {{ $reviewerBias['name'] }} scores
                        {{ $reviewerBias['direction'] === 'high' ? 'consistently higher' : 'consistently lower' }}
                        than average (<strong style="color:#f59e0b;">{{ $reviewerBias['avg_score'] }}/10</strong> vs company avg {{ $reviewerBias['company_avg'] }}/10,
                        across {{ $reviewerBias['review_count'] }} reviews).
                    </div>
                </div>
            @endif

            {{-- Actions --}}
            <div class="btn-group" style="flex-direction: column;">
                @if(auth()->user()->isAdminOrManager() && $review->status === 'draft')
                    <form action="{{ route('performance.submit', $review) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-primary" style="width: 100%;">Submit Review</button>
                    </form>
                @endif
                @if(auth()->user()->isEmployee() && $review->status === 'submitted')
                    <form action="{{ route('performance.acknowledge', $review) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-success" style="width: 100%;">Acknowledge Review</button>
                    </form>
                @endif
                <a href="{{ route('performance.index') }}" class="btn btn-secondary" style="width: 100%; text-align: center;">Back to Reviews</a>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
@if(auth()->user()->isAdmin() && $review->comments)
<script>
(function () {
    const btn = document.getElementById('ai-summary-btn');
    const box = document.getElementById('ai-summary-box');
    if (!btn || !box) return;

    btn.addEventListener('click', async () => {
        btn.disabled = true;
        btn.textContent = '✦ Loading…';
        box.style.display = 'block';
        box.textContent = 'Generating summary…';

        try {
            const res  = await fetch('{{ route("performance.aiSummary", $review) }}', { headers: { 'Accept': 'application/json' } });
            const data = await res.json();
            if (data.summary) {
                box.textContent = '"' + data.summary + '"';
                btn.textContent = '✦ AI Summary';
            } else {
                box.textContent = data.error ?? 'Could not generate summary.';
                box.style.borderLeftColor = '#ef4444';
            }
        } catch (_) {
            box.textContent = 'Connection error.';
        }

        btn.disabled = false;
    });
})();
</script>
@endif
@endpush
