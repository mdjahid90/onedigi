@php
    $status = session('status');
    $toasts = [];

    $pushToast = static function (string $type, string $title, mixed $value) use (&$toasts): void {
        $messages = is_array($value) ? $value : [$value];
        foreach ($messages as $message) {
            $messageString = trim((string) $message);
            if ($messageString === '') {
                continue;
            }
            $toasts[] = [
                'type' => $type,
                'title' => $title,
                'message' => $messageString,
            ];
        }
    };

    $pushToast('success', 'Success', session('success'));
    $pushToast('error', 'Error', session('error'));
    $pushToast('warning', 'Warning', session('warning'));
    $pushToast('info', 'Info', session('info'));

    $statusMap = [
        'profile-updated' => 'Profile updated successfully.',
        'password-updated' => 'Password updated successfully.',
        'verification-link-sent' => 'Verification link sent successfully.',
    ];

    if ($status !== null) {
        $statusMessage = $statusMap[(string) $status] ?? $status;
        $pushToast('info', 'Info', $statusMessage);
    }

    if ($errors->any()) {
        foreach ($errors->all() as $errorMessage) {
            $pushToast('error', 'Validation Error', $errorMessage);
        }
    }
@endphp

<style>
    :root {
        --toast-width: min(445px, calc(100vw - 24px));
        --toast-bg: #ffffff;
        --toast-text: #333333;
        --success-color: #2ecc71;
        --error-color: #e74c3c;
        --warning-color: #f1c40f;
        --info-color: #3498db;
        --toast-shadow: 0 8px 20px rgba(15, 23, 42, 0.12);
    }

    #toast-container {
        position: fixed;
        top: 72px;
        right: 12px;
        display: flex;
        flex-direction: column;
        gap: 8px;
        z-index: 9999;
        pointer-events: none;
    }

    .digify-toast {
        width: var(--toast-width);
        background: var(--toast-bg);
        border-radius: 10px;
        box-shadow: var(--toast-shadow);
        display: flex;
        align-items: flex-start;
        padding: 13px 16px 12px;
        position: relative;
        overflow: hidden;
        pointer-events: auto;
        animation: slideInRight 0.4s ease forwards;
        border-left: 5px solid;
        backdrop-filter: blur(10px);
    }

    .digify-toast.success { border-color: var(--success-color); }
    .digify-toast.error { border-color: var(--error-color); }
    .digify-toast.warning { border-color: var(--warning-color); }
    .digify-toast.info { border-color: var(--info-color); }

    .digify-toast-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 12px;
        margin-top: 1px;
        flex-shrink: 0;
    }

    .digify-toast-icon svg {
        width: 24px;
        height: 24px;
    }

    .digify-toast.success .digify-toast-icon { color: var(--success-color); }
    .digify-toast.error .digify-toast-icon { color: var(--error-color); }
    .digify-toast.warning .digify-toast-icon { color: var(--warning-color); }
    .digify-toast.info .digify-toast-icon { color: var(--info-color); }

    .digify-toast-content {
        flex: 1;
        min-width: 0;
        padding-right: 28px;
    }

    .digify-toast-title {
        font-weight: 700;
        margin-bottom: 3px;
        color: #111;
        font-size: 16px;
        line-height: 1.2;
    }

    .digify-toast-message {
        font-size: 14px;
        color: #666;
        margin: 0;
        line-height: 1.35;
    }

    .digify-toast-close {
        position: absolute;
        top: 10px;
        right: 12px;
        background: transparent;
        border: none;
        cursor: pointer;
        font-size: 28px;
        color: #aaa;
        padding: 0;
        margin: 0;
        line-height: 1;
        min-width: auto;
        width: 18px;
        height: 18px;
        flex: 0 0 auto;
        transition: color 0.3s;
    }

    .digify-toast-close:hover { color: #333; }

    .digify-toast-progress-track {
        position: absolute;
        bottom: 0;
        left: 0;
        height: 4px;
        width: 100%;
        background: rgba(0, 0, 0, 0.05);
    }

    .digify-toast-progress-bar {
        height: 100%;
        width: 100%;
    }

    .digify-toast.success .digify-toast-progress-bar { background-color: var(--success-color); }
    .digify-toast.error .digify-toast-progress-bar { background-color: var(--error-color); }
    .digify-toast.warning .digify-toast-progress-bar { background-color: var(--warning-color); }
    .digify-toast.info .digify-toast-progress-bar { background-color: var(--info-color); }

    @keyframes slideInRight {
        from { transform: translateX(110%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }

    @keyframes slideInTop {
        from { transform: translateY(-100%); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }

    @keyframes fadeOut {
        from { opacity: 1; transform: scale(1); }
        to { opacity: 0; transform: scale(0.9); }
    }

    @keyframes timer {
        from { width: 100%; }
        to { width: 0%; }
    }

    .digify-toast:hover .digify-toast-progress-bar {
        animation-play-state: paused;
    }

    @media screen and (max-width: 480px) {
        #toast-container {
            right: 0;
            left: 0;
            top: 62px;
            align-items: center;
            width: 100%;
            padding: 0 12px;
        }

        .digify-toast {
            animation: slideInTop 0.4s ease forwards;
            width: 100%;
            max-width: 94vw;
        }
    }

    .digify-confirm-backdrop {
        position: fixed;
        inset: 0;
        z-index: 10000;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 18px;
        background: rgba(15, 23, 42, 0.48);
        backdrop-filter: blur(6px);
    }

    .digify-confirm-backdrop.is-open {
        display: flex;
    }

    .digify-confirm-dialog {
        width: min(430px, 100%);
        border: 1px solid rgba(226, 232, 240, 0.9);
        border-radius: 18px;
        background: #ffffff;
        box-shadow: 0 28px 70px rgba(15, 23, 42, 0.28);
        overflow: hidden;
        animation: digifyConfirmIn .18s ease-out;
    }

    .digify-confirm-body {
        padding: 22px;
    }

    .digify-confirm-icon {
        display: inline-flex;
        width: 44px;
        height: 44px;
        align-items: center;
        justify-content: center;
        border-radius: 14px;
        background: #fff1f2;
        color: #e11d48;
        margin-bottom: 14px;
    }

    .digify-confirm-icon svg {
        width: 23px;
        height: 23px;
    }

    .digify-confirm-title {
        margin: 0;
        color: #0f172a;
        font-size: 19px;
        font-weight: 800;
        line-height: 1.25;
    }

    .digify-confirm-message {
        margin: 8px 0 0;
        color: #64748b;
        font-size: 14px;
        line-height: 1.55;
    }

    .digify-confirm-actions {
        display: flex;
        gap: 10px;
        justify-content: flex-end;
        padding: 14px 18px 18px;
        background: #f8fafc;
    }

    .digify-confirm-btn {
        min-height: 40px;
        border: 1px solid transparent;
        border-radius: 11px;
        padding: 0 16px;
        font-size: 14px;
        font-weight: 700;
        transition: transform .16s ease, box-shadow .16s ease, background .16s ease;
    }

    .digify-confirm-btn:hover {
        transform: translateY(-1px);
    }

    .digify-confirm-cancel {
        border-color: #dbe3ef;
        background: #ffffff;
        color: #334155;
    }

    .digify-confirm-submit {
        background: #dc2626;
        color: #ffffff;
        box-shadow: 0 10px 22px rgba(220, 38, 38, 0.22);
    }

    @keyframes digifyConfirmIn {
        from { opacity: 0; transform: translateY(10px) scale(.98); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }
</style>

<div id="toast-container"></div>
<div id="digify-confirm" class="digify-confirm-backdrop" aria-hidden="true">
    <div class="digify-confirm-dialog" role="dialog" aria-modal="true" aria-labelledby="digify-confirm-title">
        <div class="digify-confirm-body">
            <div class="digify-confirm-icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v4m0 4h.01M10.3 4.3 2.8 17.3A2 2 0 0 0 4.5 20h15a2 2 0 0 0 1.7-2.7L13.7 4.3a2 2 0 0 0-3.4 0Z" />
                </svg>
            </div>
            <h3 id="digify-confirm-title" class="digify-confirm-title">Are you sure?</h3>
            <p id="digify-confirm-message" class="digify-confirm-message">This action cannot be undone.</p>
        </div>
        <div class="digify-confirm-actions">
            <button type="button" class="digify-confirm-btn digify-confirm-cancel" data-digify-confirm-cancel>Cancel</button>
            <button type="button" class="digify-confirm-btn digify-confirm-submit" data-digify-confirm-submit>Confirm</button>
        </div>
    </div>
</div>

<script>
    (function () {
        if (window.__digifyToastInit) {
            return;
        }
        window.__digifyToastInit = true;

        const icons = {
            success: '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>',
            error: '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>',
            warning: '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>',
            info: '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>'
        };
        const MAX_VISIBLE_TOASTS = 5;

        function getContainer() {
            return document.getElementById('toast-container');
        }

        function removeToast(toast) {
            if (!toast || !toast.parentElement) {
                return;
            }
            toast.style.animation = 'fadeOut 0.4s ease forwards';
            toast.addEventListener('animationend', function () {
                toast.remove();
            }, { once: true });
        }

        function createToast(type, title, message, duration) {
            const container = getContainer();
            if (!container) {
                return;
            }

            const toastType = (type && icons[type]) ? type : 'info';
            const timeout = Number.isFinite(duration) ? duration : 5000;
            const safeTitle = String(title || 'Notification')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
            const safeMessage = String(message || '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');

            const toast = document.createElement('div');
            toast.classList.add('digify-toast', toastType);
            toast.innerHTML = ''
                + '<div class="digify-toast-icon">' + icons[toastType] + '</div>'
                + '<div class="digify-toast-content">'
                + '<div class="digify-toast-title">' + safeTitle + '</div>'
                + '<div class="digify-toast-message">' + safeMessage + '</div>'
                + '</div>'
                + '<button class="digify-toast-close" type="button" aria-label="Close">&times;</button>'
                + '<div class="digify-toast-progress-track"><div class="digify-toast-progress-bar" style="animation: timer ' + timeout + 'ms linear forwards;"></div></div>';

            while (container.children.length >= MAX_VISIBLE_TOASTS) {
                const oldest = container.firstElementChild;
                if (!oldest) {
                    break;
                }
                removeToast(oldest);
            }

            let removeTimer = window.setTimeout(function () {
                removeToast(toast);
            }, timeout);

            toast.querySelector('.digify-toast-close')?.addEventListener('click', function () {
                window.clearTimeout(removeTimer);
                removeToast(toast);
            });

            toast.addEventListener('mouseenter', function () {
                window.clearTimeout(removeTimer);
                const bar = toast.querySelector('.digify-toast-progress-bar');
                if (bar) {
                    bar.style.animationPlayState = 'paused';
                }
            });

            toast.addEventListener('mouseleave', function () {
                const bar = toast.querySelector('.digify-toast-progress-bar');
                if (bar) {
                    bar.style.animationPlayState = 'running';
                }
                removeTimer = window.setTimeout(function () {
                    removeToast(toast);
                }, timeout);
            });

            container.appendChild(toast);
        }

        window.createToast = createToast;
        window.removeToast = removeToast;

        const initialToasts = @json($toasts);
        if (Array.isArray(initialToasts) && initialToasts.length > 0) {
            initialToasts.forEach(function (toast, index) {
                window.setTimeout(function () {
                    createToast(toast.type, toast.title, toast.message);
                }, index * 120);
            });
        }

        const confirmModal = document.getElementById('digify-confirm');
        const confirmTitle = document.getElementById('digify-confirm-title');
        const confirmMessage = document.getElementById('digify-confirm-message');
        const confirmSubmit = confirmModal?.querySelector('[data-digify-confirm-submit]');
        const confirmCancel = confirmModal?.querySelector('[data-digify-confirm-cancel]');
        const confirmedForms = new WeakSet();
        let confirmResolver = null;

        function showConfirm(options) {
            if (!confirmModal || !confirmSubmit || !confirmCancel) {
                return Promise.resolve(window.confirm(options?.message || 'Are you sure?'));
            }

            if (confirmTitle) {
                confirmTitle.textContent = options?.title || 'Are you sure?';
            }
            if (confirmMessage) {
                confirmMessage.textContent = options?.message || 'This action cannot be undone.';
            }
            confirmSubmit.textContent = options?.confirmText || 'Confirm';
            confirmModal.classList.add('is-open');
            confirmModal.setAttribute('aria-hidden', 'false');
            document.body.classList.add('overflow-hidden');
            confirmSubmit.focus();

            return new Promise(function (resolve) {
                confirmResolver = resolve;
            });
        }

        window.digifyConfirm = showConfirm;

        function closeConfirm(result) {
            if (!confirmModal) {
                return;
            }
            confirmModal.classList.remove('is-open');
            confirmModal.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('overflow-hidden');
            if (confirmResolver) {
                confirmResolver(Boolean(result));
                confirmResolver = null;
            }
        }

        function confirmTextFromInline(value) {
            const text = String(value || '');
            const match = text.match(/confirm\((['"])(.*?)\1\)/);
            return match ? match[2] : '';
        }

        function formUsesDelete(form) {
            const methodInput = form?.querySelector('input[name="_method"]');
            return methodInput && String(methodInput.value || '').toUpperCase() === 'DELETE';
        }

        function needsConfirmation(trigger, form) {
            if (!trigger && !form) {
                return false;
            }
            if (trigger?.closest?.('#digify-confirm')) {
                return false;
            }
            if (trigger?.dataset?.confirm || form?.dataset?.confirm) {
                return true;
            }
            if (confirmTextFromInline(trigger?.getAttribute?.('onclick')) || confirmTextFromInline(form?.getAttribute?.('onsubmit'))) {
                return true;
            }
            if (trigger?.id === 'btn-delete-product') {
                return true;
            }
            if (formUsesDelete(form)) {
                return true;
            }
            return false;
        }

        function confirmationOptions(trigger, form) {
            const message = trigger?.dataset?.confirm
                || form?.dataset?.confirm
                || confirmTextFromInline(trigger?.getAttribute?.('onclick'))
                || confirmTextFromInline(form?.getAttribute?.('onsubmit'))
                || (formUsesDelete(form) ? 'Delete this item? This action cannot be undone.' : 'Are you sure you want to continue?');

            const destructive = formUsesDelete(form) || /delete|remove|destroy/i.test(message);
            return {
                title: destructive ? 'Confirm delete' : 'Confirm action',
                message,
                confirmText: destructive ? 'Delete' : 'Continue',
            };
        }

        function addSubmitterValue(form, submitter) {
            if (!form || !submitter?.name) {
                return null;
            }
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = submitter.name;
            input.value = submitter.value || '';
            form.appendChild(input);
            return input;
        }

        function disableInlineConfirm(form, trigger) {
            if (form && confirmTextFromInline(form.getAttribute('onsubmit'))) {
                form.removeAttribute('onsubmit');
            }
            if (trigger && confirmTextFromInline(trigger.getAttribute?.('onclick'))) {
                trigger.removeAttribute('onclick');
            }
        }

        confirmSubmit?.addEventListener('click', function () { closeConfirm(true); });
        confirmCancel?.addEventListener('click', function () { closeConfirm(false); });
        confirmModal?.addEventListener('click', function (event) {
            if (event.target === confirmModal) {
                closeConfirm(false);
            }
        });
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && confirmModal?.classList.contains('is-open')) {
                closeConfirm(false);
            }
        });

        document.addEventListener('click', function (event) {
            const trigger = event.target?.closest?.('a, button, input[type="submit"], input[type="button"]');
            if (!trigger || trigger.closest('#digify-confirm')) {
                return;
            }

            const form = trigger.form || trigger.closest('form');
            const isSubmitControl = trigger.matches('button, input[type="submit"], input[type="button"]') && form;
            const isLink = trigger.matches('a');

            if (!needsConfirmation(trigger, form)) {
                return;
            }

            event.preventDefault();
            event.stopImmediatePropagation();

            showConfirm(confirmationOptions(trigger, form)).then(function (confirmed) {
                if (!confirmed) {
                    return;
                }

                if (isLink && trigger.href) {
                    window.location.href = trigger.href;
                    return;
                }

                if (trigger.id === 'btn-delete-product') {
                    document.getElementById('form-delete-product')?.submit();
                    return;
                }

                if (isSubmitControl && form) {
                    confirmedForms.add(form);
                    disableInlineConfirm(form, trigger);
                    const temp = addSubmitterValue(form, trigger);
                    if (typeof form.requestSubmit === 'function' && trigger.type !== 'button') {
                        form.requestSubmit(trigger);
                    } else {
                        form.submit();
                    }
                    temp?.remove();
                }
            });
        }, true);

        document.addEventListener('submit', function (event) {
            const form = event.target;
            if (!(form instanceof HTMLFormElement) || confirmedForms.has(form) || !needsConfirmation(null, form)) {
                return;
            }

            event.preventDefault();
            event.stopImmediatePropagation();

            showConfirm(confirmationOptions(null, form)).then(function (confirmed) {
                if (!confirmed) {
                    return;
                }
                confirmedForms.add(form);
                disableInlineConfirm(form, null);
                if (typeof form.requestSubmit === 'function') {
                    form.requestSubmit();
                } else {
                    form.submit();
                }
            });
        }, true);
    })();
</script>
