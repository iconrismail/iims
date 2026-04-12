@extends('layouts.app')

@section('title', 'Create Performance Review')
@section('page-title', 'Create Performance Review')

@section('content')
    <div class="card" style="max-width: 720px;">
        <div class="card-header">
            <h3 class="card-title">New Performance Review</h3>
        </div>

        <form action="{{ route('performance.store') }}" method="POST" id="review-form">
            @csrf

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem; margin-bottom: 0.5rem;">
                <div class="form-group">
                    <label class="form-label">Employee</label>
                    <select name="employee_id" class="form-control" required>
                        <option value="">-- Select --</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}" {{ old('employee_id') == $emp->id ? 'selected' : '' }}>
                                {{ $emp->user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Review Period</label>
                    <select name="review_period" class="form-control" required>
                        <option value="">-- Period --</option>
                        @foreach(['Q1', 'Q2', 'Q3', 'Q4', 'annual'] as $p)
                            <option value="{{ $p }}" {{ old('review_period') === $p ? 'selected' : '' }}>{{ $p }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Year</label>
                    <select name="period_year" class="form-control" required>
                        @for($y = date('Y') + 1; $y >= date('Y') - 2; $y--)
                            <option value="{{ $y }}" {{ old('period_year', date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
            </div>

            {{-- KPI Scores --}}
            <h4 class="card-title" style="margin: 1.5rem 0 1rem; color: var(--accent);">KPI Scores</h4>

            @foreach($categories as $category)
                <div class="form-group" style="background: var(--bg-secondary); border-radius: var(--radius); padding: 1rem 1.25rem; margin-bottom: 0.75rem;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.5rem;">
                        <div>
                            <div class="form-label" style="margin-bottom: 0.2rem;">{{ $category->name }}</div>
                            @if($category->description)
                                <div style="font-size: 0.78rem; color: var(--text-muted);">{{ $category->description }}</div>
                            @endif
                        </div>
                        <span class="badge badge-info">Weight: {{ $category->weight }}%</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <input type="range" name="scores[{{ $category->id }}]"
                            id="score_{{ $category->id }}"
                            min="1" max="10" step="1"
                            value="{{ old("scores.{$category->id}", 5) }}"
                            style="flex: 1; accent-color: var(--accent);"
                            oninput="updateScore({{ $category->id }}, this.value)">
                        <div style="min-width: 50px; text-align: center;">
                            <span id="score_display_{{ $category->id }}"
                                style="font-size: 1.25rem; font-weight: 700; color: var(--accent);">
                                {{ old("scores.{$category->id}", 5) }}
                            </span>
                            <span style="color: var(--text-muted); font-size: 0.85rem;">/10</span>
                        </div>
                    </div>
                </div>
            @endforeach

            {{-- Overall Score Preview --}}
            <div id="overall-score-box" style="background: var(--bg-card); border: 1px solid var(--border-accent); border-radius: var(--radius); padding: 1rem 1.25rem; margin: 1rem 0; text-align: center;">
                <div style="font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Overall Score (Weighted)</div>
                <div id="overall-score-display" style="font-size: 2.5rem; font-weight: 700; color: var(--accent); margin: 0.25rem 0;">5.0</div>
                <div style="font-size: 0.8rem; color: var(--text-secondary);">out of 10</div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label class="form-label">Salary Increment Recommendation (%)</label>
                    <input type="number" name="salary_increment_pct" id="salary_increment_pct" class="form-control"
                        min="0" max="50" step="0.5" value="{{ old('salary_increment_pct', 0) }}" required>
                    <div id="increment-suggestion" style="margin-top:0.4rem;font-size:0.78rem;color:var(--accent);display:none;">
                        AI Suggestion: <strong id="increment-suggest-val"></strong>% &mdash;
                        <span id="increment-suggest-band" style="color:var(--text-muted);"></span>
                        <button type="button" id="apply-suggestion" style="margin-left:0.5rem;background:none;border:none;color:var(--accent);cursor:pointer;font-size:0.78rem;text-decoration:underline;">Apply</button>
                    </div>
                    <span class="form-hint">Enter 0 for no increment recommendation</span>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Comments <span style="color: var(--text-muted)">(optional)</span></label>
                <textarea name="comments" class="form-control" rows="4" placeholder="Overall review comments and feedback...">{{ old('comments') }}</textarea>
            </div>

            <div class="btn-group">
                <button type="submit" class="btn btn-primary">Create Review (Save as Draft)</button>
                <a href="{{ route('performance.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
<script>
    const categories = @json($categories->map(fn($c) => ['id' => $c->id, 'weight' => (float) $c->weight]));

    function updateScore(catId, value) {
        document.getElementById('score_display_' + catId).textContent = value;
        recalcOverall();
    }

    function recalcOverall() {
        let totalWeight = 0;
        let weightedSum = 0;

        categories.forEach(cat => {
            const el = document.getElementById('score_' + cat.id);
            if (el) {
                const score = parseFloat(el.value);
                weightedSum += score * cat.weight;
                totalWeight += cat.weight;
            }
        });

        const overall = totalWeight > 0 ? weightedSum / totalWeight : 0;
        const display = document.getElementById('overall-score-display');
        display.textContent = overall.toFixed(1);

        if (overall >= 7) {
            display.style.color = 'var(--success)';
        } else if (overall >= 5) {
            display.style.color = 'var(--warning)';
        } else {
            display.style.color = 'var(--danger)';
        }
    }

    // Init
    document.querySelectorAll('input[type=range]').forEach(el => {
        el.addEventListener('input', recalcOverall);
    });
    recalcOverall();

    // ── AI Increment Suggestion ─────────────────────────────
    const incrementBands = [
        { min: 9.0, suggested: 8.0, label: 'Exceptional (9.0–10)' },
        { min: 7.5, suggested: 6.0, label: 'Above Average (7.5–8.9)' },
        { min: 6.0, suggested: 3.5, label: 'Meets Expectations (6.0–7.4)' },
        { min: 4.0, suggested: 1.0, label: 'Below Average (4.0–5.9)' },
        { min: 0,   suggested: 0.0, label: 'Needs Improvement (<4.0)' },
    ];

    function updateIncrementSuggestion(score) {
        const band = incrementBands.find(b => score >= b.min);
        const box  = document.getElementById('increment-suggestion');
        if (!band || band.suggested === 0) { box.style.display = 'none'; return; }
        document.getElementById('increment-suggest-val').textContent  = band.suggested.toFixed(1);
        document.getElementById('increment-suggest-band').textContent = band.label;
        box.style.display = 'block';
        document.getElementById('apply-suggestion').onclick = () => {
            document.getElementById('salary_increment_pct').value = band.suggested.toFixed(1);
        };
    }

    // Hook into the existing recalcOverall to also update suggestion
    const _origRecalc = recalcOverall;
    recalcOverall = function() {
        _origRecalc();
        const display = document.getElementById('overall-score-display');
        if (display) updateIncrementSuggestion(parseFloat(display.textContent));
    };
    recalcOverall();
</script>
@endpush
