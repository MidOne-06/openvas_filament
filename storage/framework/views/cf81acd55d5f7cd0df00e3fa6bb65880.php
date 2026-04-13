<div
    <?php echo e($attributes
            ->merge([
                'id' => $getId(),
            ], escape: false)
            ->merge($getExtraAttributes(), escape: false)); ?>

>
    <?php echo e($getChildComponentContainer()); ?>

</div>
<?php /**PATH D:\PRIVADO\PROYECTOS JEAN\OPENVAS-SOFTWARE\OPENVAS_FILAMENT\vendor\filament\forms\resources\views/components/group.blade.php ENDPATH**/ ?>