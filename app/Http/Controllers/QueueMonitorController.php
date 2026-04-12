<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class QueueMonitorController extends Controller
{
    public function index(): View
    {
        $pending = DB::table('jobs')
            ->select('id', 'queue', 'payload', 'attempts', 'created_at', 'available_at')
            ->orderByDesc('created_at')
            ->limit(50)
            ->get()
            ->map(function ($job) {
                $payload = json_decode($job->payload, true);
                $job->job_name = class_basename($payload['displayName'] ?? 'Unknown');
                $job->data     = $payload['data'] ?? [];
                return $job;
            });

        $failed = DB::table('failed_jobs')
            ->orderByDesc('failed_at')
            ->limit(50)
            ->get()
            ->map(function ($job) {
                $payload = json_decode($job->payload, true);
                $job->job_name  = class_basename($payload['displayName'] ?? 'Unknown');
                $job->exception = substr($job->exception ?? '', 0, 300);
                return $job;
            });

        $stats = [
            'pending'    => DB::table('jobs')->count(),
            'failed'     => DB::table('failed_jobs')->count(),
            'processing' => DB::table('jobs')->where('reserved_at', '!=', null)->count(),
        ];

        return view('queue.monitor', compact('pending', 'failed', 'stats'));
    }

    public function retryFailed(Request $request): \Illuminate\Http\RedirectResponse
    {
        $uuid = $request->input('uuid');
        $failed = DB::table('failed_jobs')->where('uuid', $uuid)->first();

        if ($failed) {
            DB::table('jobs')->insert([
                'queue'        => $failed->queue,
                'payload'      => $failed->payload,
                'attempts'     => 0,
                'reserved_at'  => null,
                'available_at' => now()->getTimestamp(),
                'created_at'   => now()->getTimestamp(),
            ]);
            DB::table('failed_jobs')->where('uuid', $uuid)->delete();
        }

        return back()->with('success', 'Job queued for retry.');
    }

    public function clearFailed(): \Illuminate\Http\RedirectResponse
    {
        DB::table('failed_jobs')->truncate();
        return back()->with('success', 'All failed jobs cleared.');
    }
}
