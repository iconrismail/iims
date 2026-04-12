@extends('layouts.app')

@section('title', 'KPI Categories')
@section('page-title', 'KPI Categories')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">KPI Categories</h3>
        </div>

        @if($categories->count() > 0)
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Category Name</th>
                            <th>Description</th>
                            <th>Weight</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($categories as $cat)
                            <tr>
                                <td class="font-bold">{{ $cat->name }}</td>
                                <td style="color: var(--text-secondary); font-size: 0.85rem;">{{ $cat->description ?? '—' }}</td>
                                <td>
                                    <span class="badge badge-info">{{ $cat->weight }}%</span>
                                </td>
                                <td>
                                    @if($cat->is_active)
                                        <span class="badge badge-success">Active</span>
                                    @else
                                        <span class="badge badge-danger">Inactive</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @php $totalWeight = $categories->sum('weight'); @endphp
            <div style="margin-top: 1rem; padding: 0.75rem 1.25rem; background: var(--bg-secondary); border-radius: var(--radius); display: flex; justify-content: space-between; align-items: center;">
                <span style="color: var(--text-secondary); font-size: 0.9rem;">Total Weight</span>
                <span style="font-weight: 700; color: {{ $totalWeight == 100 ? 'var(--success)' : 'var(--warning)' }};">
                    {{ $totalWeight }}% {{ $totalWeight == 100 ? '✓' : '(should be 100%)' }}
                </span>
            </div>
        @else
            <div class="empty-state">
                <h3>No KPI categories found</h3>
                <p>Run the KPI seeder to create default categories.</p>
            </div>
        @endif

        <div style="margin-top: 1rem;">
            <a href="{{ route('performance.index') }}" class="btn btn-secondary">← Back to Reviews</a>
        </div>
    </div>
@endsection
