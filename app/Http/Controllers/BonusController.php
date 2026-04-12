<?php

namespace App\Http\Controllers;

use App\Models\Bonus;
use App\Models\Employee;
use Illuminate\Http\Request;

class BonusController extends Controller
{
    public function index(Request $request)
    {
        $query = Bonus::with(['employee.user', 'approver'])
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc');

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('year')) {
            $query->where('year', $request->year);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $bonuses = $query->paginate(20)->withQueryString();
        $employees = Employee::with('user')->where('status', 'active')->get();

        return view('bonuses.index', compact('bonuses', 'employees'));
    }

    public function create()
    {
        $employees = Employee::with('user')->where('status', 'active')->get();
        return view('bonuses.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2000|max:2100',
            'amount' => 'required|numeric|min:0.01|max:999999.99',
            'type' => 'required|in:performance,annual,festival,other',
            'reason' => 'nullable|string|max:1000',
        ]);

        Bonus::create($validated);

        return redirect()->route('bonuses.index')
            ->with('success', 'Bonus created successfully.');
    }

    public function approve(Bonus $bonus)
    {
        $bonus->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
        ]);

        return back()->with('success', 'Bonus approved.');
    }

    public function destroy(Bonus $bonus)
    {
        $bonus->delete();
        return redirect()->route('bonuses.index')->with('success', 'Bonus deleted.');
    }
}
