<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentController extends Controller
{
    public function index(Employee $employee)
    {
        abort_if(!auth()->user()->isAdmin(), 403);

        $documents = $employee->documents()->with('uploader')->orderBy('created_at', 'desc')->get();
        return view('documents.index', compact('employee', 'documents'));
    }

    public function store(Request $request, Employee $employee)
    {
        abort_if(!auth()->user()->isAdmin(), 403);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:contract,id_document,certificate,passport,other',
            'file' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
            'expires_at' => 'nullable|date',
            'notes' => 'nullable|string|max:1000',
        ]);

        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = "documents/{$employee->id}/{$filename}";

        Storage::disk('local')->put($path, file_get_contents($file->getRealPath()));

        EmployeeDocument::create([
            'employee_id' => $employee->id,
            'title' => $validated['title'],
            'type' => $validated['type'],
            'file_path' => $path,
            'original_name' => $originalName,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'expires_at' => $validated['expires_at'] ?? null,
            'uploaded_by' => auth()->id(),
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('employees.documents', $employee)
            ->with('success', 'Document uploaded successfully.');
    }

    public function download(EmployeeDocument $document)
    {
        abort_if(!auth()->user()->isAdmin(), 403);

        $fullPath = Storage::disk('local')->path($document->file_path);

        if (!Storage::disk('local')->exists($document->file_path)) {
            abort(404, 'File not found.');
        }

        return response()->download($fullPath, $document->original_name);
    }

    public function destroy(EmployeeDocument $document)
    {
        abort_if(!auth()->user()->isAdmin(), 403);

        $employeeId = $document->employee_id;

        if (Storage::disk('local')->exists($document->file_path)) {
            Storage::disk('local')->delete($document->file_path);
        }

        $document->delete();

        return redirect()->route('employees.documents', $employeeId)
            ->with('success', 'Document deleted successfully.');
    }
}
