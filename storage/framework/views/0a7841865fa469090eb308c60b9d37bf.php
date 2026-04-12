<?php $__env->startSection('title', 'Payroll Details'); ?>
<?php $__env->startSection('page-title', 'Payroll — ' . $payroll->periodLabel()); ?>

<?php $__env->startSection('content'); ?>
    <div class="card mb-3">
        <div class="card-header">
            <div>
                <h3 class="card-title"><?php echo e($payroll->periodLabel()); ?></h3>
                <p class="text-secondary" style="font-size: 0.82rem; margin-top: 0.25rem;">
                    Generated <?php echo e($payroll->created_at->format('M d, Y \a\t h:i A')); ?>

                </p>
            </div>
            <div class="btn-group">
                <?php if($payroll->status === 'processed'): ?>
                    <span class="badge badge-info" style="font-size: 0.85rem; padding: 0.4rem 1rem;">Processed</span>
                    <form action="<?php echo e(route('payroll.markPaid', $payroll)); ?>" method="POST">
                        <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                        <button type="submit" class="btn btn-sm btn-success">Mark as Paid</button>
                    </form>
                <?php elseif($payroll->status === 'paid'): ?>
                    <span class="badge badge-success" style="font-size: 0.85rem; padding: 0.4rem 1rem;">Paid</span>
                <?php else: ?>
                    <span class="badge badge-warning" style="font-size: 0.85rem; padding: 0.4rem 1rem;">Draft</span>
                <?php endif; ?>
                <a href="<?php echo e(route('payroll.index')); ?>" class="btn btn-sm btn-secondary">Back</a>
            </div>
        </div>

        
        <div class="stats-grid" style="margin-bottom: 0;">
            <div class="stat-card">
                <div class="stat-icon blue">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                </div>
                <div class="stat-info">
                    <h3><?php echo e($payroll->payslips->count()); ?></h3>
                    <div class="stat-label">Employees</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                </div>
                <div class="stat-info">
                    <h3>NLE <?php echo e(number_format($payroll->payslips->sum('net_salary'), 2)); ?></h3>
                    <div class="stat-label">Total Net Payroll</div>
                </div>
            </div>
        </div>
    </div>

    
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Payslips</h3>
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Department</th>
                        <th>Basic Salary</th>
                        <th>Allowances</th>
                        <th>Deductions</th>
                        <th>Days Worked</th>
                        <th>Days Absent</th>
                        <th>Net Salary</th>
                        <th>Flags</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $payroll->payslips; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $slip): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td>
                                <div class="font-bold"><?php echo e($slip->employee->user->name); ?></div>
                                <div class="text-muted" style="font-size: 0.75rem"><?php echo e($slip->employee->employee_id); ?></div>
                            </td>
                            <td class="text-secondary"><?php echo e($slip->employee->department->name); ?></td>
                            <td>NLE <?php echo e(number_format($slip->basic_salary, 2)); ?></td>
                            <td class="text-success">+NLE <?php echo e(number_format($slip->allowances, 2)); ?></td>
                            <td class="text-danger">-NLE <?php echo e(number_format($slip->deductions, 2)); ?></td>
                            <td><?php echo e($slip->days_worked); ?></td>
                            <td><?php echo e($slip->days_absent); ?></td>
                            <td class="text-accent font-bold">NLE <?php echo e(number_format($slip->net_salary, 2)); ?></td>
                            <td>
                                <?php if($slip->anomalies->isNotEmpty()): ?>
                                    <div style="display:flex; flex-wrap:wrap; gap:0.25rem;">
                                        <?php $__currentLoopData = $slip->anomalies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $anomaly): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <span title="<?php echo e($anomaly->description); ?>"
                                                  style="display:inline-block; padding:0.15rem 0.45rem; border-radius:4px; font-size:0.68rem; font-weight:600; background:<?php echo e($anomaly->severityColor()); ?>22; color:<?php echo e($anomaly->severityColor()); ?>; border:1px solid <?php echo e($anomaly->severityColor()); ?>44; cursor:default; white-space:nowrap;">
                                                <?php echo e($anomaly->typeLabel()); ?>

                                            </span>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>
                                <?php else: ?>
                                    <span class="text-secondary" style="font-size:0.75rem;">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="<?php echo e(route('payslips.show', $slip)); ?>" class="btn btn-sm btn-secondary">View</a>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="8" class="text-right font-bold">Total:</td>
                        <td class="text-accent font-bold">NLE <?php echo e(number_format($payroll->payslips->sum('net_salary'), 2)); ?></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\ishma\Music\iims\resources\views/payroll/show.blade.php ENDPATH**/ ?>