<?php if (isset($component)) { $__componentOriginald956570c5321d7185b887a45463f814f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald956570c5321d7185b887a45463f814f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.site-layout','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('site-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="py-6">
            <div class="-mx-4 sm:-mx-6 lg:-mx-8">
                <div class="bg-white border border-gray-100 rounded-xl overflow-hidden px-0">
                <?php if(isset($banners) && $banners->count()): ?>
                    <div x-data="{
                            index: 0,
                            total: <?php echo e($banners->count()); ?>,
                            intervalMs: 4000,
                            timer: null,
                            width: 0,
                            updateWidth() {
                                this.width = this.$refs.viewport ? this.$refs.viewport.clientWidth : 0;
                            },
                            start() {
                                this.updateWidth();
                                if (this.timer || this.total <= 1) return;
                                this.timer = setInterval(() => this.next(), this.intervalMs);
                            },
                            stop() {
                                if (!this.timer) return;
                                clearInterval(this.timer);
                                this.timer = null;
                            },
                            next() {
                                this.index = (this.index + 1) % this.total;
                            },
                            goTo(i) {
                                this.index = i;
                            }
                        }"
                         x-init="updateWidth(); start(); window.addEventListener('resize', () => updateWidth())"
                         @mouseenter="stop()"
                         @mouseleave="start()"
                         class="relative overflow-hidden bg-gray-100"
                         x-cloak>
                        <div x-ref="viewport" class="w-full overflow-hidden">
                            <div class="flex transition-transform duration-700 ease-out will-change-transform"
                                 :style="`transform: translateX(-${index * width}px)`">
                            <?php $__currentLoopData = $banners; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $banner): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <a href="<?php echo e($banner->link ?: '#'); ?>" class="block w-full shrink-0 bg-gray-100">
                                    <?php if(!empty($banner->image)): ?>
                                        <img
                                            src="<?php echo e(Storage::url($banner->image)); ?>"
                                            alt="<?php echo e($banner->title); ?>"
                                            class="block w-full h-auto object-contain bg-gray-100"
                                            width="1280"
                                            height="448"
                                            <?php if($i === 0): ?> fetchpriority="high" loading="eager" <?php else: ?> loading="lazy" decoding="async" <?php endif; ?>
                                        />
                                    <?php else: ?>
                                        <div class="w-full min-h-[160px] bg-gray-100"></div>
                                    <?php endif; ?>
                                </a>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>

                        <div class="absolute inset-x-0 bottom-3 flex items-center justify-center gap-2">
                            <?php $__currentLoopData = $banners; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $banner): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <button type="button"
                                        class="h-2.5 w-2.5 rounded-full"
                                        :class="index === <?php echo e($i); ?> ? 'bg-white' : 'bg-white/60'"
                                        @click="goTo(<?php echo e($i); ?>)"
                                        aria-label="Go to slide <?php echo e($i + 1); ?>"></button>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="w-full min-h-[160px] bg-gray-100"></div>
                <?php endif; ?>
                </div>
            </div>

            <div class="mt-8">
                <h2 class="text-base font-semibold text-gray-900"><?php echo e(__('ui.featured_categories')); ?></h2>
                <div class="mt-3 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                    <?php $__empty_1 = true; $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <a href="<?php echo e(route('products.index', ['category' => $category->slug])); ?>" class="bg-white border border-gray-100 rounded-lg p-4 hover:border-gray-200">
                            <div class="flex items-center gap-3">
                                <?php if(!empty($category->icon)): ?>
                                    <img src="<?php echo e(Storage::url($category->icon)); ?>" alt="<?php echo e($category->name); ?>" class="h-8 w-8 rounded-md object-cover border border-gray-100" width="32" height="32" loading="lazy" decoding="async" />
                                <?php else: ?>
                                    <div class="h-8 w-8 rounded-md bg-gray-100 border border-gray-100"></div>
                                <?php endif; ?>
                                <div class="text-sm font-semibold text-gray-900"><?php echo e($category->name); ?></div>
                            </div>
                        </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="text-sm text-gray-500">No categories yet.</div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="mt-10">
                <div class="flex items-center justify-between">
                    <h2 class="text-base font-semibold text-gray-900"><?php echo e(__('ui.trending_products')); ?></h2>
                    <a href="<?php echo e(route('products.index')); ?>" class="text-sm font-medium text-indigo-600 hover:text-indigo-700"><?php echo e(__('ui.view_all')); ?></a>
                </div>
                <div class="mt-3 grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    <?php $__empty_1 = true; $__currentLoopData = $trendingProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="group bg-white border border-gray-100 rounded-xl overflow-hidden shadow-sm hover:shadow-md hover:border-gray-200 transition">
                            <a href="<?php echo e(route('products.show', $product)); ?>" class="block bg-gray-50" aria-label="View <?php echo e($product->title); ?>">
                                <?php if(!empty($product->image)): ?>
                                    <img src="<?php echo e(Storage::url($product->image)); ?>" alt="<?php echo e($product->title); ?>" class="aspect-square w-full object-cover bg-gray-100 transition-transform duration-300 group-hover:scale-[1.02]" width="320" height="320" loading="lazy" decoding="async" />
                                <?php else: ?>
                                    <div class="aspect-square bg-gray-100"></div>
                                <?php endif; ?>
                            </a>
                            <div class="p-3 sm:p-4">
                                <div class="text-xs text-gray-500"><?php echo e($product->category?->name ?? 'Uncategorized'); ?></div>
                                <a href="<?php echo e(route('products.show', $product)); ?>" class="mt-1 block text-sm font-semibold text-gray-900 hover:text-indigo-600"><?php echo e($product->title); ?></a>
                                <?php if(($reviewsEnabled ?? false) && (int) ($product->reviews_count ?? 0) > 0): ?>
                                    <div class="mt-1 inline-flex items-center gap-1 text-xs text-amber-600">
                                        <span>&#9733;</span>
                                        <span class="font-semibold"><?php echo e(number_format((float) $product->reviews_avg_rating, 1)); ?></span>
                                        <span class="text-gray-500">(<?php echo e((int) $product->reviews_count); ?>)</span>
                                    </div>
                                <?php endif; ?>
                                <?php
                                    $minV = $product->min_variant_price;
                                    $maxV = $product->max_variant_price;
                                    $minP = $minV !== null ? (float) $minV : (float) $product->price;
                                    $maxP = $maxV !== null ? (float) $maxV : (float) $product->price;
                                    $regP = $product->regular_price ? (float) $product->regular_price : null;
                                    $isRange = $minP !== $maxP;
                                    $cardOutOfStock = $product->stock !== null && $product->stock <= 0;
                                ?>

                                <div class="mt-2 flex items-center justify-between gap-2">
                                    <div>
                                        <?php if($regP && $regP > $minP && !$isRange): ?>
                                            <div class="text-xs text-gray-400 line-through leading-tight"><?php if (isset($component)) { $__componentOriginal3c51c5b308d311657bfae6be692a1470 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3c51c5b308d311657bfae6be692a1470 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.money','data' => ['amount' => $regP]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('money'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['amount' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($regP)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3c51c5b308d311657bfae6be692a1470)): ?>
<?php $attributes = $__attributesOriginal3c51c5b308d311657bfae6be692a1470; ?>
<?php unset($__attributesOriginal3c51c5b308d311657bfae6be692a1470); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3c51c5b308d311657bfae6be692a1470)): ?>
<?php $component = $__componentOriginal3c51c5b308d311657bfae6be692a1470; ?>
<?php unset($__componentOriginal3c51c5b308d311657bfae6be692a1470); ?>
<?php endif; ?></div>
                                        <?php endif; ?>
                                        <div class="text-sm font-semibold <?php echo e($cardOutOfStock ? 'text-gray-400' : 'text-red-600'); ?>">
                                            <?php if($isRange): ?>
                                                <?php if (isset($component)) { $__componentOriginal3c51c5b308d311657bfae6be692a1470 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3c51c5b308d311657bfae6be692a1470 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.money','data' => ['amount' => $minP]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('money'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['amount' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($minP)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3c51c5b308d311657bfae6be692a1470)): ?>
<?php $attributes = $__attributesOriginal3c51c5b308d311657bfae6be692a1470; ?>
<?php unset($__attributesOriginal3c51c5b308d311657bfae6be692a1470); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3c51c5b308d311657bfae6be692a1470)): ?>
<?php $component = $__componentOriginal3c51c5b308d311657bfae6be692a1470; ?>
<?php unset($__componentOriginal3c51c5b308d311657bfae6be692a1470); ?>
<?php endif; ?> - <?php if (isset($component)) { $__componentOriginal3c51c5b308d311657bfae6be692a1470 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3c51c5b308d311657bfae6be692a1470 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.money','data' => ['amount' => $maxP]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('money'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['amount' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($maxP)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3c51c5b308d311657bfae6be692a1470)): ?>
<?php $attributes = $__attributesOriginal3c51c5b308d311657bfae6be692a1470; ?>
<?php unset($__attributesOriginal3c51c5b308d311657bfae6be692a1470); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3c51c5b308d311657bfae6be692a1470)): ?>
<?php $component = $__componentOriginal3c51c5b308d311657bfae6be692a1470; ?>
<?php unset($__componentOriginal3c51c5b308d311657bfae6be692a1470); ?>
<?php endif; ?>
                                            <?php else: ?>
                                                <?php if (isset($component)) { $__componentOriginal3c51c5b308d311657bfae6be692a1470 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3c51c5b308d311657bfae6be692a1470 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.money','data' => ['amount' => $minP]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('money'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['amount' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($minP)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3c51c5b308d311657bfae6be692a1470)): ?>
<?php $attributes = $__attributesOriginal3c51c5b308d311657bfae6be692a1470; ?>
<?php unset($__attributesOriginal3c51c5b308d311657bfae6be692a1470); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3c51c5b308d311657bfae6be692a1470)): ?>
<?php $component = $__componentOriginal3c51c5b308d311657bfae6be692a1470; ?>
<?php unset($__componentOriginal3c51c5b308d311657bfae6be692a1470); ?>
<?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <?php if($cardOutOfStock): ?>
                                        <span class="shrink-0 rounded-full bg-red-50 px-2 py-0.5 text-[10px] font-semibold text-red-500 ring-1 ring-red-100">Out of Stock</span>
                                    <?php elseif($regP && $regP > $minP && !$isRange): ?>
                                        <?php ($pct = round((1 - $minP / $regP) * 100)); ?>
                                        <span class="shrink-0 rounded-full bg-green-50 px-2 py-0.5 text-[10px] font-semibold text-green-600 ring-1 ring-green-100">-<?php echo e($pct); ?>%</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="text-sm text-gray-500">No products yet.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald956570c5321d7185b887a45463f814f)): ?>
<?php $attributes = $__attributesOriginald956570c5321d7185b887a45463f814f; ?>
<?php unset($__attributesOriginald956570c5321d7185b887a45463f814f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald956570c5321d7185b887a45463f814f)): ?>
<?php $component = $__componentOriginald956570c5321d7185b887a45463f814f; ?>
<?php unset($__componentOriginald956570c5321d7185b887a45463f814f); ?>
<?php endif; ?>

<?php /**PATH C:\xampp\htdocs\digify\resources\views/pages/home.blade.php ENDPATH**/ ?>