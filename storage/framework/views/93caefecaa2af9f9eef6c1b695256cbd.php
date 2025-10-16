<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['request']));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['request']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div class="flex flex-col gap-3">
    <h2 class="text-lg font-semibold text-neutral-900 dark:text-white">Overview</h2>
    <div class="flex flex-col">
        <!-- Date Row -->
        <div class="flex items-center gap-2 h-10">
            <div class="text-sm font-mono text-neutral-500 dark:text-neutral-400 uppercase">DATE</div>
            <div class="flex-1 h-3 border-b-2 border-dotted border-neutral-300 dark:border-white/20"></div>
            <div class="text-sm font-mono text-neutral-900 dark:text-white">
                <?php echo e(now()->format('Y/m/d H:i:s.v')); ?> <span class="text-neutral-500">UTC</span>
            </div>
        </div>
        <!-- Status Code Row -->
        <div class="flex items-center gap-2 h-10">
            <div class="text-sm font-mono text-neutral-500 dark:text-neutral-400 uppercase">STATUS CODE</div>
            <div class="flex-1 h-3 border-b-2 border-dotted border-neutral-300 dark:border-white/20"></div>
            <?php if (isset($component)) { $__componentOriginal0bc865510ef3ecddbe48edc4e8cc9ddb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0bc865510ef3ecddbe48edc4e8cc9ddb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'laravel-exceptions-renderer::components.badge','data' => ['type' => 'error','variant' => 'solid']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('laravel-exceptions-renderer::badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'error','variant' => 'solid']); ?>
                <?php if (isset($component)) { $__componentOriginalebc8ec9a834a8051f56913d6745a7050 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalebc8ec9a834a8051f56913d6745a7050 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'laravel-exceptions-renderer::components.icons.alert','data' => ['class' => 'w-2.5 h-2.5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('laravel-exceptions-renderer::icons.alert'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-2.5 h-2.5']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalebc8ec9a834a8051f56913d6745a7050)): ?>
<?php $attributes = $__attributesOriginalebc8ec9a834a8051f56913d6745a7050; ?>
<?php unset($__attributesOriginalebc8ec9a834a8051f56913d6745a7050); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalebc8ec9a834a8051f56913d6745a7050)): ?>
<?php $component = $__componentOriginalebc8ec9a834a8051f56913d6745a7050; ?>
<?php unset($__componentOriginalebc8ec9a834a8051f56913d6745a7050); ?>
<?php endif; ?>
                500
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal0bc865510ef3ecddbe48edc4e8cc9ddb)): ?>
<?php $attributes = $__attributesOriginal0bc865510ef3ecddbe48edc4e8cc9ddb; ?>
<?php unset($__attributesOriginal0bc865510ef3ecddbe48edc4e8cc9ddb); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal0bc865510ef3ecddbe48edc4e8cc9ddb)): ?>
<?php $component = $__componentOriginal0bc865510ef3ecddbe48edc4e8cc9ddb; ?>
<?php unset($__componentOriginal0bc865510ef3ecddbe48edc4e8cc9ddb); ?>
<?php endif; ?>
        </div>
        <!-- Method Row -->
        <div class="flex items-center gap-2 h-10">
            <div class="text-sm font-mono text-neutral-500 dark:text-neutral-400 uppercase">METHOD</div>
            <div class="flex-1 h-3 border-b-2 border-dotted border-neutral-300 dark:border-white/20"></div>
            <?php if (isset($component)) { $__componentOriginal5131cdd8ffd44ce9fe7ed2c3030dd413 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5131cdd8ffd44ce9fe7ed2c3030dd413 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'laravel-exceptions-renderer::components.http-method','data' => ['method' => ''.e($request->method()).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('laravel-exceptions-renderer::http-method'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['method' => ''.e($request->method()).'']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5131cdd8ffd44ce9fe7ed2c3030dd413)): ?>
<?php $attributes = $__attributesOriginal5131cdd8ffd44ce9fe7ed2c3030dd413; ?>
<?php unset($__attributesOriginal5131cdd8ffd44ce9fe7ed2c3030dd413); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5131cdd8ffd44ce9fe7ed2c3030dd413)): ?>
<?php $component = $__componentOriginal5131cdd8ffd44ce9fe7ed2c3030dd413; ?>
<?php unset($__componentOriginal5131cdd8ffd44ce9fe7ed2c3030dd413); ?>
<?php endif; ?>
        </div>
    </div>
</div>
<?php /**PATH /var/www/html/vendor/laravel/framework/src/Illuminate/Foundation/Providers/../resources/exceptions/renderer/components/overview.blade.php ENDPATH**/ ?>