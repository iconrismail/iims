<?php $__env->startSection('title', 'Performance Reviews'); ?>
<?php $__env->startSection('page-title', 'Performance Reviews'); ?>

<?php $__env->startSection('content'); ?>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Performance Reviews</h3>
            <?php if(auth()->user()->isAdminOrManager()): ?>
                <div class="btn-group">
                    <a href="<?php echo e(route('performance.create')); ?>" class="btn btn-primary btn-sm">+ New Review</a>
                    <?php if(auth()->user()->isAdmin()): ?>
                        <a href="<?php echo e(route('performance.kpi')); ?>" class="btn btn-secondary btn-sm">KPI Categories</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <?php if(auth()->user()->isAdminOrManager()): ?>
            
            <form method="GET" action="<?php echo e(route('performance.index')); ?>" style="display: flex; gap: 0.75rem; flex-wrap: wrap; margin-bottom: 1.5rem;">
                <?php if($employees->isNotEmpty()): ?>
                <select name="employee_id" class="form-control" style="width: auto; min-width: 180px;">
                    <option value="">All Employees</option>
                    <?php $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $emp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($emp->id); ?>" <?php echo e(request('employee_id') == $emp->id ? 'selected' : ''); ?>>
                            <?php echo e($emp->user->name); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <?php endif; ?>
                <select name="period" class="form-control" style="width: auto;">
                    <option value="">All Periods</option>
                    <?php $__currentLoopData = ['Q1', 'Q2', 'Q3', 'Q4', 'annual']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($p); ?>" <?php echo e(request('period') === $p ? 'selected' : ''); ?>><?php echo e($p); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <select name="year" class="form-control" style="width: auto;">
                    <option value="">All Years</option>
                    <?php for($y = date('Y'); $y >= date('Y') - 3; $y--): ?>
                        <option value="<?php echo e($y); ?>" <?php echo e(request('year') == $y ? 'selected' : ''); ?>><?php echo e($y); ?></option>
                    <?php endfor; ?>
                </select>
                <select name="status" class="form-control" style="width: auto;">
                    <option value="">All Status</option>
                    <option value="draft" <?php echo e(request('status') === 'draft' ? 'selected' : ''); ?>>Draft</option>
                    <option value="submitted" <?php echo e(request('status') === 'submitted' ? 'selected' : ''); ?>>Submitted</option>
                    <option value="acknowledged" <?php echo e(request('status') === 'acknowledged' ? 'selected' : ''); ?>>Acknowledged</option>
                </select>
                <button type="submit" class="btn btn-secondary btn-sm">Filter</button>
                <a href="<?php echo e(route('performance.index')); ?>" class="btn btn-secondary btn-sm">Clear</a>
            </form>
        <?php endif; ?>

        <?php if($reviews->count() > 0): ?>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Period</th>
                            <th>Year</th>
                            <th>Score</th>
                            <th>Increment</th>
                            <th>Status</th>
                            <?php if(auth()->user()->isAdmin()): ?>
                                <th>Reviewer</th>
                            <?php endif; ?>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $reviews; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $review): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td class="font-bold"><?php echo e($review->employee->user->name); ?></td>
                                <td>
                                    <span class="badge badge-info"><?php echo e($review->review_period); ?></span>
                                </td>
                                <td><?php echo e($review->period_year); ?></td>
                                <td>
                                    <?php
                                        $score = (float) $review->overall_score;
                                        $scoreColor = $score >= 7 ? 'var(--success)' : ($score >= 5 ? 'var(--warning)' : 'var(--danger)');
                                    ?>
                                    <span style="font-weight: 700; font-size: 1.05rem; color: <?php echo e($scoreColor); ?>;">
                                        <?php echo e(number_format($score, 1)); ?>/10
                                    </span>
                                </td>
                                <td>
                                    <?php if($review->salary_increment_pct > 0): ?>
                                        <span class="badge badge-success">+<?php echo e($review->salary_increment_pct); ?>%</span>
                                    <?php else: ?>
                                        <span style="color: var(--text-muted);">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($review->status === 'acknowledged'): ?>
                                        <span class="badge badge-success">Acknowledged</span>
                                    <?php elseif($review->status === 'submitted'): ?>
                                        <span class="badge badge-info">Submitted</span>
                                    <?php else: ?>
                                        <span class="badge badge-warning">Draft</span>
                                    <?php endif; ?>
                                </td>
                                <?php if(auth()->user()->isAdmin()): ?>
                                    <td style="font-size: 0.85rem; color: var(--text-secondary);"><?php echo e($review->reviewer->name); ?></td>
                                <?php endif; ?>
                                <td>
                                    <div class="btn-group">
                                        <a href="<?php echo e(route('performance.show', $review)); ?>" class="btn btn-sm btn-secondary">View</a>
                                        <?php if(auth()->user()->isAdmin() && $review->status === 'draft'): ?>
                                            <form action="<?php echo e(route('performance.submit', $review)); ?>" method="POST" style="display:inline">
                                                <?php echo csrf_field(); ?>
                                                <button type="submit" class="btn btn-sm btn-primary">Submit</button>
                                            </form>
                                        <?php endif; ?>
                                        <?php if(!auth()->user()->isAdmin() && $review->status === 'submitted'): ?>
                                            <form action="<?php echo e(route('performance.acknowledge', $review)); ?>" method="POST" style="display:inline">
                                                <?php echo csrf_field(); ?>
                                                <button type="submit" class="btn btn-sm btn-success">Acknowledge</button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>

            <div class="pagination-wrapper">
                <?php echo e($reviews->links()); ?>

            </div>
        <?php else: ?>
            <div class="empty-state">
                <div style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.4;">📊</div>
                <h3>No performance reviews found</h3>
                <?php if(auth()->user()->isAdmin()): ?>
                    <p>Create performance reviews to evaluate employee performance.</p>
                    <a href="<?php echo e(route('performance.create')); ?>" class="btn btn-primary" style="margin-top: 1rem;">Create Review</a>
                <?php else: ?>
                    <p>You have no performance reviews yet.</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\ishma\Music\iims\resources\views/performance/index.blade.php ENDPATH**/ ?>