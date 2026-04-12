@extends('layouts.app')

@section('title', 'Employee Documents')
@section('page-title', 'Employee Documents')

@section('content')
    {{-- Employee Header --}}
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-header">
            <div>
                <h3 class="card-title">{{ $employee->user->name }}</h3>
                <div style="font-size: 0.85rem; color: var(--text-secondary); margin-top: 0.2rem;">
                    {{ $employee->employee_id }} &bull; {{ $employee->position }} &bull; {{ $employee->department->name }}
                </div>
            </div>
            <a href="{{ route('employees.show', $employee) }}" class="btn btn-secondary btn-sm">← Back to Employee</a>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 380px; gap: 1.5rem;">
        {{-- Documents List --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Documents ({{ $documents->count() }})</h3>
            </div>

            @if($documents->count() > 0)
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Type</th>
                                <th>Size</th>
                                <th>Expiry</th>
                                <th>Uploaded By</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($documents as $doc)
                                <tr style="{{ $doc->isExpired() ? 'background: rgba(255, 82, 82, 0.06);' : ($doc->isExpiringSoon() ? 'background: rgba(255, 171, 64, 0.06);' : '') }}">
                                    <td class="font-bold">{{ $doc->title }}</td>
                                    <td>
                                        @php
                                            $typeColors = ['contract' => 'badge-info', 'id_document' => 'badge-warning', 'certificate' => 'badge-success', 'passport' => 'badge-danger', 'other' => 'badge-secondary'];
                                        @endphp
                                        <span class="badge {{ $typeColors[$doc->type] ?? 'badge-info' }}">
                                            {{ str_replace('_', ' ', ucfirst($doc->type)) }}
                                        </span>
                                    </td>
                                    <td style="color: var(--text-secondary); font-size: 0.85rem;">{{ $doc->formattedSize() }}</td>
                                    <td>
                                        @if($doc->expires_at)
                                            @if($doc->isExpired())
                                                <span class="badge badge-danger">Expired {{ $doc->expires_at->format('M d, Y') }}</span>
                                            @elseif($doc->isExpiringSoon())
                                                <span class="badge badge-warning">Expiring {{ $doc->expires_at->format('M d, Y') }}</span>
                                            @else
                                                <span style="font-size: 0.85rem; color: var(--text-secondary);">{{ $doc->expires_at->format('M d, Y') }}</span>
                                            @endif
                                        @else
                                            <span style="color: var(--text-muted); font-size: 0.82rem;">No expiry</span>
                                        @endif
                                    </td>
                                    <td style="font-size: 0.85rem; color: var(--text-secondary);">{{ $doc->uploader->name }}</td>
                                    <td style="font-size: 0.85rem; color: var(--text-muted);">{{ $doc->created_at->format('M d, Y') }}</td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('documents.download', $doc) }}" class="btn btn-sm btn-primary">Download</a>
                                            <form action="{{ route('documents.destroy', $doc) }}" method="POST" style="display:inline" onsubmit="return confirm('Delete this document?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="empty-state">
                    <div style="font-size: 2.5rem; margin-bottom: 0.75rem; opacity: 0.4;">📄</div>
                    <h3>No documents uploaded</h3>
                    <p>Upload contracts, IDs, certificates and other employee documents.</p>
                </div>
            @endif
        </div>

        {{-- Upload Form --}}
        <div class="card" style="height: fit-content;">
            <div class="card-header">
                <h3 class="card-title">Upload Document</h3>
            </div>

            <form action="{{ route('employees.documents.store', $employee) }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="form-group">
                    <label class="form-label">Document Title</label>
                    <input type="text" name="title" class="form-control" required
                        value="{{ old('title') }}" placeholder="e.g. Employment Contract">
                </div>

                <div class="form-group">
                    <label class="form-label">Document Type</label>
                    <select name="type" class="form-control" required>
                        <option value="">-- Select Type --</option>
                        <option value="contract" {{ old('type') === 'contract' ? 'selected' : '' }}>Contract</option>
                        <option value="id_document" {{ old('type') === 'id_document' ? 'selected' : '' }}>ID Document</option>
                        <option value="certificate" {{ old('type') === 'certificate' ? 'selected' : '' }}>Certificate</option>
                        <option value="passport" {{ old('type') === 'passport' ? 'selected' : '' }}>Passport</option>
                        <option value="other" {{ old('type') === 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">File</label>
                    <input type="file" name="file" class="form-control" required
                        accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                    <span class="form-hint">PDF, Word, JPG or PNG. Max 10MB.</span>
                </div>

                <div class="form-group">
                    <label class="form-label">Expiry Date <span style="color: var(--text-muted)">(optional)</span></label>
                    <input type="date" name="expires_at" class="form-control" value="{{ old('expires_at') }}">
                    <span class="form-hint">Leave blank if document doesn't expire</span>
                </div>

                <div class="form-group">
                    <label class="form-label">Notes <span style="color: var(--text-muted)">(optional)</span></label>
                    <textarea name="notes" class="form-control" rows="2" placeholder="Additional notes...">{{ old('notes') }}</textarea>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%;">Upload Document</button>
            </form>
        </div>
    </div>
@endsection
