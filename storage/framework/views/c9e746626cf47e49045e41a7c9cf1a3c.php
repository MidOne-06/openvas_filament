<?php if (isset($component)) { $__componentOriginal166a02a7c5ef5a9331faf66fa665c256 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal166a02a7c5ef5a9331faf66fa665c256 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament-panels::components.page.index','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament-panels::page'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>

<style>
    :root {
        --cv-card-bg: #ffffff;
        --cv-card-border: #e5e7eb;
        --cv-text: #111827;
        --cv-text-muted: #6b7280;
        --cv-text-light: #9ca3af;
        --cv-row-even: #ffffff;
        --cv-row-odd: #f9fafb;
        --cv-table-head-bg: #f9fafb;
        --cv-table-border: #e5e7eb;
        --cv-icon-bg: #f3f4f6;
    }
    .dark {
        --cv-card-bg: #1f2937;
        --cv-card-border: #374151;
        --cv-text: #f9fafb;
        --cv-text-muted: #9ca3af;
        --cv-text-light: #6b7280;
        --cv-row-even: #1f2937;
        --cv-row-odd: #111827;
        --cv-table-head-bg: #111827;
        --cv-table-border: #374151;
        --cv-icon-bg: #374151;
    }
</style>

    
    <?php
        if (!$checked) {
            $headerBg     = 'var(--cv-card-bg)';
            $headerBorder = 'var(--cv-card-border)';
            $iconBg       = '#e5e7eb';
            $titleStyle   = 'color:var(--cv-text-muted)';
            $subStyle     = 'color:var(--cv-text-light)';
        } elseif ($connected) {
            $headerBg     = '#f0fdf4';
            $headerBorder = '#86efac';
            $iconBg       = '#22c55e';
            $titleStyle   = 'color:#15803d';
            $subStyle     = 'color:#16a34a';
        } else {
            $headerBg     = '#fef2f2';
            $headerBorder = '#fca5a5';
            $iconBg       = '#ef4444';
            $titleStyle   = 'color:#b91c1c';
            $subStyle     = 'color:#dc2626';
        }
    ?>

    <div style="background:<?php echo e($headerBg); ?>;border:1px solid <?php echo e($headerBorder); ?>;border-radius:16px;padding:20px;margin-bottom:16px;">
        <div style="display:flex;align-items:center;gap:16px;">
            <div style="flex-shrink:0;">
                <div style="height:56px;width:56px;border-radius:50%;background:<?php echo e($iconBg); ?>;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 8px rgba(0,0,0,0.12);">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$checked): ?>
                        <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" fill="none" viewBox="0 0 24 24" stroke="#9ca3af" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.288 15.038a5.25 5.25 0 017.424 0M5.106 11.856c3.807-3.808 9.98-3.808 13.788 0M1.924 8.674c5.565-5.565 14.587-5.565 20.152 0M12.53 18.22l-.53.53-.53-.53a.75.75 0 011.06 0z"/>
                        </svg>
                    <?php elseif($connected): ?>
                        <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    <?php else: ?>
                        <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
            <div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$checked): ?>
                    <h2 style="margin:0;font-size:17px;font-weight:600;color:var(--cv-text);">Conexion sin verificar</h2>
                    <p style="margin:4px 0 0;font-size:13px;color:var(--cv-text-muted);">Haz clic en "Verificar Conexion" para comprobar el estado.</p>
                <?php elseif($connected): ?>
                    <h2 style="margin:0;font-size:17px;font-weight:600;<?php echo e($titleStyle); ?>">Conectado y autenticado</h2>
                    <p style="margin:4px 0 0;font-size:13px;<?php echo e($subStyle); ?>">
                        OpenVAS accesible<?php echo e($vmInfo ? ' — ' . $vmInfo : ''); ?>

                    </p>
                <?php else: ?>
                    <h2 style="margin:0;font-size:17px;font-weight:600;<?php echo e($titleStyle); ?>">Sin conexion</h2>
                    <p style="margin:4px 0 0;font-size:13px;<?php echo e($subStyle); ?>;font-family:monospace;"><?php echo e($errorMessage); ?></p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($checked): ?>
    
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:16px;">

        
        <div style="background:var(--cv-card-bg);border:1px solid var(--cv-card-border);border-radius:12px;padding:16px;text-align:center;box-shadow:0 1px 3px rgba(0,0,0,0.06);">
            <div style="font-size:11px;color:var(--cv-text-muted);text-transform:uppercase;letter-spacing:0.06em;margin-bottom:10px;font-weight:600;">TCP</div>
            <div style="width:40px;height:40px;border-radius:50%;background:<?php echo e($connected ? '#dcfce7' : '#fee2e2'); ?>;display:flex;align-items:center;justify-content:center;margin:0 auto 6px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="<?php echo e($connected ? '#16a34a' : '#dc2626'); ?>" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.348 14.651a3.75 3.75 0 010-5.303m5.304 0a3.75 3.75 0 010 5.303m-7.425 2.122a6.75 6.75 0 010-9.546m9.546 0a6.75 6.75 0 010 9.546M5.106 18.394c-3.807-3.807-3.807-9.98 0-13.788m13.788 0c3.807 3.807 3.807 9.981 0 13.788M12 12h.008v.008H12V12z"/>
                </svg>
            </div>
            <div style="font-size:12px;font-weight:600;color:<?php echo e($connected ? '#16a34a' : '#dc2626'); ?>;"><?php echo e($connected ? 'Reachable' : 'Unreachable'); ?></div>
        </div>

        
        <div style="background:var(--cv-card-bg);border:1px solid var(--cv-card-border);border-radius:12px;padding:16px;text-align:center;box-shadow:0 1px 3px rgba(0,0,0,0.06);">
            <div style="font-size:11px;color:var(--cv-text-muted);text-transform:uppercase;letter-spacing:0.06em;margin-bottom:10px;font-weight:600;">Latencia</div>
            <div style="font-size:26px;font-weight:700;color:#2563eb;line-height:1.1;">
                <?php echo e($latencyMs !== null ? number_format($latencyMs, 1) : '—'); ?>

            </div>
            <div style="font-size:11px;color:var(--cv-text-light);margin-top:2px;">ms</div>
        </div>

        
        <div style="background:var(--cv-card-bg);border:1px solid var(--cv-card-border);border-radius:12px;padding:16px;text-align:center;box-shadow:0 1px 3px rgba(0,0,0,0.06);">
            <div style="font-size:11px;color:var(--cv-text-muted);text-transform:uppercase;letter-spacing:0.06em;margin-bottom:10px;font-weight:600;">GMP Auth</div>
            <div style="width:40px;height:40px;border-radius:50%;background:<?php echo e($authenticated ? '#dcfce7' : '#fef9c3'); ?>;display:flex;align-items:center;justify-content:center;margin:0 auto 6px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="<?php echo e($authenticated ? '#16a34a' : '#ca8a04'); ?>" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/>
                </svg>
            </div>
            <div style="font-size:12px;font-weight:600;color:<?php echo e($authenticated ? '#16a34a' : '#ca8a04'); ?>;"><?php echo e($authenticated ? 'Autenticado' : 'Sin autenticar'); ?></div>
        </div>

        
        <div style="background:var(--cv-card-bg);border:1px solid var(--cv-card-border);border-radius:12px;padding:16px;text-align:center;box-shadow:0 1px 3px rgba(0,0,0,0.06);">
            <div style="font-size:11px;color:var(--cv-text-muted);text-transform:uppercase;letter-spacing:0.06em;margin-bottom:10px;font-weight:600;">Version OpenVAS</div>
            <div style="font-size:13px;font-weight:700;color:#4f46e5;word-break:break-all;">
                <?php echo e($openvasVersion ?: ($versionInfo['openvas'] ?? 'N/A')); ?>

            </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($versionInfo['gmp'] ?? false): ?>
            <div style="font-size:11px;color:var(--cv-text-light);margin-top:4px;">GMP: <?php echo e($versionInfo['gmp']); ?></div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($versionInfo)): ?>
    <div style="background:var(--cv-card-bg);border:1px solid var(--cv-card-border);border-radius:12px;padding:16px;margin-bottom:16px;box-shadow:0 1px 3px rgba(0,0,0,0.06);">
        <h3 style="margin:0 0 12px;font-size:13px;font-weight:600;color:var(--cv-text);display:flex;align-items:center;gap:8px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#6366f1" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/>
            </svg>
            Informacion de Version
        </h3>
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;font-size:13px;">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $versionInfo; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($value && !is_array($value)): ?>
            <div style="border-left:3px solid #6366f1;padding-left:10px;">
                <div style="font-size:10px;color:var(--cv-text-muted);text-transform:uppercase;letter-spacing:0.06em;"><?php echo e($key); ?></div>
                <div style="font-family:monospace;font-weight:600;color:var(--cv-text);margin-top:2px;"><?php echo e($value); ?></div>
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($history)): ?>
    <div style="background:var(--cv-card-bg);border:1px solid var(--cv-card-border);border-radius:12px;padding:16px;box-shadow:0 1px 3px rgba(0,0,0,0.06);">
        <h3 style="margin:0 0 12px;font-size:13px;font-weight:600;color:var(--cv-text);display:flex;align-items:center;gap:8px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#6366f1" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Historial de Operaciones Recientes (<?php echo e(count($history)); ?>)
        </h3>
        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:12px;">
                <thead>
                    <tr style="background:var(--cv-table-head-bg);border-bottom:2px solid var(--cv-table-border);">
                        <th style="text-align:left;padding:8px 10px;font-weight:600;color:var(--cv-text-muted);font-size:10px;text-transform:uppercase;">Accion</th>
                        <th style="text-align:left;padding:8px 10px;font-weight:600;color:var(--cv-text-muted);font-size:10px;text-transform:uppercase;">Recurso</th>
                        <th style="text-align:left;padding:8px 10px;font-weight:600;color:var(--cv-text-muted);font-size:10px;text-transform:uppercase;">Estado</th>
                        <th style="text-align:left;padding:8px 10px;font-weight:600;color:var(--cv-text-muted);font-size:10px;text-transform:uppercase;">Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $history; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $entry): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php $rowBg = $i % 2 === 0 ? 'var(--cv-row-even)' : 'var(--cv-row-odd)'; ?>
                    <tr style="background:<?php echo e($rowBg); ?>;border-bottom:1px solid var(--cv-table-border);">
                        <td style="padding:8px 10px;font-weight:600;color:var(--cv-text);"><?php echo e($entry['action'] ?? '—'); ?></td>
                        <td style="padding:8px 10px;font-family:monospace;color:#4f46e5;"><?php echo e($entry['resource_id'] ?? $entry['resource'] ?? '—'); ?></td>
                        <td style="padding:8px 10px;">
                            <?php $ok = ($entry['status'] ?? '') === 'success'; ?>
                            <span style="display:inline-block;padding:2px 8px;border-radius:9999px;font-size:10px;font-weight:600;background:<?php echo e($ok ? '#dcfce7' : '#fee2e2'); ?>;color:<?php echo e($ok ? '#15803d' : '#b91c1c'); ?>;">
                                <?php echo e($entry['status'] ?? '—'); ?>

                            </span>
                        </td>
                        <td style="padding:8px 10px;color:var(--cv-text-muted);"><?php echo e($entry['timestamp'] ?? $entry['created_at'] ?? '—'); ?></td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php else: ?>
    
    <div style="background:var(--cv-card-bg);border:2px dashed var(--cv-card-border);border-radius:16px;padding:60px;text-align:center;">
        <div style="width:72px;height:72px;border-radius:50%;background:var(--cv-icon-bg);display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" fill="none" viewBox="0 0 24 24" stroke="var(--cv-text-light)" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.288 15.038a5.25 5.25 0 017.424 0M5.106 11.856c3.807-3.808 9.98-3.808 13.788 0M1.924 8.674c5.565-5.565 14.587-5.565 20.152 0M12.53 18.22l-.53.53-.53-.53a.75.75 0 011.06 0z"/>
            </svg>
        </div>
        <p style="font-size:14px;color:var(--cv-text-muted);margin:0;">
            Presiona "Verificar Conexion" para comprobar el estado de la conexion con la VM de OpenVAS.
        </p>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal166a02a7c5ef5a9331faf66fa665c256)): ?>
<?php $attributes = $__attributesOriginal166a02a7c5ef5a9331faf66fa665c256; ?>
<?php unset($__attributesOriginal166a02a7c5ef5a9331faf66fa665c256); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal166a02a7c5ef5a9331faf66fa665c256)): ?>
<?php $component = $__componentOriginal166a02a7c5ef5a9331faf66fa665c256; ?>
<?php unset($__componentOriginal166a02a7c5ef5a9331faf66fa665c256); ?>
<?php endif; ?>
<?php /**PATH D:\PRIVADO\PROYECTOS JEAN\OPENVAS-SOFTWARE\OPENVAS_FILAMENT\resources\views/filament/pages/conectividad.blade.php ENDPATH**/ ?>