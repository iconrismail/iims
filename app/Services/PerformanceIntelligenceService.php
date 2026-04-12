<?php

namespace App\Services;

use App\Models\PerformanceReview;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PerformanceIntelligenceService
{
    /**
     * Suggest a salary increment % based on overall score (0-10 scale)
     * and the company's own historical data for the same score band.
     *
     * Returns:
     *   suggested float  – the recommended %
     *   band      string – descriptive tier label
     *   avg_given float|null – company historical average for this band
     *   basis     int    – number of past reviews used for calibration
     */
    public function suggestIncrement(float $score, int $employeeId = 0): array
    {
        [$suggested, $band] = match(true) {
            $score >= 9.0 => [8.0,  'Exceptional (9.0–10.0)'],
            $score >= 7.5 => [6.0,  'Above Average (7.5–8.9)'],
            $score >= 6.0 => [3.5,  'Meets Expectations (6.0–7.4)'],
            $score >= 4.0 => [1.0,  'Below Average (4.0–5.9)'],
            default       => [0.0,  'Needs Improvement (<4.0)'],
        };

        // Calibrate against company historical averages for the same band
        [$bandMin, $bandMax] = match(true) {
            $score >= 9.0 => [9.0, 10.0],
            $score >= 7.5 => [7.5, 8.99],
            $score >= 6.0 => [6.0, 7.49],
            $score >= 4.0 => [4.0, 5.99],
            default       => [0.0, 3.99],
        };

        $historical = PerformanceReview::whereBetween('overall_score', [$bandMin, $bandMax])
            ->where('salary_increment_pct', '>', 0)
            ->when($employeeId > 0, fn($q) => $q->where('employee_id', '!=', $employeeId))
            ->selectRaw('AVG(salary_increment_pct) AS avg_given, COUNT(*) AS basis')
            ->first();

        $avgGiven = $historical?->basis > 0 ? round((float) $historical->avg_given, 2) : null;
        $basis    = (int) ($historical?->basis ?? 0);

        // If we have enough historical data, blend the suggestion with the company average
        if ($avgGiven !== null && $basis >= 3) {
            $suggested = round(($suggested + $avgGiven) / 2, 2);
        }

        return [
            'suggested' => $suggested,
            'band'      => $band,
            'avg_given' => $avgGiven,
            'basis'     => $basis,
        ];
    }

    /**
     * Detect reviewers whose scoring is significantly biased vs the company-wide average.
     * Returns array of ['name', 'avg_score', 'deviation', 'direction', 'review_count']
     */
    public function detectBiasedReviewers(): array
    {
        $companyAvg = (float) PerformanceReview::whereNotNull('overall_score')->avg('overall_score');
        if ($companyAvg <= 0) {
            return [];
        }

        $reviewers = PerformanceReview::whereNotNull('overall_score')
            ->whereNotNull('reviewer_id')
            ->selectRaw('reviewer_id, AVG(overall_score) AS avg_score, COUNT(*) AS review_count')
            ->groupBy('reviewer_id')
            ->havingRaw('COUNT(*) >= 3')
            ->get();

        $reviewerIds = $reviewers->pluck('reviewer_id');
        $users = User::whereIn('id', $reviewerIds)->pluck('name', 'id');

        $biased = [];
        foreach ($reviewers as $r) {
            $deviation = (float) $r->avg_score - $companyAvg;
            if (abs($deviation) >= 1.5) { // >1.5 points on a 10-point scale is notable
                $biased[] = [
                    'reviewer_id'  => $r->reviewer_id,
                    'name'         => $users[$r->reviewer_id] ?? 'Unknown',
                    'avg_score'    => round((float) $r->avg_score, 2),
                    'company_avg'  => round($companyAvg, 2),
                    'deviation'    => round($deviation, 2),
                    'direction'    => $deviation > 0 ? 'high' : 'low',
                    'review_count' => (int) $r->review_count,
                ];
            }
        }

        return $biased;
    }

    /**
     * Generate a one-line AI summary of the review comments using Claude API.
     * Returns null if API key is not configured or call fails.
     */
    public function summarizeComments(string $comments, float $overallScore): ?string
    {
        $apiKey = config('services.anthropic.api_key', '');
        if (empty($apiKey) || trim($comments) === '') {
            return null;
        }

        $prompt = "Summarize the following employee performance review comment in one sentence (max 25 words). "
            . "The employee scored " . number_format($overallScore, 1) . "/10 overall. "
            . "Be objective and professional.\n\nComment:\n{$comments}";

        try {
            $response = Http::withHeaders([
                'x-api-key'         => $apiKey,
                'anthropic-version' => '2023-06-01',
                'content-type'      => 'application/json',
            ])->timeout(20)->post('https://api.anthropic.com/v1/messages', [
                'model'      => config('services.anthropic.model', 'claude-haiku-4-5-20251001'),
                'max_tokens' => 80,
                'messages'   => [['role' => 'user', 'content' => $prompt]],
            ]);

            if ($response->successful()) {
                return $response->json('content.0.text');
            }

            Log::warning('PerformanceIntelligence API error: ' . $response->status());
        } catch (\Exception $e) {
            Log::warning('PerformanceIntelligence API exception: ' . $e->getMessage());
        }

        return null;
    }
}
