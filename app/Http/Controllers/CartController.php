<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function show(Request $request): View
    {
        $cart = $this->getCart($request);

        $productIds = $this->productIdsFromCart($cart);

        $products = Product::query()
            ->with('category')
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
                'cart_key' => (string) $cartKey,
                'product' => $product,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'subtotal' => $subtotal,
                'variant_id' => $variantId,
                'meta' => $meta,
            ];
        }

        return view('pages.cart', [
            'items' => $items,
            'total' => $total,
        ]);
    }

    public function add(Request $request, Product $product): RedirectResponse
    {
        abort_unless($product->is_active, 404);

        $product->load([
            'optionGroups.values' => function ($q) {
                $q->orderBy('sort_order');
            },
            'variants',
            'formFields',
        ]);

        $variants = ($product->variants ?? collect())->where('is_active', true)->values();
        $hasOptions = ($product->optionGroups ?? collect())->count() > 0 && $variants->count() > 0;

        $variantId = $request->input('variant_id');
        $variant = null;
        if ($hasOptions) {
            $variantId = (int) $variantId;
            abort_unless($variantId > 0, 422);
            $variant = $variants->firstWhere('id', $variantId);
            abort_unless($variant, 422);
        }

        $pf = $this->normalizeFields($request->input('pf', []));

        $rules = [];
        foreach (($product->formFields ?? collect())->where('is_required', true) as $field) {
            $rules['pf.'.$field->key] = ['required', 'string', 'max:255'];
        }
        if (count($rules) > 0) {
            $request->validate($rules);
        }

        $cart = $this->getCart($request);

        $cartKey = $this->cartKey($product, $variant, $pf);
        $existing = $cart[$cartKey] ?? null;
        $existingQty = is_array($existing) ? (int) ($existing['quantity'] ?? 0) : (int) $existing;
        $nextQty = max(1, $existingQty + 1);

        $unitPrice = $variant ? (float) $variant->price : (float) $product->price;

        $optionLabels = [];
        if ($variant) {
            foreach ($product->optionGroups as $group) {
                $selectedValue = ($variant->options ?? [])[$group->key] ?? null;
                if ($selectedValue !== null) {
                    $match = $group->values->firstWhere('value', (string) $selectedValue);
                    $optionLabels[$group->name] = $match ? $match->label : $selectedValue;
                }
            }
        }

        $cart[$cartKey] = [
            'product_id' => $product->id,
            'quantity' => $nextQty,
            'variant_id' => $variant ? $variant->id : null,
            'unit_price' => $unitPrice,
            'meta' => [
                'options' => $variant ? (array) ($variant->options ?? []) : [],
                'option_labels' => $optionLabels,
                'fields' => $pf,
            ],
        ];

        $request->session()->put('cart', $cart);

        return back()->with('success', 'Added to cart.');
    }

    public function buyNow(Request $request, Product $product): RedirectResponse
    {
        abort_unless($product->is_active, 404);

        $product->load([
            'optionGroups.values' => function ($q) {
                $q->orderBy('sort_order');
            },
            'variants',
            'formFields',
        ]);

        $variants = ($product->variants ?? collect())->where('is_active', true)->values();
        $hasOptions = ($product->optionGroups ?? collect())->count() > 0 && $variants->count() > 0;

        $variantId = $request->input('variant_id');
        $variant = null;
        if ($hasOptions) {
            $variantId = (int) $variantId;
            abort_unless($variantId > 0, 422);
            $variant = $variants->firstWhere('id', $variantId);
            abort_unless($variant, 422);
        }

        $pf = $this->normalizeFields($request->input('pf', []));

        $rules = [];
        foreach (($product->formFields ?? collect())->where('is_required', true) as $field) {
            $rules['pf.'.$field->key] = ['required', 'string', 'max:255'];
        }
        if (count($rules) > 0) {
            $request->validate($rules);
        }

        $unitPrice = $variant ? (float) $variant->price : (float) $product->price;

        $optionLabels = [];
        if ($variant) {
            foreach ($product->optionGroups as $group) {
                $selectedValue = ($variant->options ?? [])[$group->key] ?? null;
                if ($selectedValue !== null) {
                    $match = $group->values->firstWhere('value', (string) $selectedValue);
                    $optionLabels[$group->name] = $match ? $match->label : $selectedValue;
                }
            }
        }

        $cartKey = $this->cartKey($product, $variant, $pf);

        $request->session()->put('cart', [
            $cartKey => [
                'product_id' => $product->id,
                'quantity' => 1,
                'variant_id' => $variant ? $variant->id : null,
                'unit_price' => $unitPrice,
                'meta' => [
                    'options' => $variant ? (array) ($variant->options ?? []) : [],
                    'option_labels' => $optionLabels,
                    'fields' => $pf,
                ],
            ],
        ]);

        return redirect()->route('checkout')->with('success', 'Ready for checkout.');
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        abort_unless($product->is_active, 404);

        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:99'],
        ]);

        $cart = $this->getCart($request);

        $cartKey = $this->requestedCartKey($request, $product);
        $row = $cart[$cartKey] ?? null;
        if (is_array($row)) {
            $row['quantity'] = (int) $validated['quantity'];
            $cart[$cartKey] = $row;
        } else {
            $cart[$cartKey] = (int) $validated['quantity'];
        }

        $request->session()->put('cart', $cart);

        return back()->with('success', 'Cart updated successfully.');
    }

    public function remove(Request $request, Product $product): RedirectResponse
    {
        $cart = $this->getCart($request);
        unset($cart[$this->requestedCartKey($request, $product)]);

        $request->session()->put('cart', $cart);

        return back()->with('success', 'Item removed from cart.');
    }

    public function clear(Request $request): RedirectResponse
    {
        $request->session()->forget('cart');

        return back()->with('success', 'Cart cleared successfully.');
    }

    private function getCart(Request $request): array
    {
        $cart = $request->session()->get('cart', []);

        return is_array($cart) ? $cart : [];
    }

    private function productIdsFromCart(array $cart): array
    {
        $ids = [];

        foreach ($cart as $key => $row) {
            $ids[] = is_array($row) ? (int) ($row['product_id'] ?? $key) : (int) $key;
        }

        return array_values(array_unique(array_filter($ids)));
    }

    private function cartKey(Product $product, mixed $variant, array $fields): string
    {
        $variantId = $variant ? (int) $variant->id : 0;
        $fieldHash = hash('sha256', json_encode($fields, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '');

        return $product->id.':'.$variantId.':'.$fieldHash;
    }

    private function requestedCartKey(Request $request, Product $product): string
    {
        $key = (string) $request->input('cart_key', '');

        return $key !== '' ? $key : (string) $product->id;
    }

    private function normalizeFields(mixed $fields): array
    {
        if (!is_array($fields)) {
            return [];
        }

        ksort($fields);

        return $fields;
    }
}
