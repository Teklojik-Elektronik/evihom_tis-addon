<?php $__env->startSection('content'); ?>
    <div class="page page-center">
        <div class="container container-tight py-4">
            <div class="text-center mb-4 display-6 auth-logo-container">
                <?php echo backpack_theme_config('project_logo'); ?>

            </div>
            <div class="card card-md">
                <div class="card-body pt-0">
                    <?php echo $__env->make(backpack_view('auth.login.inc.form'), \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                </div>
            </div>
            <?php if(config('backpack.base.registration_open')): ?>
                <div class="text-center text-muted mt-4">
                    <a tabindex="6" href="<?php echo e(route('backpack.auth.register')); ?>"><?php echo e(trans('backpack::base.register')); ?></a>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make(backpack_view('layouts.auth'), \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\Homeassistant TIS\Evihom\evihom_tis-addon\laravel\laravel\vendor/backpack/theme-tabler/resources/views/auth/login/default.blade.php ENDPATH**/ ?>