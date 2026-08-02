<?php
    $autoTranslate = app(\App\Services\AutoTranslate::class);
    $locale = app()->getLocale();
    $enabled = (bool) ((int) \App\Models\Setting::getValue('whatsapp_widget_enabled', '1'));
    $whatsappNumberRaw = (string) \App\Models\Setting::getValue('whatsapp_number', '');
    $whatsappNumber = preg_replace('/\D+/', '', $whatsappNumberRaw ?? '');

    $themeColor = (string) \App\Models\Setting::getValue('whatsapp_widget_color', '#25D366');
    $right = (int) \App\Models\Setting::getValue('whatsapp_widget_right', '20');
    $bottomMobile = (int) \App\Models\Setting::getValue('whatsapp_widget_bottom_mobile', '96');
    $bottomDesktop = (int) \App\Models\Setting::getValue('whatsapp_widget_bottom_desktop', '24');

    $messageEn = (string) \App\Models\Setting::getValue('whatsapp_widget_message', '');
    if ($messageEn === '') {
        $messageEn = (string) __('ui.whatsapp_default_message');
    }
    $defaultMessage = $locale === 'en' ? $messageEn : $autoTranslate->translate($messageEn, $locale, false);
?>

<?php if($enabled && $whatsappNumber !== ''): ?>
    <div
        class="fixed z-50 flex flex-col items-end"
        data-wa-widget="1"
        style="--wa-right: <?php echo e($right); ?>px; --wa-bottom-mobile: <?php echo e($bottomMobile); ?>px; --wa-bottom-desktop: <?php echo e($bottomDesktop); ?>px;"
        x-data="{
            open: false,
            message: <?php echo \Illuminate\Support\Js::from($defaultMessage)->toHtml() ?>,
            wa: <?php echo \Illuminate\Support\Js::from($whatsappNumber)->toHtml() ?>,
            send() {
                const text = (this.message || '').trim();
                const url = `https://wa.me/${this.wa}?text=${encodeURIComponent(text)}`;
                window.open(url, '_blank', 'noopener,noreferrer');
            }
        }"
    >
        <div
            class="mb-3 w-80 max-w-[calc(100vw-2.5rem)] overflow-hidden rounded-2xl bg-white shadow-2xl ring-1 ring-black/5"
            x-show="open"
            x-cloak
            x-transition.origin.bottom.right
            @click.outside="open = false"
        >
            <div class="flex items-start justify-between gap-3 px-4 py-3" style="background-color: <?php echo e($themeColor); ?>;">
                <div class="flex items-start gap-3">
                    <div class="mt-0.5 inline-flex h-9 w-9 items-center justify-center rounded-full bg-white/15">
                        <svg viewBox="0 0 32 32" class="h-5 w-5 text-white" fill="currentColor" aria-hidden="true">
                            <path d="M19.11 17.56c-.28-.14-1.64-.81-1.89-.91-.25-.09-.44-.14-.62.14-.19.28-.71.91-.87 1.1-.16.19-.32.21-.6.07-.28-.14-1.18-.43-2.25-1.38-.83-.74-1.4-1.65-1.56-1.93-.16-.28-.02-.43.12-.57.12-.12.28-.32.41-.48.14-.16.19-.28.28-.46.09-.19.05-.35-.02-.5-.07-.14-.62-1.5-.85-2.05-.22-.53-.45-.46-.62-.47h-.53c-.19 0-.5.07-.76.35-.26.28-1 1-1 2.43s1.02 2.82 1.16 3.02c.14.19 2.01 3.06 4.86 4.29.68.29 1.21.47 1.62.6.68.22 1.3.19 1.79.12.55-.08 1.64-.67 1.87-1.31.23-.64.23-1.18.16-1.31-.07-.13-.25-.2-.53-.34z"/>
                            <path d="M16.02 3C8.83 3 3 8.82 3 16c0 2.52.74 4.98 2.13 7.09L3.73 29l6.06-1.36A12.93 12.93 0 0 0 16.02 29C23.2 29 29 23.18 29 16S23.2 3 16.02 3zm0 23.6c-2.1 0-4.15-.56-5.93-1.63l-.43-.26-3.6.8.82-3.5-.28-.45A10.65 10.65 0 0 1 5.38 16c0-5.88 4.78-10.66 10.64-10.66 5.86 0 10.64 4.78 10.64 10.66S21.88 26.6 16.02 26.6z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="text-sm font-semibold text-white"><?php echo e(__('ui.whatsapp_us')); ?></div>
                        <div class="text-xs text-white/90"><?php echo e(__('ui.typically_replies_fast')); ?></div>
                    </div>
                </div>

                <button type="button" class="mt-1 text-white/90 hover:text-white" @click="open = false" aria-label="Close">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M18 6L6 18" />
                        <path d="M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="p-4">
                <label class="block text-xs font-semibold text-gray-700"><?php echo e(__('ui.message')); ?></label>
                <textarea
                    class="mt-2 w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 placeholder:text-gray-400 focus:outline-none"
                    rows="4"
                    x-model="message"
                ></textarea>

                <button
                    type="button"
                    class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold text-white hover:brightness-95"
                    style="background-color: <?php echo e($themeColor); ?>;"
                    @click="send()"
                >
                    <svg viewBox="0 0 32 32" class="h-5 w-5" fill="currentColor" aria-hidden="true">
                        <path d="M19.11 17.56c-.28-.14-1.64-.81-1.89-.91-.25-.09-.44-.14-.62.14-.19.28-.71.91-.87 1.1-.16.19-.32.21-.6.07-.28-.14-1.18-.43-2.25-1.38-.83-.74-1.4-1.65-1.56-1.93-.16-.28-.02-.43.12-.57.12-.12.28-.32.41-.48.14-.16.19-.28.28-.46.09-.19.05-.35-.02-.5-.07-.14-.62-1.5-.85-2.05-.22-.53-.45-.46-.62-.47h-.53c-.19 0-.5.07-.76.35-.26.28-1 1-1 2.43s1.02 2.82 1.16 3.02c.14.19 2.01 3.06 4.86 4.29.68.29 1.21.47 1.62.6.68.22 1.3.19 1.79.12.55-.08 1.64-.67 1.87-1.31.23-.64.23-1.18.16-1.31-.07-.13-.25-.2-.53-.34z"/>
                        <path d="M16.02 3C8.83 3 3 8.82 3 16c0 2.52.74 4.98 2.13 7.09L3.73 29l6.06-1.36A12.93 12.93 0 0 0 16.02 29C23.2 29 29 23.18 29 16S23.2 3 16.02 3zm0 23.6c-2.1 0-4.15-.56-5.93-1.63l-.43-.26-3.6.8.82-3.5-.28-.45A10.65 10.65 0 0 1 5.38 16c0-5.88 4.78-10.66 10.64-10.66 5.86 0 10.64 4.78 10.64 10.66S21.88 26.6 16.02 26.6z"/>
                    </svg>
                    <span><?php echo e(__('ui.send_on_whatsapp')); ?></span>
                </button>

                <div class="mt-2 text-[11px] text-gray-400">
                    <?php echo e(__('ui.whatsapp_opens_new_tab')); ?>

                </div>
            </div>
        </div>

        <button
            type="button"
            data-wa-button="1"
            class="inline-flex h-14 w-14 items-center justify-center rounded-full p-0 text-white shadow-xl ring-1 ring-black/5 hover:brightness-95"
            style="background-color: <?php echo e($themeColor); ?>;"
            @click="open = !open"
            aria-label="WhatsApp"
        >
            <svg viewBox="0 0 32 32" class="h-7 w-7" fill="currentColor" aria-hidden="true">
                <path d="M19.11 17.56c-.28-.14-1.64-.81-1.89-.91-.25-.09-.44-.14-.62.14-.19.28-.71.91-.87 1.1-.16.19-.32.21-.6.07-.28-.14-1.18-.43-2.25-1.38-.83-.74-1.4-1.65-1.56-1.93-.16-.28-.02-.43.12-.57.12-.12.28-.32.41-.48.14-.16.19-.28.28-.46.09-.19.05-.35-.02-.5-.07-.14-.62-1.5-.85-2.05-.22-.53-.45-.46-.62-.47h-.53c-.19 0-.5.07-.76.35-.26.28-1 1-1 2.43s1.02 2.82 1.16 3.02c.14.19 2.01 3.06 4.86 4.29.68.29 1.21.47 1.62.6.68.22 1.3.19 1.79.12.55-.08 1.64-.67 1.87-1.31.23-.64.23-1.18.16-1.31-.07-.13-.25-.2-.53-.34z"/>
                <path d="M16.02 3C8.83 3 3 8.82 3 16c0 2.52.74 4.98 2.13 7.09L3.73 29l6.06-1.36A12.93 12.93 0 0 0 16.02 29C23.2 29 29 23.18 29 16S23.2 3 16.02 3zm0 23.6c-2.1 0-4.15-.56-5.93-1.63l-.43-.26-3.6.8.82-3.5-.28-.45A10.65 10.65 0 0 1 5.38 16c0-5.88 4.78-10.66 10.64-10.66 5.86 0 10.64 4.78 10.64 10.66S21.88 26.6 16.02 26.6z"/>
            </svg>
        </button>
    </div>

    <style>
        [data-wa-widget="1"] {
            right: var(--wa-right);
            bottom: var(--wa-bottom-mobile);
        }

        [data-wa-button="1"] {
            width: 56px;
            height: 56px;
            padding: 0;
            border-radius: 9999px;
            flex: none;
            overflow: hidden;
        }

        [data-wa-widget="1"] textarea:focus {
            border-color: <?php echo e($themeColor); ?>;
            box-shadow: 0 0 0 2px <?php echo e($themeColor); ?>;
        }

        @media (min-width: 640px) {
            [data-wa-widget="1"] { bottom: var(--wa-bottom-desktop); }
        }
    </style>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\digify\resources\views/layouts/partials/whatsapp-widget.blade.php ENDPATH**/ ?>