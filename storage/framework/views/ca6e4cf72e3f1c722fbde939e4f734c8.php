<?php $__env->startSection('title', 'Payroll'); ?>
<?php $__env->startSection('page-title', 'Payroll Management'); ?>

<?php $__env->startSection('content'); ?>
    
    <?php if($pendingJobs > 0): ?>
        <div class="alert" style="background:rgba(108,99,255,0.12);border:1px solid rgba(108,99,255,0.3);color:var(--text-primary);display:flex;align-items:center;gap:0.75rem;margin-bottom:1rem">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#6c63ff" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            <span><strong><?php echo e($pendingJobs); ?></strong> payroll job(s) queued. Run <code style="background:rgba(0,0,0,0.3);padding:2px 6px;border-radius:3px">php artisan queue:work</code> to process them.</span>
            <a href="<?php echo e(route('queue.monitor')); ?>" class="btn btn-sm btn-secondary" style="margin-left:auto">View Queue</a>
        </div>
    <?php endif; ?>

    
    <div class="card mb-3">
        <form method="GET" action="<?php echo e(route('payroll.index')); ?>" style="display:flex;gap:0.75rem;flex-wrap:wrap;align-items:flex-end">
            <div class="form-group" style="margin:0;min-width:120px">
                <label class="form-label">Year</label>
                <select name="year" class="form-control">
                    <option value="">All Years</option>
                    <?php for($y = date('Y')-3; $y <= date('Y')+1; $y++): ?>
                        <option value="<?php echo e($y); ?>" <?php echo e(request('year') == $y ? 'selected' : ''); ?>><?php echo e($y); ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="form-group" style="margin:0;min-width:140px">
                <label class="form-label">Status</label>
                <select name="status" class="form-control">
                    <option value="">All Statuses</option>
                    <option value="draft"     <?php echo e(request('status') === 'draft'     ? 'selected' : ''); ?>>Draft</option>
                    <option value="processed" <?php echo e(request('status') === 'processed' ? 'selected' : ''); ?>>Processed</option>
                    <option value="paid"      <?php echo e(request('status') === 'paid'      ? 'selected' : ''); ?>>Paid</option>
                </select>
            </div>
            <div style="display:flex;gap:0.5rem;padding-bottom:1px">
                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                <?php if(request()->hasAny(['year','status'])): ?>
                    <a href="<?php echo e(route('payroll.index')); ?>" class="btn btn-secondary btn-sm">Clear</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Payroll Periods</h3>
            <a href="<?php echo e(route('payroll.create')); ?>" class="btn btn-primary btn-sm">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Process Payroll
            </a>
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Period</th>
                        <th>Status</th>
                        <th>Payslips</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $payrolls; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payroll): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td class="font-bold"><?php echo e($payroll->periodLabel()); ?></td>
                            <td>
                                <?php if($payroll->status === 'paid'): ?>
                                    <span class="badge badge-success">Paid</span>
                                <?php elseif($payroll->status === 'processed'): ?>
                                    <span class="badge badge-info">Processed</span>
                                <?php else: ?>
                                    <span class="badge badge-warning">Draft</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge badge-info"><?php echo e($payroll->payslips_count); ?></span>
                            </td>
                            <td class="text-secondary"><?php echo e($payroll->created_at->format('M d, Y')); ?></td>
                            <td>
                                <div class="btn-group">
                                    <a href="<?php echo e(route('payroll.show', $payroll)); ?>" class="btn btn-sm btn-secondary">View</a>
                                    <?php if($payroll->status === 'processed'): ?>
                                        <form action="<?php echo e(route('payroll.markPaid', $payroll)); ?>" method="POST"
                                              onsubmit="return confirm('Mark this payroll as paid?')">
                                            <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                                            <button type="submit" class="btn btn-sm btn-success">Mark Paid</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="5" class="text-center text-muted" style="padding: 2rem">No payroll periods found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if($payrolls->hasPages()): ?>
            <div class="pagination-wrapper">
                <?php echo e($payrolls->links()); ?>

            </div>
        <?php endif; ?>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\ishma\Music\iims\resources\views/payroll/index.blade.php ENDPATH**/ ?>