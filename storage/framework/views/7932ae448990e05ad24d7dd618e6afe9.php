<?php $__env->startSection('title', 'Settings'); ?>
<?php $__env->startSection('page-title', 'System Settings'); ?>

<?php $__env->startSection('content'); ?>
<form action="<?php echo e(route('settings.update')); ?>" method="POST" style="max-width:760px">
    <?php echo csrf_field(); ?>

    
    <div class="card mb-3">
        <div class="card-header">
            <h3 class="card-title">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right:0.5rem;vertical-align:-3px"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                Company Information
            </h3>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
            <div class="form-group">
                <label class="form-label">Company Name *</label>
                <input type="text" name="company_name" class="form-control"
                       value="<?php echo e(old('company_name', $settings['company_name'] ?? 'IIMS')); ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label">Company Email</label>
                <input type="email" name="company_email" class="form-control"
                       value="<?php echo e(old('company_email', $settings['company_email'] ?? '')); ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Phone</label>
                <input type="text" name="company_phone" class="form-control"
                       value="<?php echo e(old('company_phone', $settings['company_phone'] ?? '')); ?>" placeholder="+232-...">
            </div>
            <div class="form-group" style="grid-column:1/-1">
                <label class="form-label">Address</label>
                <input type="text" name="company_address" class="form-control"
                       value="<?php echo e(old('company_address', $settings['company_address'] ?? '')); ?>" placeholder="City, Country">
            </div>
        </div>
    </div>

    
    <div class="card mb-3">
        <div class="card-header">
            <h3 class="card-title">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right:0.5rem;vertical-align:-3px"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                Payroll Configuration
            </h3>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
            <div class="form-group">
                <label class="form-label">Currency Symbol *</label>
                <input type="text" name="currency_symbol" class="form-control"
                       value="<?php echo e(old('currency_symbol', $settings['currency_symbol'] ?? 'NLE')); ?>"
                       placeholder="NLE, USD, GBP…" required style="max-width:120px">
                <div class="form-hint">Used on payslips and throughout the system</div>
            </div>
            <div class="form-group">
                <label class="form-label">Fiscal Year Start Month *</label>
                <select name="fiscal_year_start_month" class="form-control" style="max-width:180px">
                    <?php $__currentLoopData = range(1,12); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($m); ?>" <?php echo e(($settings['fiscal_year_start_month'] ?? '1') == $m ? 'selected' : ''); ?>>
                            <?php echo e(date('F', mktime(0,0,0,$m,1))); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Working Days</label>
                <div style="display:flex;align-items:center;gap:0.75rem;margin-top:0.5rem">
                    <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;font-weight:400">
                        <input type="checkbox" name="include_saturdays" value="1"
                               <?php echo e(($settings['include_saturdays'] ?? '0') === '1' ? 'checked' : ''); ?>>
                        Include Saturdays as working days
                    </label>
                </div>
                <div class="form-hint">Affects payroll pro-rating and attendance calculations</div>
            </div>
        </div>
    </div>

    
    <div class="card mb-3">
        <div class="card-header">
            <h3 class="card-title">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right:0.5rem;vertical-align:-3px"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                Payslip Customisation
            </h3>
        </div>

        <div class="form-group">
            <label class="form-label">Payslip Footer Note</label>
            <textarea name="payslip_footer" class="form-control" rows="2"
                      placeholder="This is a computer-generated payslip."><?php echo e(old('payslip_footer', $settings['payslip_footer'] ?? '')); ?></textarea>
            <div class="form-hint">Appears at the bottom of every generated PDF payslip</div>
        </div>
    </div>

    <div>
        <button type="submit" class="btn btn-primary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right:0.4rem"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
            Save Settings
        </button>
    </div>
</form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\ishma\Music\iims\resources\views/settings/index.blade.php ENDPATH**/ ?>