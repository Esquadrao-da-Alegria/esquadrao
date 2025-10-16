<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'code',
    'grammar',
    'lightTheme' => 'light-plus',
    'darkTheme' => 'dark-plus',
    'withGutter' => false,
    'startingLine' => 1,
    'highlightedLine' => null,
    'truncate' => false,
]));

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

foreach (array_filter(([
    'code',
    'grammar',
    'lightTheme' => 'light-plus',
    'darkTheme' => 'dark-plus',
    'withGutter' => false,
    'startingLine' => 1,
    'highlightedLine' => null,
    'truncate' => false,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php use \Phiki\Phiki; ?>
<?php use \Phiki\Grammar\Grammar; ?>
<?php use \Phiki\Theme\Theme; ?>
<?php use \Phiki\Transformers\Decorations\GutterDecoration; ?>
<?php use \Phiki\Transformers\Decorations\LineDecoration; ?>
<?php use \Phiki\Transformers\Decorations\PreDecoration; ?>

<?php
    $highlightedCode = (new Phiki)->codeToHtml($code, $grammar, ['light' => $lightTheme, 'dark' => $darkTheme])
        ->withGutter($withGutter)
        ->startingLine($startingLine)
        ->decoration(
            PreDecoration::make()->class('bg-transparent!', $truncate ? ' truncate' : ''),
            GutterDecoration::make()->class('mr-6 text-neutral-500! dark:text-neutral-600!'),
        );

    if ($highlightedLine !== null) {
        $highlightedCode->decoration(
            LineDecoration::forLine($highlightedLine)->class('bg-rose-200! [&_.line-number]:dark:text-white! dark:bg-rose-900!'),
        );
    }
?>

<div
    <?php echo e($attributes); ?>

>
    <?php echo $highlightedCode; ?>

</div>
<?php /**PATH /var/www/html/vendor/laravel/framework/src/Illuminate/Foundation/Providers/../resources/exceptions/renderer/components/syntax-highlight.blade.php ENDPATH**/ ?>