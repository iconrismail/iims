<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function index(): View
    {
        Setting::seedDefaults();
        $settings = Setting::allKeyed();
        return view('settings.index', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'company_name'            => 'required|string|max:100',
            'company_address'         => 'nullable|string|max:255',
            'company_phone'           => 'nullable|string|max:30',
            'company_email'           => 'nullable|email|max:100',
            'currency_symbol'         => 'required|string|max:10',
            'fiscal_year_start_month' => 'required|integer|between:1,12',
            'include_saturdays'       => 'nullable|boolean',
            'payslip_footer'          => 'nullable|string|max:500',
        ]);

        $groups = [
            'company_name'            => 'company',
            'company_address'         => 'company',
            'company_phone'           => 'company',
            'company_email'           => 'company',
            'currency_symbol'         => 'payroll',
            'fiscal_year_start_month' => 'payroll',
            'include_saturdays'       => 'payroll',
            'payslip_footer'          => 'system',
        ];

        foreach ($groups as $key => $group) {
            $value = $key === 'include_saturdays'
                ? ($request->boolean($key) ? '1' : '0')
                : ($request->input($key) ?? '');
            Setting::set($key, $value, $group);
        }

        return back()->with('success', 'Settings saved successfully.');
    }
}
