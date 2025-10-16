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

<div
    x-data="{
        copied: false,
        async copyToClipboard() {
            try {
                await window.copyToClipboard('<?php echo e($request->fullUrl()); ?>');
                this.copied = true;
                setTimeout(() => { this.copied = false }, 3000);
            } catch (err) {
                console.error('Failed to copy the requestURL: ', err);
            }
        }
    }"
    <?php echo e($attributes->merge(['class' => "bg-white dark:bg-[#1a1a1a] border border-neutral-200 dark:border-white/10 rounded-lg flex items-center justify-between h-10 px-2 shadow-xs"])); ?>

>
    <div class="flex items-center gap-3 w-full">
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
            <?php if (isset($component)) { $__componentOriginalba2eecb54ab69c011eea9820c76048d8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalba2eecb54ab69c011eea9820c76048d8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'laravel-exceptions-renderer::components.icons.globe','data' => ['class' => 'w-2.5 h-2.5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('laravel-exceptions-renderer::icons.globe'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-2.5 h-2.5']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalba2eecb54ab69c011eea9820c76048d8)): ?>
<?php $attributes = $__attributesOriginalba2eecb54ab69c011eea9820c76048d8; ?>
<?php unset($__attributesOriginalba2eecb54ab69c011eea9820c76048d8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalba2eecb54ab69c011eea9820c76048d8)): ?>
<?php $component = $__componentOriginalba2eecb54ab69c011eea9820c76048d8; ?>
<?php unset($__componentOriginalba2eecb54ab69c011eea9820c76048d8); ?>
<?php endif; ?>
            <?php echo e($request->method()); ?>

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
        <div class="flex-1 text-sm font-light truncate text-neutral-950 dark:text-white">
            <span data-tippy-content="<?php echo e($request->fullUrl()); ?>">
                <?php echo e($request->fullUrl()); ?>

            </span>
        </div>
        <button
            x-cloak
            @click="copyToClipboard()"
            class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                "rounded-md w-6 h-6 flex flex-shrink-0 items-center justify-center cursor-pointer border transition-colors duration-200 ease-in-out",
                "bg-white/5 border-neutral-200 hover:bg-neutral-100 dark:bg-white/5 dark:border-white/10 dark:hover:bg-white/10",
            ]); ?>"
        >
            <?php if (isset($component)) { $__componentOriginal8894ff2e6e6bd543865d608162806b35 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8894ff2e6e6bd543865d608162806b35 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'laravel-exceptions-renderer::components.icons.copy','data' => ['class' => 'w-3 h-3 text-neutral-400','xShow' => '!copied']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('laravel-exceptions-renderer::icons.copy'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-3 h-3 text-neutral-400','x-show' => '!copied']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8894ff2e6e6bd543865d608162806b35)): ?>
<?php $attributes = $__attributesOriginal8894ff2e6e6bd543865d608162806b35; ?>
<?php unset($__attributesOriginal8894ff2e6e6bd543865d608162806b35); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8894ff2e6e6bd543865d608162806b35)): ?>
<?php $component = $__componentOriginal8894ff2e6e6bd543865d608162806b35; ?>
<?php unset($__componentOriginal8894ff2e6e6bd543865d608162806b35); ?>
<?php endif; ?>
            <?php if (isset($component)) { $__componentOriginal394a4f59b8774713925fcf456ba90b57 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal394a4f59b8774713925fcf456ba90b57 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'laravel-exceptions-renderer::components.icons.check','data' => ['class' => 'w-3 h-3 text-emerald-500','xShow' => 'copied']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('laravel-exceptions-renderer::icons.check'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-3 h-3 text-emerald-500','x-show' => 'copied']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal394a4f59b8774713925fcf456ba90b57)): ?>
<?php $attributes = $__attributesOriginal394a4f59b8774713925fcf456ba90b57; ?>
<?php unset($__attributesOriginal394a4f59b8774713925fcf456ba90b57); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal394a4f59b8774713925fcf456ba90b57)): ?>
<?php $component = $__componentOriginal394a4f59b8774713925fcf456ba90b57; ?>
<?php unset($__componentOriginal394a4f59b8774713925fcf456ba90b57); ?>
<?php endif; ?>
        </button>
    </div>
</div>
<?php /**PATH /var/www/html/vendor/laravel/framework/src/Illuminate/Foundation/Providers/../resources/exceptions/renderer/components/request-url.blade.php ENDPATH**/ ?>