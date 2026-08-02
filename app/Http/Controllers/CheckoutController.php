<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentGateway;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use App\Services\AdminNotificationService;
use App\Services\AnalyticsService;
use App\Services\CurrencyService;
use App\Services\EmailTemplateRenderer;
use App\Services\PaymentGatewayService;
use App\Services\UserNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function create(Request $request): View|RedirectResponse
    {
        $cart = $request->session()->get('cart', []);
        if (!is_array($cart) || count($cart) === 0) {
            return redirect()->route('cart');
        }

        $productIds = $this->productIdsFromCart($cart);

        $products = Product::query()
            ->whereIn('id', $productIds)
            ->where('is_active', true)
            ->get()
            ->keyBy('id');

        $items = [];
        $total = 0;

        foreach ($cart as $cartKey => $row) {
            $productId = is_array($row) ? (int) ($row['product_id'] ?? $cartKey) : (int) $cartKey;
            $product = $products->get($productId);
            if (!$product) {
                continue;
            }

            $quantity = 1;
            $unitPrice = (float) $product->price;
            $variantId = null;
            $meta = null;

            if (is_array($row)) {
                $quantity = max(1, (int) ($row['quantity'] ?? 1));
                $unitPrice = (float) ($row['unit_price'] ?? $product->price);
                $variantId = isset($row['variant_id']) ? (int) $row['variant_id'] : null;
                $meta = $row['meta'] ?? null;
            } else {
                $quantity = max(1, (int) $row);
            }

            $subtotal = (float) $unitPrice * $quantity;
            $total += $subtotal;

            $items[] = [
                'product' => $product,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'subtotal' => $subtotal,
                'variant_id' => $variantId,
                'meta' => $meta,
            ];
        }

        if (count($items) === 0) {
            return redirect()->route('cart');
        }

        return view('pages.checkout', [
            'items' => $items,
            'total' => $total,
            'activeGateways' => PaymentGatewayService::activeGateways(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $cart = $request->session()->get('cart', []);
        if (!is_array($cart) || count($cart) === 0) {
            return redirect()->route('cart');
        }

        $isGuestCheckout = !$request->user();

        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'country' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'payment_gateway' => ['nullable', 'string', 'max:80'],
            'password' => $isGuestCheckout
                ? ['required', 'confirmed', Password::min(8)->letters()->numbers()]
                : ['nullable', 'confirmed', Password::min(8)->letters()->numbers()],
        ]);

        $activeGateways = PaymentGatewayService::activeGateways();
        if ($activeGateways->isEmpty()) {
            return back()
                ->withErrors(['payment_gateway' => 'No active payment gateway is configured.'])
                ->withInput($request->except(['password', 'password_confirmation']));
        }

        $selectedGatewayCode = $activeGateways->count() === 1
            ? (string) $activeGateways->first()->code
            : (string) ($validated['payment_gateway'] ?? '');

        $paymentGateway = PaymentGatewayService::activeByCode($selectedGatewayCode);
        if (!$paymentGateway) {
            return back()
                ->withErrors(['payment_gateway' => 'Select a valid payment method.'])
                ->withInput($request->except(['password', 'password_confirmation']));
        }

        $productIds = $this->productIdsFromCart($cart);

        $products = Product::query()
            ->whereIn('id', $productIds)
            ->where('is_active', true)
            ->get()
            ->keyBy('id');

        $itemsPayload = [];
        $total = 0;

        foreach ($cart as $cartKey => $row) {
            $productId = is_array($row) ? (int) ($row['product_id'] ?? $cartKey) : (int) $cartKey;
            $product = $products->get($productId);
            if (!$product) {
                continue;
            }

            $quantity = 1;
            $unitPrice = (float) $product->price;
            $variantId = null;
            $meta = null;

            if (is_array($row)) {
                $quantity = max(1, (int) ($row['quantity'] ?? 1));
                $unitPrice = (float) ($row['unit_price'] ?? $product->price);
                $variantId = isset($row['variant_id']) ? (int) $row['variant_id'] : null;
                $meta = $row['meta'] ?? null;
            } else {
                $quantity = max(1, (int) $row);
            }

            $subtotal = (float) $unitPrice * $quantity;
            $total += $subtotal;

            $itemsPayload[] = [
                'product_id' => $product->id,
                'variant_id' => $variantId,
                'title' => $product->title,
                'unit_price' => $unitPrice,
                'quantity' => $quantity,
                'subtotal' => $subtotal,
                'meta' => $meta,
            ];
        }

        if (count($itemsPayload) === 0) {
            return redirect()->route('cart');
        }

        $checkoutUser = $request->user();

        if ($isGuestCheckout) {
            $email = strtolower(trim($validated['email']));
            $existingUser = User::query()->where('email', $email)->first();

            if ($existingUser) {
                $attempted = Auth::attempt([
                    'email' => $email,
                    'password' => (string) $validated['password'],
                ], true);

                if (!$attempted) {
                    return back()
                        ->withErrors(['email' => 'This email is already registered. Enter the correct password to continue.'])
                        ->withInput($request->except(['password', 'password_confirmation']));
                }

                $request->session()->regenerate();
                $checkoutUser = Auth::user();
            } else {
                $checkoutUser = User::create([
                    'name' => $validated['full_name'],
                    'email' => $email,
                    'password' => Hash::make((string) $validated['password']),
                    'has_local_password' => true,
                ]);

                Auth::login($checkoutUser, true);
                $request->session()->regenerate();
            }
        }

        $order = DB::transaction(function () use ($validated, $total, $itemsPayload, $checkoutUser, $request, $paymentGateway) {
            $order = Order::create([
                'user_id' => $checkoutUser?->id,
                'customer_name' => $validated['full_name'],
                'customer_email' => $validated['email'],
                'country' => $validated['country'],
                'notes' => $validated['notes'] ?? null,
                'status' => 'PENDING',
                'total_amount' => $total,
                'currency' => CurrencyService::BASE,
            ]);

            foreach ($itemsPayload as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'variant_id' => $item['variant_id'] ?? null,
                    'title' => $item['title'],
                    'unit_price' => $item['unit_price'],
                    'quantity' => $item['quantity'],
                    'subtotal' => $item['subtotal'],
                    'meta' => $item['meta'] ?? null,
                ]);
            }

            $transaction = Transaction::create([
                'order_id' => $order->id,
                'gateway' => $paymentGateway->code,
                'trx_id' => null,
                'amount' => $total,
                'status' => 'CREATED',
                'payload' => [
                    'ip' => $request->ip(),
                    'user_agent' => (string) $request->userAgent(),
                    'order_id' => $order->id,
                    'payment_gateway' => $paymentGateway->code,
                ],
            ]);

            $order->setRelation('transactions', collect([$transaction]));

            return $order;
        });

        $request->session()->put('pending_order_id', $order->id);

        AnalyticsService::record('order_created', [
            'user_id' => $order->user_id,
            'subject_type' => 'order',
            'subject_id' => $order->id,
            'route_name' => $request->route()?->getName(),
            'path' => '/'.$request->path(),
            'ip_address' => $request->ip(),
            'session_hash' => $request->hasSession() ? hash('sha256', (string) $request->session()->getId()) : null,
            'meta' => [
                'amount' => (float) $order->total_amount,
                'currency' => $order->currency,
            ],
        ]);

        AdminNotificationService::create(
            'order_created',
            'New order #'.$order->id,
            $order->customer_name.' placed an order for '.$this->formatOrderAmount($order).'.',
            route('admin.orders.show', $order),
            'success',
            $order
        );

        UserNotificationService::create(
            $order->user_id,
            'order_created',
            'Order #'.$order->id.' placed',
            'We received your order for '.$this->formatOrderAmount($order).'. Complete payment to start processing.',
            route('orders.show', $order),
            'info',
            $order
        );

        if ($paymentGateway->code === PaymentGatewayService::PIPRAPAY) {
            return $this->startPipraPayPayment($request, $order, $paymentGateway);
        }

        return $this->startUddoktaPayPayment($request, $order, $paymentGateway);
    }

    private function startUddoktaPayPayment(Request $request, Order $order, PaymentGateway $paymentGateway): RedirectResponse
    {
        $baseUrl = (string) $paymentGateway->base_url;
        $apiKey = (string) $paymentGateway->api_key;

        if ($paymentGateway->code !== PaymentGatewayService::UDDOKTAPAY) {
            return redirect()->route('orders.show', $order)->with('error', 'Selected payment gateway is not supported yet.');
        }

        if ($baseUrl === '' || $apiKey === '') {
            return redirect()->route('orders.show', $order)->with('error', 'Payment gateway is not configured.');
        }

        $checkoutUrl = $this->uddoktaPayEndpoint($baseUrl, '/checkout-v2');

        $fields = [
            'full_name' => $order->customer_name,
            'email' => $order->customer_email,
            'amount' => number_format((float) $order->total_amount, 2, '.', ''),
            'metadata' => [
                'user_id' => (string) ($order->user_id ?? ''),
                'order_id' => (string) $order->id,
                'payment_gateway' => (string) $paymentGateway->code,
            ],
            'redirect_url' => route('uddoktapay.success'),
            'return_type' => 'GET',
            'cancel_url' => route('uddoktapay.cancel'),
            'webhook_url' => route('uddoktapay.webhook'),
        ];

        try {
            $response = Http::acceptJson()
                ->timeout(30)
                ->withHeaders([
                    'RT-UDDOKTAPAY-API-KEY' => $apiKey,
                ])
                ->post($checkoutUrl, $fields);
        } catch (\Throwable $e) {
            Log::error('UddoktaPay create charge request failed', [
                'order_id' => $order->id,
                'message' => $e->getMessage(),
            ]);

            return redirect()->route('orders.show', $order)->with('error', 'Payment initialization failed. Please try again.');
        }

        $data = $response->json();

        $transaction = Transaction::query()
            ->where('order_id', $order->id)
            ->where('gateway', PaymentGatewayService::UDDOKTAPAY)
            ->latest()
            ->first();

        if ($transaction && $transaction->status === 'COMPLETED') {
            $request->session()->forget('pending_order_id');
            $request->session()->put('last_order_id', $order->id);

            return redirect()->route('orders.show', $order)->with('success', 'Payment completed successfully.');
        }

        if ($transaction) {
            $payload = (array) ($transaction->payload ?? []);
            $payload['uddoktapay_create_charge'] = [
                'request' => $fields,
                'http_status' => $response->status(),
                'response' => $data,
            ];
            $transaction->update([
                'payload' => $payload,
            ]);
        }

        if (!$response->successful() || !is_array($data) || empty($data['status']) || empty($data['payment_url'])) {
            Log::error('UddoktaPay create charge error', [
                'order_id' => $order->id,
                'http_status' => $response->status(),
                'response' => $data,
            ]);

            return redirect()->route('orders.show', $order)->with('error', 'Payment initialization failed. Please try again.');
        }

        return redirect()->away((string) $data['payment_url']);
    }

    public function uddoktaPaySuccess(Request $request): RedirectResponse
    {
        $invoiceId = (string) ($request->input('invoice_id') ?? $request->query('invoice_id') ?? '');
        if ($invoiceId === '') {
            return redirect()->route('checkout')->with('error', 'Missing payment reference.');
        }

        $verification = $this->verifyUddoktaPayment($invoiceId);
        if (!$verification['ok']) {
            Log::error('UddoktaPay verify payment request failed', [
                'invoice_id' => $invoiceId,
                'message' => $verification['message'],
            ]);

            return redirect()->route('checkout')->with('error', 'Payment verification failed. Please contact support.');
        }

        $result = $this->applyUddoktaPayment($request, $verification['data'], 'redirect');

        if (!$result['order']) {
            return redirect()->route('checkout')->with('error', 'Order not found.');
        }

        $order = $result['order'];

        if (!$result['valid']) {
            return redirect()->route('orders.show', $order)->with('error', 'Payment verification failed.');
        }

        if (!$result['completed']) {
            return redirect()->route('orders.show', $order)->with('warning', 'Payment is not completed yet.');
        }

        $request->session()->forget('cart');
        $request->session()->forget('pending_order_id');
        $request->session()->put('last_order_id', $order->id);

        return redirect()->route('orders.show', $order)->with('success', 'Payment completed successfully.');
    }

    public function uddoktaPayWebhook(Request $request): JsonResponse
    {
        $gateway = PaymentGatewayService::gatewayByCode(PaymentGatewayService::UDDOKTAPAY);
        $apiKey = (string) ($gateway?->api_key ?? '');
        $headerApiKey = (string) $request->header('RT-UDDOKTAPAY-API-KEY', '');

        if ($apiKey === '' || !hash_equals($apiKey, $headerApiKey)) {
            Log::warning('Unauthorized UddoktaPay webhook attempt', [
                'ip' => $request->ip(),
            ]);

            return response()->json(['message' => 'Unauthorized Action'], 401);
        }

        $payload = $request->json()->all();
        if (!is_array($payload) || empty($payload)) {
            $payload = $request->all();
        }

        $invoiceId = (string) ($payload['invoice_id'] ?? '');
        if ($invoiceId === '') {
            return response()->json(['message' => 'Missing invoice_id'], 400);
        }

        $verification = $this->verifyUddoktaPayment($invoiceId);
        if (!$verification['ok']) {
            Log::error('UddoktaPay webhook verify failed', [
                'invoice_id' => $invoiceId,
                'message' => $verification['message'],
                'payload' => $payload,
            ]);

            return response()->json(['message' => 'Payment verification failed'], 422);
        }

        $result = $this->applyUddoktaPayment($request, $verification['data'], 'webhook', $payload);

        if (!$result['order']) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        if (!$result['valid']) {
            return response()->json(['message' => 'Payment verification mismatch'], 422);
        }

        return response()->json([
            'message' => 'Webhook processed',
            'order_id' => $result['order']->id,
            'completed' => $result['completed'],
            'already_processed' => $result['already_processed'],
        ]);
    }

    public function pipraPaySuccess(Request $request): RedirectResponse
    {
        $ppId = (string) ($request->input('pp_id') ?? $request->query('pp_id') ?? $request->input('invoice_id') ?? $request->query('invoice_id') ?? '');
        if ($ppId === '') {
            return $this->cancelPendingPayment($request, PaymentGatewayService::PIPRAPAY);
        }

        $verification = $this->verifyPipraPayPayment($ppId);
        if (!$verification['ok']) {
            Log::error('PipraPay verify payment request failed', [
                'pp_id' => $ppId,
                'message' => $verification['message'],
            ]);

            return redirect()->route('checkout')->with('error', 'Payment verification failed. Please contact support.');
        }

        $result = $this->applyPipraPayPayment($request, $verification['data'], 'redirect');

        if (!$result['order']) {
            return redirect()->route('checkout')->with('error', 'Order not found.');
        }

        $order = $result['order'];

        if (!$result['valid']) {
            return redirect()->route('orders.show', $order)->with('error', 'Payment verification failed.');
        }

        if (!$result['completed']) {
            return redirect()->route('orders.show', $order)->with('warning', 'Payment is not completed yet.');
        }

        $request->session()->forget('cart');
        $request->session()->forget('pending_order_id');
        $request->session()->put('last_order_id', $order->id);

        return redirect()->route('orders.show', $order)->with('success', 'Payment completed successfully.');
    }

    public function pipraPayCancel(Request $request): RedirectResponse
    {
        return $this->cancelPendingPayment($request, PaymentGatewayService::PIPRAPAY);
    }

    public function retryOrderPayment(Request $request, Order $order): RedirectResponse
    {
        $this->authorizeCustomerOrder($request, $order);

        $validated = $request->validate([
            'payment_gateway' => ['nullable', 'string', 'max:80'],
        ]);

        $status = strtoupper((string) $order->status);
        if (!in_array($status, ['PENDING', 'CANCELLED'], true)) {
            return redirect()->route('orders.show', $order)->with('error', 'This order cannot be retried.');
        }

        $latestTransaction = $order->transactions()->latest()->first();
        $activeGateways = PaymentGatewayService::activeGateways();
        if ($activeGateways->isEmpty()) {
            return redirect()->route('orders.show', $order)->with('error', 'No active payment gateway is configured.');
        }

        $gatewayCode = $activeGateways->count() === 1
            ? (string) $activeGateways->first()->code
            : (string) ($validated['payment_gateway'] ?? '');

        $paymentGateway = PaymentGatewayService::activeByCode($gatewayCode);

        if (!$paymentGateway) {
            return redirect()->route('orders.show', $order)->with('error', 'Select a valid payment method.');
        }

        $order->update([
            'status' => 'PENDING',
            'completed_at' => null,
        ]);

        Transaction::create([
            'order_id' => $order->id,
            'gateway' => $paymentGateway->code,
            'trx_id' => null,
            'amount' => $order->total_amount,
            'status' => 'CREATED',
            'payload' => [
                'retry_from_transaction_id' => $latestTransaction?->id,
                'ip' => $request->ip(),
                'user_agent' => (string) $request->userAgent(),
                'order_id' => $order->id,
                'payment_gateway' => $paymentGateway->code,
            ],
        ]);

        $request->session()->put('pending_order_id', $order->id);
        $request->session()->put('last_order_id', $order->id);

        if ($paymentGateway->code === PaymentGatewayService::PIPRAPAY) {
            return $this->startPipraPayPayment($request, $order->fresh(), $paymentGateway);
        }

        if ($paymentGateway->code === PaymentGatewayService::UDDOKTAPAY) {
            return $this->startUddoktaPayPayment($request, $order->fresh(), $paymentGateway);
        }

        return redirect()->route('orders.show', $order)->with('error', 'Selected payment gateway is not supported yet.');
    }

    public function pipraPayWebhook(Request $request): JsonResponse
    {
        $gateway = PaymentGatewayService::gatewayByCode(PaymentGatewayService::PIPRAPAY);
        $apiKey = (string) ($gateway?->api_key ?? '');
        $headerApiKey = (string) (
            $request->header('MHS-PIPRAPAY-API-KEY')
            ?: $request->header('mh-piprapay-api-key')
            ?: $request->header('MH-PIPRAPAY-API-KEY')
        );

        if ($apiKey === '' || !hash_equals($apiKey, $headerApiKey)) {
            Log::warning('Unauthorized PipraPay webhook attempt', [
                'ip' => $request->ip(),
            ]);

            return response()->json(['message' => 'Unauthorized Action'], 401);
        }

        $payload = $request->json()->all();
        if (!is_array($payload) || empty($payload)) {
            $payload = $request->all();
        }

        $ppId = (string) ($payload['pp_id'] ?? $payload['invoice_id'] ?? '');
        if ($ppId === '') {
            return response()->json(['message' => 'Missing pp_id'], 400);
        }

        $verification = $this->verifyPipraPayPayment($ppId);
        if (!$verification['ok']) {
            Log::error('PipraPay webhook verify failed', [
                'pp_id' => $ppId,
                'message' => $verification['message'],
                'payload' => $payload,
            ]);

            return response()->json(['message' => 'Payment verification failed'], 422);
        }

        $result = $this->applyPipraPayPayment($request, $verification['data'], 'webhook', $payload);

        if (!$result['order']) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        if (!$result['valid']) {
            return response()->json(['message' => 'Payment verification mismatch'], 422);
        }

        return response()->json([
            'message' => 'Webhook processed',
            'order_id' => $result['order']->id,
            'completed' => $result['completed'],
            'already_processed' => $result['already_processed'],
        ]);
    }

    private function startPipraPayPayment(Request $request, Order $order, PaymentGateway $paymentGateway): RedirectResponse
    {
        $baseUrl = (string) $paymentGateway->base_url;
        $apiKey = (string) $paymentGateway->api_key;

        if ($baseUrl === '' || $apiKey === '') {
            return redirect()->route('orders.show', $order)->with('error', 'Payment gateway is not configured.');
        }

        $metadata = [
            'user_id' => (string) ($order->user_id ?? ''),
            'order_id' => (string) $order->id,
            'payment_gateway' => (string) $paymentGateway->code,
        ];

        $fields = [
            'full_name' => $order->customer_name,
            'email_mobile' => $order->customer_email,
            'amount' => number_format((float) $order->total_amount, 2, '.', ''),
            'metadata' => $metadata,
            'redirect_url' => route('piprapay.success'),
            'return_type' => 'GET',
            'cancel_url' => route('piprapay.cancel'),
            'webhook_url' => route('piprapay.webhook'),
            'currency' => CurrencyService::BASE,
        ];

        $legacyFields = [
            'full_name' => $order->customer_name,
            'email_address' => $order->customer_email,
            'mobile_number' => $order->customer_email,
            'amount' => number_format((float) $order->total_amount, 2, '.', ''),
            'metadata' => $metadata,
            'return_url' => route('piprapay.success'),
            'cancel_url' => route('piprapay.cancel'),
            'webhook_url' => route('piprapay.webhook'),
            'currency' => CurrencyService::BASE,
        ];

        $attempts = [
            ['path' => '/create-charge', 'request' => $fields],
            ['path' => '/checkout/redirect', 'request' => $legacyFields],
        ];
        $attemptLogs = [];
        $response = null;
        $data = null;
        $body = '';
        $paymentUrl = '';

        foreach ($attempts as $index => $attempt) {
            try {
                $response = Http::acceptJson()
                    ->timeout(30)
                    ->withHeaders($this->pipraPayHeaders($apiKey))
                    ->post($this->pipraPayEndpoint($baseUrl, $attempt['path']), $attempt['request']);
            } catch (\Throwable $e) {
                Log::error('PipraPay create charge request failed', [
                    'order_id' => $order->id,
                    'path' => $attempt['path'],
                    'message' => $e->getMessage(),
                ]);

                return redirect()
                    ->route('orders.show', $order)
                    ->with('error', $this->pipraPayConnectionErrorMessage($e->getMessage()));
            }

            $data = $response->json();
            $body = $response->body();
            $paymentUrl = $this->paymentRedirectUrl(is_array($data) ? $data : []);
            $attemptLogs[] = [
                'path' => $attempt['path'],
                'request' => $attempt['request'],
                'http_status' => $response->status(),
                'response' => $data,
                'body_excerpt' => is_array($data) ? null : substr($body, 0, 1000),
            ];

            if ($response->successful() && is_array($data) && $paymentUrl !== '') {
                break;
            }

            if ($index === array_key_last($attempts) || !$this->shouldRetryPipraPayCreateCharge($response->status(), is_array($data) ? $data : [], $body)) {
                break;
            }
        }

        $transaction = Transaction::query()
            ->where('order_id', $order->id)
            ->where('gateway', PaymentGatewayService::PIPRAPAY)
            ->latest()
            ->first();

        if ($transaction) {
            $payload = (array) ($transaction->payload ?? []);
            $payload['piprapay_create_charge'] = [
                'attempts' => $attemptLogs,
            ];

            $transaction->update([
                'trx_id' => (string) (is_array($data) ? ($data['pp_id'] ?? $data['payment_id'] ?? $transaction->trx_id) : $transaction->trx_id),
                'payload' => $payload,
            ]);
        }

        if (!$response || !$response->successful() || !is_array($data) || $paymentUrl === '') {
            Log::error('PipraPay create charge error', [
                'order_id' => $order->id,
                'http_status' => $response?->status(),
                'response' => $data,
                'body' => $body,
            ]);

            return redirect()
                ->route('orders.show', $order)
                ->with('error', $this->pipraPayHttpErrorMessage((int) ($response?->status() ?? 0), $body));
        }

        return redirect()->away($paymentUrl);
    }

    private function verifyPipraPayPayment(string $ppId): array
    {
        $gateway = PaymentGatewayService::gatewayByCode(PaymentGatewayService::PIPRAPAY);
        $baseUrl = (string) ($gateway?->base_url ?? '');
        $apiKey = (string) ($gateway?->api_key ?? '');

        if ($baseUrl === '' || $apiKey === '') {
            return [
                'ok' => false,
                'data' => [],
                'message' => 'Payment gateway is not configured.',
            ];
        }

        foreach (['/verify-payments', '/verify-payment'] as $path) {
            try {
                $response = Http::acceptJson()
                    ->timeout(30)
                    ->withHeaders($this->pipraPayHeaders($apiKey))
                    ->post($this->pipraPayEndpoint($baseUrl, $path), [
                        'pp_id' => $ppId,
                    ]);
            } catch (\Throwable $e) {
                return [
                    'ok' => false,
                    'data' => [],
                    'message' => $e->getMessage(),
                ];
            }

            $data = $response->json();
            $paymentData = is_array($data) ? $this->pipraPayPaymentData($data) : [];

            if ($response->successful() && !empty($paymentData)) {
                return [
                    'ok' => true,
                    'data' => $paymentData,
                    'message' => '',
                ];
            }
        }

        return [
            'ok' => false,
            'data' => [],
            'message' => 'HTTP verification failed.',
        ];
    }

    private function applyPipraPayPayment(Request $request, array $data, string $source, array $webhookPayload = []): array
    {
        $ppId = (string) ($data['pp_id'] ?? $data['payment_id'] ?? $data['invoice_id'] ?? '');
        $metadata = $this->metadataArray($data['metadata'] ?? []);
        $orderId = isset($metadata['order_id']) ? (int) $metadata['order_id'] : 0;

        if ($orderId <= 0) {
            Log::error('PipraPay verify payment missing order_id in metadata', [
                'pp_id' => $ppId,
                'source' => $source,
                'response' => $data,
            ]);

            return [
                'order' => null,
                'valid' => false,
                'completed' => false,
                'already_processed' => false,
            ];
        }

        $order = Order::query()->find($orderId);
        if (!$order) {
            return [
                'order' => null,
                'valid' => false,
                'completed' => false,
                'already_processed' => false,
            ];
        }

        $verifiedCustomer = strtolower(trim((string) ($data['customer_email_mobile'] ?? $data['email_mobile'] ?? $data['email'] ?? '')));
        $orderEmail = strtolower(trim((string) $order->customer_email));
        $verifiedAmount = (float) ($data['amount'] ?? $data['total'] ?? 0);
        $orderAmount = (float) $order->total_amount;

        if ($verifiedCustomer !== '' && str_contains($verifiedCustomer, '@') && $verifiedCustomer !== $orderEmail) {
            Log::error('PipraPay verify payment email mismatch', [
                'pp_id' => $ppId,
                'order_id' => $order->id,
                'source' => $source,
                'verified_customer' => $verifiedCustomer,
                'order_email' => $orderEmail,
            ]);

            return [
                'order' => $order,
                'valid' => false,
                'completed' => false,
                'already_processed' => false,
            ];
        }

        if ($verifiedAmount > 0 && abs($verifiedAmount - $orderAmount) > 0.01) {
            Log::error('PipraPay verify payment amount mismatch', [
                'pp_id' => $ppId,
                'order_id' => $order->id,
                'source' => $source,
                'verified_amount' => $verifiedAmount,
                'order_amount' => $orderAmount,
            ]);

            return [
                'order' => $order,
                'valid' => false,
                'completed' => false,
                'already_processed' => false,
            ];
        }

        $transaction = Transaction::query()
            ->where('order_id', $order->id)
            ->where('gateway', PaymentGatewayService::PIPRAPAY)
            ->latest()
            ->first();

        $verifiedStatus = strtolower((string) ($data['status'] ?? ''));
        $isCompleted = in_array($verifiedStatus, ['completed', 'complete', 'success', 'paid'], true);
        $alreadyProcessed = in_array((string) $order->status, ['PROCESSING', 'DELIVERED'], true);

        if ($transaction) {
            $payload = (array) ($transaction->payload ?? []);
            $payload['piprapay_'.$source] = [
                'pp_id' => $ppId,
                'response' => $data,
                'webhook_payload' => $webhookPayload ?: null,
                'received_at' => now()->toDateTimeString(),
            ];

            $transaction->update([
                'trx_id' => $ppId ?: $transaction->trx_id,
                'amount' => $orderAmount,
                'status' => $isCompleted ? 'COMPLETED' : 'PENDING',
                'payload' => $payload,
            ]);
        } else {
            Transaction::create([
                'order_id' => $order->id,
                'gateway' => PaymentGatewayService::PIPRAPAY,
                'trx_id' => $ppId ?: null,
                'amount' => $orderAmount,
                'status' => $isCompleted ? 'COMPLETED' : 'PENDING',
                'payload' => [
                    'piprapay_'.$source => [
                        'pp_id' => $ppId,
                        'response' => $data,
                        'webhook_payload' => $webhookPayload ?: null,
                        'received_at' => now()->toDateTimeString(),
                    ],
                ],
            ]);
        }

        if (!$isCompleted) {
            return [
                'order' => $order,
                'valid' => true,
                'completed' => false,
                'already_processed' => $alreadyProcessed,
            ];
        }

        if (!$alreadyProcessed) {
            $order->update([
                'status' => 'PROCESSING',
                'completed_at' => now(),
            ]);

            AdminNotificationService::create(
                'payment_completed',
                'Payment completed for order #'.$order->id,
                $order->customer_name.' paid '.$this->formatOrderAmount($order).' through PipraPay.',
                route('admin.orders.show', $order),
                'success',
                $order
            );

            AnalyticsService::record('order_processing', [
                'user_id' => $request->user()?->id,
                'subject_type' => 'order',
                'subject_id' => $order->id,
                'route_name' => $request->route()?->getName(),
                'path' => '/'.$request->path(),
                'ip_address' => $request->ip(),
                'session_hash' => $request->hasSession() ? hash('sha256', (string) $request->session()->getId()) : null,
                'meta' => [
                    'source' => $source,
                    'pp_id' => $ppId,
                    'gateway' => PaymentGatewayService::PIPRAPAY,
                ],
            ]);

            $this->sendOrderReceivedEmail($order);
        }

        $order->refresh();

        return [
            'order' => $order,
            'valid' => true,
            'completed' => true,
            'already_processed' => $alreadyProcessed,
        ];
    }

    private function verifyUddoktaPayment(string $invoiceId): array
    {
        $gateway = PaymentGatewayService::gatewayByCode(PaymentGatewayService::UDDOKTAPAY);
        $baseUrl = (string) ($gateway?->base_url ?? '');
        $apiKey = (string) ($gateway?->api_key ?? '');

        if ($baseUrl === '' || $apiKey === '') {
            return [
                'ok' => false,
                'data' => [],
                'message' => 'Payment gateway is not configured.',
            ];
        }

        try {
            $response = Http::acceptJson()
                ->timeout(30)
                ->withHeaders([
                    'RT-UDDOKTAPAY-API-KEY' => $apiKey,
                ])
                ->post($this->uddoktaPayEndpoint($baseUrl, '/verify-payment'), [
                    'invoice_id' => $invoiceId,
                ]);
        } catch (\Throwable $e) {
            return [
                'ok' => false,
                'data' => [],
                'message' => $e->getMessage(),
            ];
        }

        $data = $response->json();

        return [
            'ok' => $response->successful() && is_array($data),
            'data' => is_array($data) ? $data : [],
            'message' => $response->successful() ? '' : 'HTTP '.$response->status(),
        ];
    }

    private function applyUddoktaPayment(Request $request, array $data, string $source, array $webhookPayload = []): array
    {
        $invoiceId = (string) ($data['invoice_id'] ?? '');
        $metadata = $this->metadataArray($data['metadata'] ?? []);
        $orderId = isset($metadata['order_id']) ? (int) $metadata['order_id'] : 0;

        if ($orderId <= 0) {
            Log::error('UddoktaPay verify payment missing order_id in metadata', [
                'invoice_id' => $invoiceId,
                'source' => $source,
                'response' => $data,
            ]);

            return [
                'order' => null,
                'valid' => false,
                'completed' => false,
                'already_processed' => false,
            ];
        }

        $order = Order::query()->find($orderId);
        if (!$order) {
            return [
                'order' => null,
                'valid' => false,
                'completed' => false,
                'already_processed' => false,
            ];
        }

        $verifiedEmail = strtolower(trim((string) ($data['email'] ?? '')));
        $orderEmail = strtolower(trim((string) $order->customer_email));
        $verifiedAmount = (float) ($data['amount'] ?? 0);
        $orderAmount = (float) $order->total_amount;

        if ($verifiedEmail !== '' && $verifiedEmail !== $orderEmail) {
            Log::error('UddoktaPay verify payment email mismatch', [
                'invoice_id' => $invoiceId,
                'order_id' => $order->id,
                'source' => $source,
                'verified_email' => $verifiedEmail,
                'order_email' => $orderEmail,
            ]);

            return [
                'order' => $order,
                'valid' => false,
                'completed' => false,
                'already_processed' => false,
            ];
        }

        if ($verifiedAmount > 0 && abs($verifiedAmount - $orderAmount) > 0.01) {
            Log::error('UddoktaPay verify payment amount mismatch', [
                'invoice_id' => $invoiceId,
                'order_id' => $order->id,
                'source' => $source,
                'verified_amount' => $verifiedAmount,
                'order_amount' => $orderAmount,
            ]);

            return [
                'order' => $order,
                'valid' => false,
                'completed' => false,
                'already_processed' => false,
            ];
        }

        $transaction = Transaction::query()
            ->where('order_id', $order->id)
            ->where('gateway', PaymentGatewayService::UDDOKTAPAY)
            ->latest()
            ->first();

        $verifiedStatus = strtoupper((string) ($data['status'] ?? ''));
        $isCompleted = $verifiedStatus === 'COMPLETED';
        $alreadyProcessed = in_array((string) $order->status, ['PROCESSING', 'DELIVERED'], true);

        if ($transaction) {
            $payload = (array) ($transaction->payload ?? []);
            $payload['uddoktapay_'.$source] = [
                'invoice_id' => $invoiceId,
                'response' => $data,
                'webhook_payload' => $webhookPayload ?: null,
                'received_at' => now()->toDateTimeString(),
            ];

            $transaction->update([
                'trx_id' => $invoiceId ?: $transaction->trx_id,
                'amount' => $verifiedAmount > 0 ? $verifiedAmount : $transaction->amount,
                'status' => $isCompleted ? 'COMPLETED' : 'PENDING',
                'payload' => $payload,
            ]);
        } else {
            $transaction = Transaction::create([
                'order_id' => $order->id,
                'gateway' => PaymentGatewayService::UDDOKTAPAY,
                'trx_id' => $invoiceId ?: null,
                'amount' => $verifiedAmount > 0 ? $verifiedAmount : $orderAmount,
                'status' => $isCompleted ? 'COMPLETED' : 'PENDING',
                'payload' => [
                    'uddoktapay_'.$source => [
                        'invoice_id' => $invoiceId,
                        'response' => $data,
                        'webhook_payload' => $webhookPayload ?: null,
                        'received_at' => now()->toDateTimeString(),
                    ],
                ],
            ]);
        }

        if (!$isCompleted) {
            return [
                'order' => $order,
                'valid' => true,
                'completed' => false,
                'already_processed' => $alreadyProcessed,
            ];
        }

        if (!$alreadyProcessed) {
            $order->update([
                'status' => 'PROCESSING',
                'completed_at' => now(),
            ]);

            AdminNotificationService::create(
                'payment_completed',
                'Payment completed for order #'.$order->id,
                $order->customer_name.' paid '.$this->formatOrderAmount($order).'.',
                route('admin.orders.show', $order),
                'success',
                $order
            );

            AnalyticsService::record('order_processing', [
                'user_id' => $request->user()?->id,
                'subject_type' => 'order',
                'subject_id' => $order->id,
                'route_name' => $request->route()?->getName(),
                'path' => '/'.$request->path(),
                'ip_address' => $request->ip(),
                'session_hash' => $request->hasSession() ? hash('sha256', (string) $request->session()->getId()) : null,
                'meta' => [
                    'source' => $source,
                    'invoice_id' => $invoiceId,
                ],
            ]);

            $this->sendOrderReceivedEmail($order);
        }

        $order->refresh();

        return [
            'order' => $order,
            'valid' => true,
            'completed' => true,
            'already_processed' => $alreadyProcessed,
        ];
    }

    private function cancelPendingPayment(Request $request, ?string $gatewayCode = null): RedirectResponse
    {
        $orderId = (int) $request->session()->get('pending_order_id');
        if ($orderId <= 0) {
            return redirect()->route('checkout')->with('warning', 'Payment was cancelled.');
        }

        $order = Order::query()->find($orderId);

        $transactionQuery = Transaction::query()
            ->where('order_id', $orderId);

        if ($gatewayCode) {
            $transactionQuery->where('gateway', $gatewayCode);
        }

        $transaction = $transactionQuery->latest()->first();

        if ($transaction && in_array((string) $transaction->status, ['CREATED', 'PENDING'], true)) {
            $payload = (array) ($transaction->payload ?? []);
            $payload['cancelled_at'] = now()->toDateTimeString();
            $payload['cancel_source'] = $request->route()?->getName();

            $transaction->update([
                'status' => 'CANCELLED',
                'payload' => $payload,
            ]);
        }

        if ($order && !in_array((string) $order->status, ['CANCELLED', 'PROCESSING', 'DELIVERED'], true)) {
            $previousStatus = (string) $order->status;
            $order->update(['status' => 'CANCELLED']);

            AnalyticsService::record('order_cancelled', [
                'user_id' => $request->user()?->id,
                'subject_type' => 'order',
                'subject_id' => $order->id,
                'route_name' => $request->route()?->getName(),
                'path' => '/'.$request->path(),
                'ip_address' => $request->ip(),
                'session_hash' => $request->hasSession() ? hash('sha256', (string) $request->session()->getId()) : null,
                'meta' => [
                    'from' => $previousStatus,
                    'to' => 'CANCELLED',
                    'gateway' => $gatewayCode,
                ],
            ]);

            AdminNotificationService::create(
                'order_cancelled',
                'Order #'.$order->id.' was cancelled',
                $order->customer_name.' cancelled or abandoned payment.',
                route('admin.orders.show', $order),
                'warning',
                $order
            );

            UserNotificationService::create(
                $order->user_id,
                'order_cancelled',
                'Order #'.$order->id.' cancelled',
                'Payment was cancelled. You can retry payment from the order page.',
                route('orders.show', $order),
                'warning',
                $order
            );
        }

        $request->session()->put('last_order_id', $orderId);
        $request->session()->forget('pending_order_id');

        if ($order) {
            return redirect()->route('orders.show', $order)->with('warning', 'Payment was cancelled. You can retry the payment below.');
        }

        return redirect()->route('checkout')->with('warning', 'Payment was cancelled.');
    }

    private function authorizeCustomerOrder(Request $request, Order $order): void
    {
        $user = $request->user();
        $allowed = $user && $order->user_id && (int) $order->user_id === (int) $user->id;

        if (!$allowed) {
            $allowed = (int) $request->session()->get('last_order_id') === (int) $order->id
                || (int) $request->session()->get('pending_order_id') === (int) $order->id;
        }

        abort_unless($allowed, 403);
    }

    private function sendOrderReceivedEmail(Order $order): void
    {
        UserNotificationService::create(
            $order->user_id,
            'payment_completed',
            'Payment confirmed for order #'.$order->id,
            'Your payment is confirmed. We are processing your order now.',
            route('orders.show', $order),
            'success',
            $order
        );

        $renderer = new EmailTemplateRenderer();
        $rendered = $renderer->render('Order Received', [
            'name' => $order->customer_name,
            'order_id' => $order->id,
            'amount' => number_format((float) $order->total_amount, 0),
        ]);

        try {
            Mail::send([], [], function ($message) use ($order, $rendered) {
                $message->to($order->customer_email)
                    ->subject($rendered['subject'])
                    ->html($rendered['body']);
            });
        } catch (\Throwable $e) {
            Log::error('Order received email send failed', [
                'order_id' => $order->id,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function uddoktaPayCancel(Request $request): RedirectResponse
    {
        $orderId = (int) $request->session()->get('pending_order_id');
        if ($orderId > 0) {
            $order = Order::query()->find($orderId);
            $transaction = Transaction::query()
                ->where('order_id', $orderId)
                ->latest()
                ->first();

            if ($transaction && $transaction->status === 'CREATED') {
                $transaction->update([
                    'status' => 'CANCELLED',
                ]);
            }

            if ($order && $order->status !== 'CANCELLED') {
                $previousStatus = (string) $order->status;
                $order->update(['status' => 'CANCELLED']);

                AnalyticsService::record('order_cancelled', [
                    'user_id' => $request->user()?->id,
                    'subject_type' => 'order',
                    'subject_id' => $order->id,
                    'route_name' => $request->route()?->getName(),
                    'path' => '/'.$request->path(),
                    'ip_address' => $request->ip(),
                    'session_hash' => $request->hasSession() ? hash('sha256', (string) $request->session()->getId()) : null,
                    'meta' => [
                        'from' => $previousStatus,
                        'to' => 'CANCELLED',
                    ],
                ]);

                AdminNotificationService::create(
                    'order_cancelled',
                    'Order #'.$order->id.' was cancelled',
                    $order->customer_name.' cancelled or abandoned payment.',
                    route('admin.orders.show', $order),
                    'warning',
                    $order
                );

                UserNotificationService::create(
                    $order->user_id,
                    'order_cancelled',
                    'Order #'.$order->id.' cancelled',
                    'Payment was cancelled. You can retry payment from the order page.',
                    route('orders.show', $order),
                    'warning',
                    $order
                );
            }

            $request->session()->put('last_order_id', $orderId);
            $request->session()->forget('pending_order_id');

            return redirect()->route('orders.show', $orderId)->with('warning', 'Payment was cancelled.');
        }

        return redirect()->route('checkout')->with('warning', 'Payment was cancelled.');
    }

    public function uddoktaPayFailed(Request $request): RedirectResponse
    {
        $orderId = (int) $request->session()->get('pending_order_id');
        if ($orderId > 0) {
            $transaction = Transaction::query()
                ->where('order_id', $orderId)
                ->latest()
                ->first();

            if ($transaction && $transaction->status === 'CREATED') {
                $transaction->update([
                    'status' => 'FAILED',
                ]);
            }

            AnalyticsService::record('payment_failed', [
                'user_id' => $request->user()?->id,
                'subject_type' => 'order',
                'subject_id' => $orderId,
                'route_name' => $request->route()?->getName(),
                'path' => '/'.$request->path(),
                'ip_address' => $request->ip(),
                'session_hash' => $request->hasSession() ? hash('sha256', (string) $request->session()->getId()) : null,
            ]);

            AdminNotificationService::create(
                'payment_failed',
                'Payment failed for order #'.$orderId,
                'A customer payment failed during checkout.',
                route('admin.orders.show', $orderId),
                'danger'
            );

            $order = Order::query()->find($orderId);

            UserNotificationService::create(
                $order?->user_id,
                'payment_failed',
                'Payment failed for order #'.$orderId,
                'Payment could not be completed. Please retry from the order page.',
                route('orders.show', $orderId),
                'danger',
                $order
            );

            $request->session()->put('last_order_id', $orderId);
            $request->session()->forget('pending_order_id');

            return redirect()->route('orders.show', $orderId)->with('error', 'Payment failed. Please try again.');
        }

        return redirect()->route('checkout')->with('error', 'Payment failed. Please try again.');
    }

    private function uddoktaPayEndpoint(string $baseUrl, string $path): string
    {
        $baseUrl = rtrim(trim($baseUrl), '/');
        $path = '/'.ltrim($path, '/');

        if (!str_ends_with($baseUrl, '/api')) {
            $baseUrl .= '/api';
        }

        return $baseUrl.$path;
    }

    private function pipraPayEndpoint(string $baseUrl, string $path): string
    {
        $baseUrl = rtrim(trim($baseUrl), '/');
        $path = '/'.ltrim($path, '/');

        if (!str_ends_with($baseUrl, '/api')) {
            $baseUrl .= '/api';
        }

        return $baseUrl.$path;
    }

    /**
     * @return array<string, string>
     */
    private function pipraPayHeaders(string $apiKey): array
    {
        return [
            'MHS-PIPRAPAY-API-KEY' => $apiKey,
            'mh-piprapay-api-key' => $apiKey,
            'accept' => 'application/json',
            'content-type' => 'application/json',
        ];
    }

    private function paymentRedirectUrl(array $data): string
    {
        foreach (['pp_url', 'payment_url', 'redirect_url', 'checkout_url', 'url'] as $key) {
            $value = trim((string) ($data[$key] ?? ''));
            if ($value !== '') {
                return $value;
            }
        }

        return '';
    }

    /**
     * @return array<string, mixed>
     */
    private function pipraPayPaymentData(array $response): array
    {
        foreach (['data', 'payment', 'transaction'] as $key) {
            if (isset($response[$key]) && is_array($response[$key])) {
                return $response[$key];
            }
        }

        if (isset($response['status']) && $response['status'] === false) {
            return [];
        }

        return $response;
    }

    private function pipraPayHttpErrorMessage(int $status, string $body): string
    {
        $decoded = json_decode($body, true);
        if (is_array($decoded)) {
            $messages = [
                $decoded['message'] ?? null,
                $decoded['description'] ?? null,
                $decoded['error']['message'] ?? null,
                $decoded['error']['code'] ?? null,
            ];

            foreach ($messages as $value) {
                $message = is_scalar($value) ? trim((string) $value) : '';
                if ($message !== '') {
                    return 'PipraPay error: '.$message;
                }
            }
        }

        $body = strtolower($body);

        if ($status === 403 && (
            str_contains($body, 'checking your browser')
            || str_contains($body, 'just a moment')
            || str_contains($body, 'cloudflare')
        )) {
            return 'PipraPay API is blocked by server security. Please disable browser challenge for the payment API.';
        }

        if ($status === 401 || $status === 403) {
            return 'PipraPay rejected the API request. Please check the API key and API access settings.';
        }

        return 'Payment initialization failed. Please try again.';
    }

    private function shouldRetryPipraPayCreateCharge(int $status, array $data, string $body): bool
    {
        $code = (string) ($data['error']['code'] ?? $data['code'] ?? '');
        $message = strtolower((string) ($data['error']['message'] ?? $data['message'] ?? $body));

        return $status === 400
            && (
                $code === 'INVALID_JSON_PAYLOAD'
                || str_contains($message, 'json payload')
                || str_contains($message, 'malformed')
            );
    }

    private function pipraPayConnectionErrorMessage(string $message): string
    {
        $message = strtolower($message);

        if (str_contains($message, 'ssl certificate') || str_contains($message, 'unable to get local issuer certificate')) {
            return 'Payment gateway SSL certificate could not be verified from this server.';
        }

        return 'Payment initialization failed. Please try again.';
    }

    /**
     * @return array<string, mixed>
     */
    private function metadataArray(mixed $metadata): array
    {
        if (is_array($metadata)) {
            return $metadata;
        }

        if (is_string($metadata) && trim($metadata) !== '') {
            $decoded = json_decode($metadata, true);

            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }

    private function productIdsFromCart(array $cart): array
    {
        $ids = [];

        foreach ($cart as $key => $row) {
            $ids[] = is_array($row) ? (int) ($row['product_id'] ?? $key) : (int) $key;
        }

        return array_values(array_unique(array_filter($ids)));
    }

    private function formatOrderAmount(Order $order): string
    {
        $currency = strtoupper((string) ($order->currency ?: CurrencyService::BASE));
        $amount = number_format((float) $order->total_amount, 2);

        return $amount.' '.$currency;
    }
}
