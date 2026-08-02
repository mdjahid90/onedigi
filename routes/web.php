<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\AnalyticsController as AdminAnalyticsController;
use App\Http\Controllers\Admin\NotificationController as AdminNotificationController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\SettingController as AdminSettingController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\Marketing\BannerController as AdminBannerController;
use App\Http\Controllers\Admin\Marketing\SeoController as AdminSeoController;
use App\Http\Controllers\Admin\Marketing\SitemapController as AdminSitemapController;
use App\Http\Controllers\Admin\Marketing\VerificationController as AdminVerificationController;
use App\Http\Controllers\Admin\EmailTemplateController as AdminEmailTemplateController;
use App\Http\Controllers\Admin\BrandingController as AdminBrandingController;
use App\Http\Controllers\Admin\RefundPolicyController as AdminRefundPolicyController;
use App\Http\Controllers\Admin\ProductReviewController as AdminProductReviewController;
use App\Http\Controllers\Admin\PaymentGatewayController as AdminPaymentGatewayController;
use App\Http\Controllers\Admin\CurrencyController as AdminCurrencyController;
use App\Http\Controllers\Admin\WhatsAppWidgetController as AdminWhatsAppWidgetController;
use App\Http\Controllers\Admin\FooterController as AdminFooterController;
use App\Http\Controllers\Admin\PageController as AdminPageController;
use App\Http\Controllers\Admin\ContactMessageController as AdminContactMessageController;
use App\Http\Controllers\Admin\TicketController as AdminTicketController;
use App\Http\Controllers\Admin\TransactionController as AdminTransactionController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DeliveryDownloadController;
use App\Http\Controllers\DownloadCenterController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductReviewController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\LicenseController;
use App\Http\Controllers\RefundRequestController;
use App\Http\Controllers\RefundPolicyController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\UserNotificationController;
use App\Http\Controllers\UserDashboardController;
use App\Http\Controllers\Admin\RefundRequestController as AdminRefundRequestController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Cookie;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', HomeController::class)->name('home');
Route::get('/sitemap.xml', [SitemapController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [SitemapController::class, 'robots'])->name('robots');

Route::get('/lang/{locale}', function (string $locale) {
    $supported = ['en', 'bn', 'ru'];
    if (!in_array($locale, $supported, true)) {
        $locale = 'en';
    }

    session()->put('locale', $locale);
    Cookie::queue('locale', $locale, 60 * 24 * 365);

    return redirect()->back();
})->name('lang.switch');

Route::get('/currency/{currency}', function (string $currency) {
    $supported = ['BDT', 'USD', 'RUB'];
    $currency = strtoupper($currency);
    if (!in_array($currency, $supported, true)) {
        $currency = 'BDT';
    }

    session()->put('currency', $currency);
    Cookie::queue('currency', $currency, 60 * 24 * 365);

    return redirect()->back();
})->name('currency.switch');

Route::get('/categories', [CategoryController::class, 'index'])->name('categories');

Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/search/suggest', [ProductController::class, 'suggest'])->name('products.suggest');
Route::get('/products/{product:slug}', [ProductController::class, 'show'])->name('products.show');
Route::post('/products/{product:slug}/reviews', [ProductReviewController::class, 'store'])
    ->middleware('throttle:15,1')
    ->name('products.reviews.store');

Route::get('/cart', [CartController::class, 'show'])->name('cart');
Route::post('/cart/{product}/add', [CartController::class, 'add'])->name('cart.add');
Route::post('/cart/{product}/update', [CartController::class, 'update'])->name('cart.update');
Route::post('/cart/{product}/remove', [CartController::class, 'remove'])->name('cart.remove');
Route::post('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');
Route::post('/buy-now/{product}', [CartController::class, 'buyNow'])->name('buy_now');

Route::get('/checkout', [CheckoutController::class, 'create'])->name('checkout');
Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');

Route::match(['get', 'post'], '/payment/uddoktapay/success', [CheckoutController::class, 'uddoktaPaySuccess'])->name('uddoktapay.success');
Route::get('/payment/uddoktapay/cancel', [CheckoutController::class, 'uddoktaPayCancel'])->name('uddoktapay.cancel');
Route::get('/payment/uddoktapay/failed', [CheckoutController::class, 'uddoktaPayFailed'])->name('uddoktapay.failed');
Route::post('/payment/uddoktapay/webhook', [CheckoutController::class, 'uddoktaPayWebhook'])->name('uddoktapay.webhook');
Route::match(['get', 'post'], '/payment/piprapay/success', [CheckoutController::class, 'pipraPaySuccess'])->name('piprapay.success');
Route::get('/payment/piprapay/cancel', [CheckoutController::class, 'pipraPayCancel'])->name('piprapay.cancel');
Route::post('/payment/piprapay/webhook', [CheckoutController::class, 'pipraPayWebhook'])->name('piprapay.webhook');

Route::get('/orders', [OrderController::class, 'index'])->middleware('auth')->name('orders');
Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
Route::post('/orders/{order}/retry-payment', [CheckoutController::class, 'retryOrderPayment'])->name('orders.retry_payment');
Route::get('/orders/{order}/delivery/download', DeliveryDownloadController::class)
    ->name('orders.delivery.download');
Route::get('/downloads', DownloadCenterController::class)->middleware('auth')->name('downloads.index');
Route::middleware('auth')->group(function () {
    Route::get('/tickets', [TicketController::class, 'index'])->name('tickets.index');
    Route::post('/tickets', [TicketController::class, 'store'])->name('tickets.store');
    Route::get('/tickets/{ticket}', [TicketController::class, 'show'])->name('tickets.show');
    Route::post('/tickets/{ticket}/reply', [TicketController::class, 'reply'])->name('tickets.reply');
    Route::post('/tickets/{ticket}/close', [TicketController::class, 'close'])->name('tickets.close');
});

Route::prefix('page')->name('page.')->group(function () {
    Route::get('/privacy-policy', [PageController::class, 'show'])->defaults('page', 'privacy-policy')->name('privacy');

    Route::get('/terms-conditions', [PageController::class, 'show'])->defaults('page', 'terms-conditions')->name('terms');

    Route::get('/aml-policy', [PageController::class, 'show'])->defaults('page', 'aml-policy')->name('aml');

    Route::get('/faq', [PageController::class, 'show'])->defaults('page', 'faq')->name('faq');

    Route::get('/api', [PageController::class, 'show'])->defaults('page', 'api')->name('api');

    Route::get('/contact', [ContactController::class, 'create'])->name('contact');
    Route::post('/contact', [ContactController::class, 'store'])->name('contact.submit');

    Route::get('/refund-policy', [PageController::class, 'show'])->defaults('page', 'refund-policy')->name('refund-policy');

    Route::get('/{page}', [PageController::class, 'show'])->name('show');
});

Route::get('/dashboard', UserDashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/notifications', [UserNotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/read-all', [UserNotificationController::class, 'readAll'])->name('notifications.read_all');
    Route::get('/notifications/{notification}/open', [UserNotificationController::class, 'open'])->name('notifications.open');

    Route::get('/subscriptions', SubscriptionController::class)->name('subscriptions.index');
    Route::get('/licenses', LicenseController::class)->name('licenses.index');
    Route::get('/refunds', [RefundRequestController::class, 'index'])->name('refunds.index');
    Route::post('/refunds', [RefundRequestController::class, 'store'])->name('refunds.store');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::get('/analytics/realtime', [DashboardController::class, 'realtime'])->name('analytics.realtime');
    Route::get('/analytics', AdminAnalyticsController::class)->name('analytics.index');
    Route::get('/notifications', [AdminNotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/read-all', [AdminNotificationController::class, 'readAll'])->name('notifications.read_all');
    Route::get('/notifications/{notification}/open', [AdminNotificationController::class, 'open'])->name('notifications.open');

    Route::get('/messages', [AdminContactMessageController::class, 'index'])->name('messages.index');
    Route::get('/messages/{message}', [AdminContactMessageController::class, 'show'])->name('messages.show');
    Route::post('/messages/{message}/read', [AdminContactMessageController::class, 'markRead'])->name('messages.read');
    Route::post('/messages/{message}/unread', [AdminContactMessageController::class, 'markUnread'])->name('messages.unread');
    Route::delete('/messages/{message}', [AdminContactMessageController::class, 'destroy'])->name('messages.destroy');
    Route::get('/tickets', [AdminTicketController::class, 'index'])->name('tickets.index');
    Route::get('/tickets/{ticket}', [AdminTicketController::class, 'show'])->name('tickets.show');
    Route::post('/tickets/{ticket}/reply', [AdminTicketController::class, 'reply'])->name('tickets.reply');
    Route::post('/tickets/{ticket}/status', [AdminTicketController::class, 'updateStatus'])->name('tickets.status');

    Route::resource('products', AdminProductController::class)->except(['show']);
    Route::resource('categories', AdminCategoryController::class)->except(['show']);
    Route::resource('users', AdminUserController::class)->except(['show']);
    Route::resource('pages', AdminPageController::class)->except(['show']);

    Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
    Route::get('/orders/{order}/edit', [AdminOrderController::class, 'edit'])->name('orders.edit');
    Route::put('/orders/{order}', [AdminOrderController::class, 'update'])->name('orders.update');

    Route::get('/transactions', [AdminTransactionController::class, 'index'])->name('transactions.index');
    Route::get('/refund-requests', [AdminRefundRequestController::class, 'index'])->name('refund_requests.index');
    Route::patch('/refund-requests/{refundRequest}', [AdminRefundRequestController::class, 'update'])->name('refund_requests.update');

    Route::get('/settings', [AdminSettingController::class, 'edit'])->name('settings');
    Route::post('/settings', [AdminSettingController::class, 'update'])->name('settings.update');

    Route::get('/reviews', [AdminProductReviewController::class, 'index'])->name('reviews.index');
    Route::post('/reviews/{review}/approve', [AdminProductReviewController::class, 'approve'])->name('reviews.approve');
    Route::post('/reviews/{review}/reject', [AdminProductReviewController::class, 'reject'])->name('reviews.reject');

    Route::get('/branding', [AdminBrandingController::class, 'index'])->name('branding.index');
    Route::post('/branding', [AdminBrandingController::class, 'update'])->name('branding.update');

    Route::get('/refund-policy', [AdminRefundPolicyController::class, 'edit'])->name('refund-policy.edit');
    Route::post('/refund-policy', [AdminRefundPolicyController::class, 'update'])->name('refund-policy.update');

    Route::get('/footer', [AdminFooterController::class, 'edit'])->name('footer.edit');
    Route::post('/footer', [AdminFooterController::class, 'update'])->name('footer.update');

    Route::get('/currency', [AdminCurrencyController::class, 'edit'])->name('currency.edit');
    Route::post('/currency', [AdminCurrencyController::class, 'update'])->name('currency.update');

    Route::get('/whatsapp-widget', [AdminWhatsAppWidgetController::class, 'edit'])->name('whatsapp-widget.edit');
    Route::post('/whatsapp-widget', [AdminWhatsAppWidgetController::class, 'update'])->name('whatsapp-widget.update');

    Route::prefix('marketing')->name('marketing.')->group(function () {
        Route::resource('banners', AdminBannerController::class)->except(['show']);

        Route::get('/seo', [AdminSeoController::class, 'edit'])->name('seo');
        Route::post('/seo', [AdminSeoController::class, 'update'])->name('seo.update');

        Route::get('/sitemap', [AdminSitemapController::class, 'index'])->name('sitemap');
        Route::post('/sitemap/generate', [AdminSitemapController::class, 'generate'])->name('sitemap.generate');

        Route::get('/verification', [AdminVerificationController::class, 'edit'])->name('verification');
        Route::post('/verification', [AdminVerificationController::class, 'update'])->name('verification.update');
    });

    Route::get('/email-templates', [AdminEmailTemplateController::class, 'index'])->name('email_templates.index');
    Route::get('/email-templates/create', [AdminEmailTemplateController::class, 'create'])->name('email_templates.create');
    Route::post('/email-templates', [AdminEmailTemplateController::class, 'store'])->name('email_templates.store');
    Route::get('/email-templates/{template}/edit', [AdminEmailTemplateController::class, 'edit'])->name('email_templates.edit');
    Route::put('/email-templates/{template}', [AdminEmailTemplateController::class, 'update'])->name('email_templates.update');
    Route::delete('/email-templates/{template}', [AdminEmailTemplateController::class, 'destroy'])->name('email_templates.destroy');

    Route::resource('gateways', AdminPaymentGatewayController::class)->only(['index', 'edit', 'update']);
});

require __DIR__.'/auth.php';
