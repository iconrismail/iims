<?php
namespace App\Http\Controllers;

use App\Models\TaxBracket;
use App\Models\DeductionRule;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class TaxController extends Controller
{
    public function index(): View
    {
        $brackets = TaxBracket::orderBy('min_salary')->get();
        $rules = DeductionRule::orderBy('name')->get();
        return view('tax.index', compact('brackets','rules'));
    }

    public function storeBracket(Request $request): RedirectResponse
    {
        $request->validate(['name'=>'required','min_salary'=>'required|numeric|min:0','max_salary'=>'nullable|numeric|gt:min_salary','rate'=>'required|numeric|min:0|max:100']);
        TaxBracket::create($request->only('name','min_salary','max_salary','rate'));
        return back()->with('success','Tax bracket added.');
    }

    public function destroyBracket(TaxBracket $bracket): RedirectResponse
    {
        $bracket->delete();
        return back()->with('success','Tax bracket removed.');
    }

    public function storeRule(Request $request): RedirectResponse
    {
        $request->validate(['name'=>'required','type'=>'required|in:percentage,fixed','value'=>'required|numeric|min:0']);
        DeductionRule::create($request->only('name','type','value') + ['is_active'=>true, 'applies_to'=>'all']);
        return back()->with('success','Deduction rule added.');
    }

    public function toggleRule(DeductionRule $rule): RedirectResponse
    {
        $rule->update(['is_active'=>!$rule->is_active]);
        return back()->with('success','Rule updated.');
    }

    public function destroyRule(DeductionRule $rule): RedirectResponse
    {
        $rule->delete();
        return back()->with('success','Deduction rule removed.');
    }
}
