<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag; ?>
<?php foreach($attributes->onlyProps([
    'amount' => 0,
]) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $attributes = $attributes->exceptProps([
    'amount' => 0,
]); ?>
<?php foreach (array_filter(([
    'amount' => 0,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>

<?php
    $currency = app(\App\Services\CurrencyService::class);
    $value = is_numeric($amount) ? (float) $amount : 0.0;
?>

<?php echo e($currency->format($value)); ?>

<?php /**PATH C:\xampp\htdocs\digify\resources\views/components/money.blade.php ENDPATH**/ ?>