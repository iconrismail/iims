<?php $__env->startSection('title', 'Manager Dashboard'); ?>
<?php $__env->startSection('page-title', 'Manager Dashboard'); ?>

<?php $__env->startSection('content'); ?>
<?php
    $user = auth()->user();
    $hour = $now->hour;
    $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
?>


<div class="card" style="background:linear-gradient(135deg,var(--accent) 0%,#0099bb 100%);color:#fff;margin-bottom:1.5rem;">
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem;">
        <div>
            <div style="font-size:1.35rem;font-weight:700;"><?php echo e($greeting); ?>, <?php echo e($user->name); ?></div>
            <div style="opacity:.85;font-size:.9rem;margin-top:.25rem;">
                Department Manager &nbsp;·&nbsp;
                <?php echo e($department?->name ?? 'No Department'); ?>

                &nbsp;·&nbsp;<?php echo e($now->format('l, d M Y')); ?>

            </div>
        </div>
        <div style="display:flex;gap:.75rem;flex-wrap:wrap;">
            <?php if($pendingLeaves > 0): ?>
                <a href="<?php echo e(route('leaves.index', ['status'=>'pending'])); ?>" class="btn btn-sm" style="background:rgba(255,255,255,.2);color:#fff;border:1px solid rgba(255,255,255,.35);">
                    <?php echo e($pendingLeaves); ?> Leave<?php echo e($pendingLeaves != 1 ? 's' : ''); ?> Pending
                </a>
            <?php endif; ?>
            <?php if($pendingOvertimes > 0): ?>
                <a href="<?php echo e(route('overtime.index', ['status'=>'pending'])); ?>" class="btn btn-sm" style="background:rgba(255,255,255,.2);color:#fff;border:1px solid rgba(255,255,255,.35);">
                    <?php echo e($pendingOvertimes); ?> OT Pending
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>


<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:1rem;margin-bottom:1.5rem;">
    <div class="card" style="text-align:center;padding:1.25rem 1rem;">
        <div style="font-size:2rem;font-weight:700;color:var(--accent);"><?php echo e($teamSize); ?></div>
        <div style="font-size:.8rem;color:var(--text-muted);margin-top:.25rem;">Team Members</div>
    </div>
    <div class="card" style="text-align:center;padding:1.25rem 1rem;">
        <div style="font-size:2rem;font-weight:700;color:#28a745;"><?php echo e($teamPresent); ?></div>
        <div style="font-size:.8rem;color:var(--text-muted);margin-top:.25rem;">Present Today</div>
    </div>
    <div class="card" style="text-align:center;padding:1.25rem 1rem;">
        <div style="font-size:2rem;font-weight:700;color:#dc3545;"><?php echo e($teamAbsent); ?></div>
        <div style="font-size:.8rem;color:var(--text-muted);margin-top:.25rem;">Absent Today</div>
    </div>
    <div class="card" style="text-align:center;padding:1.25rem 1rem;">
        <div style="font-size:2rem;font-weight:700;color:#f0ad4e;"><?php echo e($pendingLeaves); ?></div>
        <div style="font-size:.8rem;color:var(--text-muted);margin-top:.25rem;">Pending Leaves</div>
    </div>
    <div class="card" style="text-align:center;padding:1.25rem 1rem;">
        <div style="font-size:2rem;font-weight:700;color:#9b59b6;"><?php echo e($pendingOvertimes); ?></div>
        <div style="font-size:.8rem;color:var(--text-muted);margin-top:.25rem;">Pending Overtime</div>
    </div>
    <div class="card" style="text-align:center;padding:1.25rem 1rem;">
        <?php
            $total = $monthPresent + $monthAbsent;
            $rate = $total > 0 ? round(($monthPresent / $total) * 100) : 0;
            $rateColor = $rate >= 90 ? '#28a745' : ($rate >= 75 ? '#f0ad4e' : '#dc3545');
        ?>
        <div style="font-size:2rem;font-weight:700;color:<?php echo e($rateColor); ?>;"><?php echo e($rate); ?>%</div>
        <div style="font-size:.8rem;color:var(--text-muted);margin-top:.25rem;">My Attendance</div>
    </div>
    
    <div class="card" style="text-align:center;padding:1.25rem 1rem;">
        <div style="font-size:1.35rem;font-weight:700;color:var(--accent);">NLE <?php echo e(number_format($deptPayrollCost, 0)); ?></div>
        <div style="font-size:.8rem;color:var(--text-muted);margin-top:.25rem;">Dept Payroll Cost</div>
    </div>
    
    <div class="card" style="text-align:center;padding:1.25rem 1rem;">
        <?php
            $avgScore   = round((float)($teamPerformance?->avg_score ?? 0), 1);
            $reviewCount = (int)($teamPerformance?->review_count ?? 0);
            $scoreColor = $avgScore >= 7.5 ? '#22c55e' : ($avgScore >= 5 ? '#f59e0b' : '#ef4444');
        ?>
        <div style="font-size:2rem;font-weight:700;color:<?php echo e($avgScore > 0 ? $scoreColor : 'var(--text-muted)'); ?>;">
            <?php echo e($avgScore > 0 ? $avgScore : '—'); ?>

        </div>
        <div style="font-size:.8rem;color:var(--text-muted);margin-top:.25rem;">
            Team Avg Score
            <?php if($reviewCount > 0): ?>
                <span style="display:block;font-size:.7rem;">(<?php echo e($reviewCount); ?> reviews)</span>
            <?php endif; ?>
        </div>
    </div>
</div>


<?php if($onLeaveToday->isNotEmpty()): ?>
<div style="background:rgba(245,158,11,.08);border:1px solid rgba(245,158,11,.35);border-radius:8px;padding:.65rem 1rem;margin-bottom:1.25rem;display:flex;align-items:center;gap:.75rem;flex-wrap:wrap;">
    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2" style="flex-shrink:0"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/><path d="M9 14l2 2 4-4"/></svg>
    <span style="font-size:.82rem;color:#f59e0b;font-weight:600;">On approved leave today:</span>
    <?php $__currentLoopData = $onLeaveToday; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lr): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <span style="font-size:.8rem;background:rgba(245,158,11,.15);border-radius:99px;padding:.15rem .6rem;color:var(--text-secondary);"><?php echo e($lr->employee->user->name); ?></span>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php endif; ?>


<div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;margin-bottom:1.5rem;">

    
    <div class="card">
        <div class="card-header" style="display:flex;align-items:center;justify-content:space-between;">
            <h3 class="card-title">Pending Leave Requests</h3>
            <a href="<?php echo e(route('leaves.index', ['status'=>'pending'])); ?>" style="font-size:.8rem;color:var(--accent);">View all</a>
        </div>
        <?php if($pendingLeaveRequests->isEmpty()): ?>
            <div class="empty-state"><p>No pending leave requests.</p></div>
        <?php else: ?>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Type</th>
                            <th>Dates</th>
                            <th>Days</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $pendingLeaveRequests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $leave): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td style="font-weight:600;font-size:.85rem;"><?php echo e($leave->employee->user->name); ?></td>
                            <td style="font-size:.8rem;"><?php echo e($leave->leaveType->name); ?></td>
                            <td style="font-size:.75rem;color:var(--text-muted);">
                                <?php echo e($leave->start_date->format('d M')); ?> – <?php echo e($leave->end_date->format('d M')); ?>

                            </td>
                            <td style="font-size:.85rem;text-align:center;"><?php echo e($leave->total_days); ?></td>
                            <td>
                                <div style="display:flex;gap:.4rem;">
                                    <form method="POST" action="<?php echo e(route('leaves.approve', $leave)); ?>">
                                        <?php echo csrf_field(); ?>
                                        <button type="submit" class="btn btn-sm" style="background:#28a74520;color:#28a745;border:none;padding:.2rem .6rem;font-size:.75rem;cursor:pointer;">Approve</button>
                                    </form>
                                    <form method="POST" action="<?php echo e(route('leaves.reject', $leave)); ?>">
                                        <?php echo csrf_field(); ?>
                                        <button type="submit" class="btn btn-sm" style="background:#dc354520;color:#dc3545;border:none;padding:.2rem .6rem;font-size:.75rem;cursor:pointer;">Reject</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    
    <div class="card">
        <div class="card-header" style="display:flex;align-items:center;justify-content:space-between;">
            <h3 class="card-title">Pending Overtime</h3>
            <a href="<?php echo e(route('overtime.index', ['status'=>'pending'])); ?>" style="font-size:.8rem;color:var(--accent);">View all</a>
        </div>
        <?php if($pendingOvertimeRequests->isEmpty()): ?>
            <div class="empty-state"><p>No pending overtime requests.</p></div>
        <?php else: ?>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Date</th>
                            <th>Hours</th>
                            <th>Amount</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $pendingOvertimeRequests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td style="font-weight:600;font-size:.85rem;"><?php echo e($ot->employee->user->name); ?></td>
                            <td style="font-size:.8rem;"><?php echo e(\Carbon\Carbon::parse($ot->date)->format('d M Y')); ?></td>
                            <td style="font-size:.85rem;text-align:center;"><?php echo e($ot->hours); ?>h</td>
                            <td style="font-size:.85rem;">NLE <?php echo e(number_format($ot->amount, 2)); ?></td>
                            <td>
                                <div style="display:flex;gap:.4rem;">
                                    <form method="POST" action="<?php echo e(route('overtime.approve', $ot)); ?>">
                                        <?php echo csrf_field(); ?>
                                        <button type="submit" class="btn btn-sm" style="background:#28a74520;color:#28a745;border:none;padding:.2rem .6rem;font-size:.75rem;cursor:pointer;">Approve</button>
                                    </form>
                                    <form method="POST" action="<?php echo e(route('overtime.reject', $ot)); ?>">
                                        <?php echo csrf_field(); ?>
                                        <button type="submit" class="btn btn-sm" style="background:#dc354520;color:#dc3545;border:none;padding:.2rem .6rem;font-size:.75rem;cursor:pointer;">Reject</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>


<div style="display:grid;grid-template-columns:1.4fr 1fr;gap:1.5rem;margin-bottom:1.5rem;">

    
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Team Attendance Trend (6 Months)</h3>
        </div>
        <canvas id="teamAttendanceChart" height="110"></canvas>
    </div>

    
    <div class="card">
        <div class="card-header" style="display:flex;align-items:center;justify-content:space-between;">
            <h3 class="card-title">Team Members</h3>
            <span class="badge badge-info"><?php echo e($teamSize); ?> active</span>
        </div>
        <?php if($teamMembers->isEmpty()): ?>
            <div class="empty-state"><p>No team members found.</p></div>
        <?php else: ?>
            <div style="display:flex;flex-direction:column;gap:.6rem;max-height:260px;overflow-y:auto;">
                <?php $__currentLoopData = $teamMembers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $member): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div style="display:flex;align-items:center;gap:.75rem;padding:.5rem .25rem;border-bottom:1px solid var(--border-color);">
                    <div style="width:34px;height:34px;border-radius:50%;background:var(--accent);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.85rem;flex-shrink:0;">
                        <?php echo e(strtoupper(substr($member->user->name, 0, 1))); ?>

                    </div>
                    <div>
                        <div style="font-weight:600;font-size:.85rem;"><?php echo e($member->user->name); ?></div>
                        <div style="font-size:.75rem;color:var(--text-muted);"><?php echo e($member->position); ?></div>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endif; ?>
    </div>
</div>


<div class="card">
    <div class="card-header">
        <h3 class="card-title">My Quick Actions</h3>
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:.75rem;">
        <a href="<?php echo e(route('leaves.create')); ?>" class="card" style="text-align:center;padding:1rem;text-decoration:none;color:inherit;display:flex;flex-direction:column;align-items:center;gap:.5rem;border:1px solid var(--border-color);">
            <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="var(--accent)" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/><path d="M9 14l2 2 4-4"/></svg>
            <span style="font-size:.8rem;font-weight:600;">Apply Leave</span>
        </a>
        <a href="<?php echo e(route('payslips.index')); ?>" class="card" style="text-align:center;padding:1rem;text-decoration:none;color:inherit;display:flex;flex-direction:column;align-items:center;gap:.5rem;border:1px solid var(--border-color);">
            <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="var(--accent)" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            <span style="font-size:.8rem;font-weight:600;">My Payslips</span>
        </a>
        <a href="<?php echo e(route('attendance.index')); ?>" class="card" style="text-align:center;padding:1rem;text-decoration:none;color:inherit;display:flex;flex-direction:column;align-items:center;gap:.5rem;border:1px solid var(--border-color);">
            <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="var(--accent)" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            <span style="font-size:.8rem;font-weight:600;">Attendance</span>
        </a>
        <a href="<?php echo e(route('leaves.index')); ?>" class="card" style="text-align:center;padding:1rem;text-decoration:none;color:inherit;display:flex;flex-direction:column;align-items:center;gap:.5rem;border:1px solid var(--border-color);">
            <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="var(--accent)" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
            <span style="font-size:.8rem;font-weight:600;">Team Leaves</span>
        </a>
        <a href="<?php echo e(route('overtime.index')); ?>" class="card" style="text-align:center;padding:1rem;text-decoration:none;color:inherit;display:flex;flex-direction:column;align-items:center;gap:.5rem;border:1px solid var(--border-color);">
            <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="var(--accent)" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            <span style="font-size:.8rem;font-weight:600;">Team Overtime</span>
        </a>
        <a href="<?php echo e(route('performance.index')); ?>" class="card" style="text-align:center;padding:1rem;text-decoration:none;color:inherit;display:flex;flex-direction:column;align-items:center;gap:.5rem;border:1px solid var(--border-color);">
            <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="var(--accent)" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
            <span style="font-size:.8rem;font-weight:600;">Reviews</span>
        </a>
        <a href="<?php echo e(route('performance.create')); ?>" class="card" style="text-align:center;padding:1rem;text-decoration:none;color:inherit;display:flex;flex-direction:column;align-items:center;gap:.5rem;border:1px solid var(--border-color);">
            <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="var(--accent)" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
            <span style="font-size:.8rem;font-weight:600;">New Review</span>
        </a>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
(function () {
    const trend = <?php echo json_encode($teamAttendanceTrend, 15, 512) ?>;
    const ctx = document.getElementById('teamAttendanceChart')?.getContext('2d');
    if (!ctx || !trend.length) return;
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: trend.map(d => d.label),
            datasets: [
                {
                    label: 'Present',
                    data: trend.map(d => d.present),
                    backgroundColor: 'rgba(40,167,69,.7)',
                    borderRadius: 4,
                },
                {
                    label: 'Absent',
                    data: trend.map(d => d.absent),
                    backgroundColor: 'rgba(220,53,69,.5)',
                    borderRadius: 4,
                }
            ]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'top', labels: { font: { size: 11 } } } },
            scales: {
                x: { grid: { display: false } },
                y: { beginAtZero: true, ticks: { precision: 0 } }
            }
        }
    });
})();
</script>
<?php $__env->stopPush(); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\ishma\Music\iims\resources\views/dashboard/manager.blade.php ENDPATH**/ ?>