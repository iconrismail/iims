<?php $__env->startSection('title', 'Overtime Records'); ?>
<?php $__env->startSection('page-title', 'Overtime Records'); ?>

<?php $__env->startSection('content'); ?>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Overtime Records</h3>
            <a href="<?php echo e(route('overtime.create')); ?>" class="btn btn-primary btn-sm">+ Add Overtime</a>
        </div>

        
        <form method="GET" action="<?php echo e(route('overtime.index')); ?>" style="display: flex; gap: 0.75rem; flex-wrap: wrap; margin-bottom: 1.5rem;">
            <select name="employee_id" class="form-control" style="width: auto; min-width: 180px;">
                <option value="">All Employees</option>
                <?php $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $emp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($emp->id); ?>" <?php echo e(request('employee_id') == $emp->id ? 'selected' : ''); ?>>
                        <?php echo e($emp->user->name); ?>

                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <select name="month" class="form-control" style="width: auto;">
                <option value="">All Months</option>
                <?php for($m = 1; $m <= 12; $m++): ?>
                    <option value="<?php echo e($m); ?>" <?php echo e(request('month') == $m ? 'selected' : ''); ?>>
                        <?php echo e(date('F', mktime(0,0,0,$m,1))); ?>

                    </option>
                <?php endfor; ?>
            </select>
            <select name="year" class="form-control" style="width: auto;">
                <option value="">All Years</option>
                <?php for($y = date('Y'); $y >= date('Y') - 3; $y--): ?>
                    <option value="<?php echo e($y); ?>" <?php echo e(request('year') == $y ? 'selected' : ''); ?>><?php echo e($y); ?></option>
                <?php endfor; ?>
            </select>
            <select name="status" class="form-control" style="width: auto;">
                <option value="">All Status</option>
                <option value="pending" <?php echo e(request('status') === 'pending' ? 'selected' : ''); ?>>Pending</option>
                <option value="approved" <?php echo e(request('status') === 'approved' ? 'selected' : ''); ?>>Approved</option>
                <option value="rejected" <?php echo e(request('status') === 'rejected' ? 'selected' : ''); ?>>Rejected</option>
            </select>
            <button type="submit" class="btn btn-secondary btn-sm">Filter</button>
            <a href="<?php echo e(route('overtime.index')); ?>" class="btn btn-secondary btn-sm">Clear</a>
        </form>

        <?php if($overtimes->count() > 0): ?>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Employee</th>
                            <th>Hours</th>
                            <th>Rate</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Notes</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $overtimes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr style="<?php echo e($ot->status === 'pending' ? 'background: rgba(255, 171, 64, 0.05);' : ''); ?>">
                                <td><?php echo e($ot->date->format('M d, Y')); ?></td>
                                <td class="font-bold"><?php echo e($ot->employee->user->name); ?></td>
                                <td><?php echo e($ot->hours); ?>h</td>
                                <td><?php echo e($ot->rate_multiplier); ?>x</td>
                                <td class="text-accent font-bold">NLE <?php echo e(number_format($ot->amount, 2)); ?></td>
                                <td>
                                    <?php if($ot->status === 'approved'): ?>
                                        <span class="badge badge-success">Approved</span>
                                    <?php elseif($ot->status === 'rejected'): ?>
                                        <span class="badge badge-danger">Rejected</span>
                                    <?php else: ?>
                                        <span class="badge badge-warning">Pending</span>
                                    <?php endif; ?>
                                </td>
                                <td style="color: var(--text-secondary); font-size: 0.85rem;"><?php echo e(Str::limit($ot->notes, 40)); ?></td>
                                <td>
                                    <div class="btn-group">
                                        <?php if($ot->status === 'pending'): ?>
                                            <form action="<?php echo e(route('overtime.approve', $ot)); ?>" method="POST" style="display:inline">
                                                <?php echo csrf_field(); ?>
                                                <button type="submit" class="btn btn-sm btn-success">Approve</button>
                                            </form>
                                            <form action="<?php echo e(route('overtime.reject', $ot)); ?>" method="POST" style="display:inline">
                                                <?php echo csrf_field(); ?>
                                                <button type="submit" class="btn btn-sm btn-secondary">Reject</button>
                                            </form>
                                        <?php endif; ?>
                                        <form action="<?php echo e(route('overtime.destroy', $ot)); ?>" method="POST" style="display:inline" onsubmit="return confirm('Delete this record?')">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>

            <div class="pagination-wrapper">
                <?php echo e($overtimes->links()); ?>

            </div>
        <?php else: ?>
            <div class="empty-state">
                <div style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.4;">⏱</div>
                <h3>No overtime records found</h3>
                <p>Add overtime records to track extra hours worked.</p>
                <a href="<?php echo e(route('overtime.create')); ?>" class="btn btn-primary" style="margin-top: 1rem;">Add Overtime</a>
            </div>
        <?php endif; ?>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\ishma\Music\iims\resources\views/overtime/index.blade.php ENDPATH**/ ?>