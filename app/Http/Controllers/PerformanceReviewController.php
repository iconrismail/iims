<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\KpiCategory;
use App\Models\PerformanceReview;
use App\Services\PerformanceIntelligenceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PerformanceReviewController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $query = PerformanceReview::with(['employee.user', 'reviewer'])
            ->orderBy('period_year', 'desc')
            ->orderBy('created_at', 'desc');

        if ($user->isAdmin()) {
            // Admin sees all, with optional filters
            if ($request->filled('employee_id')) {
                $query->where('employee_id', $request->employee_id);
            }
            if ($request->filled('year')) {
                $query->where('period_year', $request->year);
            }
            if ($request->filled('period')) {
                $query->where('review_period', $request->period);
            }
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
        } elseif ($user->isManager()) {
            // Manager sees reviews for their department employees
            $deptId = $user->employee?->department_id;
            $query->whereHas('employee', fn($q) => $q->where('department_id', $deptId));
        } else {
            // Employees see only their own reviews
            $query->whereHas('employee', fn($q) => $q->where('user_id', $user->id));
        }

        $reviews = $query->paginate(20)->withQueryString();

        $employees = match(true) {
            $user->isAdmin()   => Employee::with('user')->where('status', 'active')->get(),
            $user->isManager() => Employee::with('user')->where('status', 'active')
                                    ->where('department_id', $user->employee?->department_id)
                                    ->get(),
            default            => collect(),
        };

        return view('performance.index', compact('reviews', 'employees'));
    }

    public function create()
    {
        $user = auth()->user();

        $employees = $user->isManager()
            ? Employee::with('user')->where('status', 'active')
                ->where('department_id', $user->employee?->department_id)
                ->get()
            : Employee::with('user')->where('status', 'active')->get();

        $categories = KpiCategory::where('is_active', true)->get();
        return view('performance.create', compact('employees', 'categories'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $categories = KpiCategory::where('is_active', true)->get();

        $scoreRules = [];
        foreach ($categories as $cat) {
            $scoreRules["scores.{$cat->id}"] = 'required|integer|min:1|max:10';
        }

        $validated = $request->validate(array_merge([
            'employee_id'          => 'required|exists:employees,id',
            'review_period'        => 'required|in:Q1,Q2,Q3,Q4,annual',
            'period_year'          => 'required|integer|min:2000|max:2100',
            'comments'             => 'nullable|string|max:2000',
            'salary_increment_pct' => 'required|numeric|min:0|max:50',
        ], $scoreRules));

        // Managers may only review employees in their own department
        if ($user->isManager()) {
            $deptId   = $user->employee?->department_id;
            $employee = Employee::find($validated['employee_id']);
            abort_if(!$employee || $employee->department_id !== $deptId, 403,
                'You can only create reviews for employees in your department.');
        }

        $review = new PerformanceReview($validated);
        $review->reviewer_id  = auth()->id();
        $review->scores       = $validated['scores'];
        $review->overall_score = $review->calculateOverallScore();
        $review->save();

        return redirect()->route('performance.show', $review)
            ->with('success', 'Performance review created successfully.');
    }

    public function show(PerformanceReview $review, PerformanceIntelligenceService $perfIntel)
    {
        $user = auth()->user();

        if ($user->isManager()) {
            // Managers can only view reviews for their department
            $deptId = $user->employee?->department_id;
            abort_if($review->employee->department_id !== $deptId, 403);
        } elseif (!$user->isAdmin()) {
            // Employees can only see their own reviews
            $employee = Employee::where('user_id', $user->id)->first();
            abort_if(!$employee || $review->employee_id !== $employee->id, 403);
        }

        $review->load(['employee.user', 'reviewer']);
        $categories = KpiCategory::where('is_active', true)->get()->keyBy('id');

        $incrementSuggestion = $perfIntel->suggestIncrement(
            (float) $review->overall_score,
            $review->employee_id
        );

        // Reviewer bias (admin only)
        $reviewerBias = null;
        if ($user->isAdmin() && $review->reviewer_id) {
            $biased = $perfIntel->detectBiasedReviewers();
            foreach ($biased as $b) {
                if ($b['reviewer_id'] === $review->reviewer_id) {
                    $reviewerBias = $b;
                    break;
                }
            }
        }

        return view('performance.show', compact('review', 'categories', 'incrementSuggestion', 'reviewerBias'));
    }

    public function aiSummary(PerformanceReview $review, PerformanceIntelligenceService $perfIntel): JsonResponse
    {
        abort_if(!auth()->user()->isAdmin(), 403);

        if (!$review->comments) {
            return response()->json(['summary' => null, 'error' => 'No comments to summarise.']);
        }

        $summary = $perfIntel->summarizeComments($review->comments, (float) $review->overall_score);

        if ($summary === null) {
            return response()->json(['summary' => null, 'error' => 'AI summary unavailable — check ANTHROPIC_API_KEY in .env']);
        }

        return response()->json(['summary' => $summary]);
    }

    public function submit(PerformanceReview $review)
    {
        $user = auth()->user();

        // Managers can only submit reviews for their department
        if ($user->isManager()) {
            $deptId = $user->employee?->department_id;
            abort_if($review->employee->department_id !== $deptId, 403);
        }

        if ($review->status !== 'draft') {
            return back()->with('error', 'Only draft reviews can be submitted.');
        }

        $review->update(['status' => 'submitted']);

        return back()->with('success', 'Review submitted successfully.');
    }

    public function acknowledge(PerformanceReview $review)
    {
        $user     = auth()->user();
        $employee = Employee::where('user_id', $user->id)->first();

        if (!$employee || $review->employee_id !== $employee->id) {
            abort(403);
        }

        if ($review->status !== 'submitted') {
            return back()->with('error', 'Only submitted reviews can be acknowledged.');
        }

        $review->update([
            'status'         => 'acknowledged',
            'acknowledged_at'=> now(),
        ]);

        return back()->with('success', 'Review acknowledged.');
    }

    public function kpiIndex()
    {
        $categories = KpiCategory::all();
        return view('performance.kpi', compact('categories'));
    }
}
