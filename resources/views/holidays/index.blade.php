@extends('layouts.app')
@section('title', 'Public Holidays')
@section('page-title', 'Public Holiday Calendar')

@section('content')
<div style="display:grid;grid-template-columns:1fr 1.6fr;gap:1.5rem;align-items:start;">

    {{-- Add Holiday Form --}}
    <div class="card" style="align-self:start;">
        <div class="card-header">
            <h3 class="card-title">Add Holiday</h3>
        </div>
        <form method="POST" action="{{ route('holidays.store') }}">
            @csrf
            <div class="form-group">
                <label class="form-label">Date *</label>
                <input type="date" name="date" class="form-control" value="{{ old('date') }}" required>
                <small class="text-muted" style="font-size:.75rem;">For recurring holidays the year is ignored — only month/day matters.</small>
            </div>
            <div class="form-group">
                <label class="form-label">Name *</label>
                <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="e.g. Independence Day" required maxlength="120">
            </div>
            <div class="form-group" style="display:flex;align-items:center;gap:.6rem;">
                <input type="checkbox" id="is_recurring" name="is_recurring" value="1" {{ old('is_recurring', '1') ? 'checked' : '' }} style="width:16px;height:16px;accent-color:var(--accent);">
                <label for="is_recurring" class="form-label" style="margin:0;cursor:pointer;">Recurring every year</label>
            </div>
            <button type="submit" class="btn btn-primary w-full" style="justify-content:center;">Add Holiday</button>
        </form>
    </div>

    {{-- Holiday List --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">All Holidays ({{ $holidays->count() }})</h3>
            <span style="font-size:.75rem;color:var(--text-muted);">Recurring holidays apply every year</span>
        </div>
        @if($holidays->isEmpty())
            <div class="empty-state"><p>No holidays configured yet.</p></div>
        @else
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Next Occurrence</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($holidays as $h)
                        @php
                            $year = now()->year;
                            $next = $h->resolveForYear($year);
                            if ($next && $next->isPast()) {
                                $next = $h->resolveForYear($year + 1);
                            }
                        @endphp
                        <tr>
                            <td style="font-weight:600;">
                                @if($h->is_recurring)
                                    {{ $h->date->format('d M') }}
                                @else
                                    {{ $h->date->format('d M Y') }}
                                @endif
                            </td>
                            <td>{{ $h->name }}</td>
                            <td>
                                @if($h->is_recurring)
                                    <span class="badge badge-info">Recurring</span>
                                @else
                                    <span class="badge badge-secondary">One-off</span>
                                @endif
                            </td>
                            <td style="font-size:.82rem;color:var(--text-muted);">
                                @if($next)
                                    {{ $next->format('d M Y') }}
                                    @if($next->diffInDays(now(), false) <= 0 && $next->diffInDays(now()) <= 30)
                                        <span style="color:#f59e0b;font-size:.72rem;margin-left:.3rem;">soon</span>
                                    @endif
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                <form method="POST" action="{{ route('holidays.destroy', $h) }}"
                                      onsubmit="return confirm('Remove {{ addslashes($h->name) }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Remove</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
