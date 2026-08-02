<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductFormField;
use App\Models\ProductOptionGroup;
use App\Models\ProductOptionValue;
use App\Models\ProductVariant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(): View
    {
        $products = Product::query()
            ->with('category')
            ->latest()
            ->paginate(15);

        return view('admin.products.index', [
            'products' => $products,
        ]);
    }

    public function create(): View
    {
        $categories = Category::query()->orderBy('name')->get();

        return view('admin.products.create', [
            'categories' => $categories,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:products,slug'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'price' => ['required', 'numeric', 'min:0'],
            'regular_price' => ['nullable', 'numeric', 'min:0'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'exclusive_duration_label' => ['nullable', 'string', 'max:80'],
            'exclusive_account_label' => ['nullable', 'string', 'max:80'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'max:5120'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $slug = $validated['slug'] ?? '';
        $slug = $slug !== '' ? $slug : Str::slug($validated['title']);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
        }

        $product = Product::create([
            'title' => $validated['title'],
            'slug' => $slug,
            'category_id' => $validated['category_id'] ?? null,
            'price' => $validated['price'],
            'regular_price' => $validated['regular_price'] ?? null,
            'stock' => isset($validated['stock']) ? (int) $validated['stock'] : null,
            'exclusive_duration_label' => trim((string) ($validated['exclusive_duration_label'] ?? '')) ?: null,
            'exclusive_account_label' => trim((string) ($validated['exclusive_account_label'] ?? '')) ?: null,
            'description' => $validated['description'] ?? null,
            'image' => $imagePath,
            'is_active' => (bool) ($validated['is_active'] ?? false),
        ]);

        return redirect()->route('admin.products.edit', $product)->with('success', 'Product created successfully.');
    }

    public function edit(Product $product): View
    {
        $categories = Category::query()->orderBy('name')->get();

        $product->load([
            'optionGroups.values' => function ($q) {
                $q->orderBy('sort_order');
            },
            'variants',
            'formFields',
        ]);

        if ($product->relationLoaded('variants')) {
            $product->variants->each(function (ProductVariant $variant) {
                $opts = $variant->options ?? [];
                if (!is_array($opts)) {
                    $opts = [];
                }

                $normalized = [];
                foreach ($opts as $k => $v) {
                    $nk = $this->normalizeKey((string) $k);
                    if ($nk === '') {
                        continue;
                    }
                    $normalized[$nk] = (string) $v;
                }

                $variant->options = $normalized;
            });
        }

        return view('admin.products.edit', [
            'product' => $product,
            'categories' => $categories,
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:products,slug,'.$product->id],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'price' => ['required', 'numeric', 'min:0'],
            'regular_price' => ['nullable', 'numeric', 'min:0'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'exclusive_duration_label' => ['nullable', 'string', 'max:80'],
            'exclusive_account_label' => ['nullable', 'string', 'max:80'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'max:5120'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $slug = $validated['slug'] ?? '';
        $slug = $slug !== '' ? $slug : Str::slug($validated['title']);

        $update = [
            'title' => $validated['title'],
            'slug' => $slug,
            'category_id' => $validated['category_id'] ?? null,
            'price' => $validated['price'],
            'regular_price' => $validated['regular_price'] ?? null,
            'stock' => isset($validated['stock']) ? (int) $validated['stock'] : null,
            'exclusive_duration_label' => trim((string) ($validated['exclusive_duration_label'] ?? '')) ?: null,
            'exclusive_account_label' => trim((string) ($validated['exclusive_account_label'] ?? '')) ?: null,
            'description' => $validated['description'] ?? null,
            'is_active' => (bool) ($validated['is_active'] ?? false),
        ];

        if ($request->hasFile('image')) {
            $update['image'] = $request->file('image')->store('products', 'public');
        }

        $product->update($update);

        $this->syncOptionGroups($product, $request->input('option_groups', []));
        $this->syncVariants($product, $request->input('variants', []));
        $this->syncFormFields($product, $request->input('form_fields', []));

        $minVariantPrice = $product->variants()
            ->where('is_active', true)
            ->min('price');
        if ($minVariantPrice !== null) {
            $product->update(['price' => $minVariantPrice]);
        }

        return back()->with('success', 'Product saved successfully.');
    }

    private function normalizeKey(string $key): string
    {
        $key = strtolower(trim($key));
        $key = preg_replace('/[^a-z0-9_\-\s]+/', '', $key) ?? '';
        $key = str_replace(['-', ' '], '_', $key);
        $key = preg_replace('/_+/', '_', $key) ?? '';

        return trim($key, '_');
    }

    private function syncOptionGroups(Product $product, mixed $groupsInput): void
    {
        if (!is_array($groupsInput)) {
            $groupsInput = [];
        }

        $existingGroups = $product->optionGroups()->with('values')->get()->keyBy('id');
        $keepGroupIds = [];

        foreach ($groupsInput as $groupRow) {
            if (!is_array($groupRow)) {
                continue;
            }

            $name = trim((string) ($groupRow['name'] ?? ''));
            $key = $this->normalizeKey((string) ($groupRow['key'] ?? ''));
            $mode = (string) ($groupRow['mode'] ?? 'normal');
            if (!in_array($mode, ['normal', 'exclusive'], true)) {
                $mode = 'normal';
            }

            if ($name === '' || $key === '') {
                continue;
            }

            $sortOrder = (int) ($groupRow['sort_order'] ?? 0);
            $groupId = isset($groupRow['id']) ? (int) $groupRow['id'] : 0;

            $group = null;
            if ($groupId > 0) {
                $group = $existingGroups->get($groupId);
            }

            if ($group) {
                $group->update([
                    'name' => $name,
                    'key' => $key,
                    'mode' => $mode,
                    'sort_order' => $sortOrder,
                ]);
            } else {
                $group = ProductOptionGroup::create([
                    'product_id' => $product->id,
                    'name' => $name,
                    'key' => $key,
                    'mode' => $mode,
                    'sort_order' => $sortOrder,
                ]);
            }

            $keepGroupIds[] = $group->id;

            $valuesInput = $groupRow['values'] ?? [];
            if (!is_array($valuesInput)) {
                $valuesInput = [];
            }

            $existingValues = $group->values()->get()->keyBy('id');
            $keepValueIds = [];

            foreach ($valuesInput as $valueRow) {
                if (!is_array($valueRow)) {
                    continue;
                }

                $label = trim((string) ($valueRow['label'] ?? ''));
                $value = trim((string) ($valueRow['value'] ?? ''));

                if ($label === '' || $value === '') {
                    continue;
                }

                $valueId = isset($valueRow['id']) ? (int) $valueRow['id'] : 0;
                $isActive = (bool) ($valueRow['is_active'] ?? false);
                $valueSortOrder = (int) ($valueRow['sort_order'] ?? 0);

                $existingValue = $valueId > 0 ? $existingValues->get($valueId) : null;

                if ($existingValue) {
                    $existingValue->update([
                        'label' => $label,
                        'value' => $value,
                        'sort_order' => $valueSortOrder,
                        'is_active' => $isActive,
                    ]);
                    $keepValueIds[] = $existingValue->id;
                } else {
                    $created = ProductOptionValue::create([
                        'group_id' => $group->id,
                        'label' => $label,
                        'value' => $value,
                        'sort_order' => $valueSortOrder,
                        'is_active' => $isActive,
                    ]);
                    $keepValueIds[] = $created->id;
                }
            }

            $group->values()->whereNotIn('id', $keepValueIds)->delete();
        }

        $product->optionGroups()->whereNotIn('id', $keepGroupIds)->delete();
    }

    private function syncVariants(Product $product, mixed $variantsInput): void
    {
        if (!is_array($variantsInput)) {
            $variantsInput = [];
        }

        $groups = $product->optionGroups()->get(['key', 'mode']);
        $validKeys = $groups->pluck('key')->all();
        $exclusiveKeys = $groups->where('mode', 'exclusive')->pluck('key')->all();
        $normalKeys = $groups->where('mode', '!=', 'exclusive')->pluck('key')->all();

        $existing = $product->variants()->get()->keyBy('id');

        foreach ($variantsInput as $row) {
            if (!is_array($row)) {
                continue;
            }

            $price = $row['price'] ?? null;
            $variantId = isset($row['id']) ? (int) $row['id'] : 0;
            $isActive = (bool) ($row['is_active'] ?? false);
            $shouldDelete = (bool) ($row['_delete'] ?? false);

            if ($shouldDelete) {
                if ($variantId > 0) {
                    $product->variants()->where('id', $variantId)->delete();
                }
                continue;
            }

            if ($price === null || $price === '') {
                continue;
            }

            $options = $row['options'] ?? [];
            if (!is_array($options)) {
                $options = [];
            }

            $normalizedOptions = [];
            foreach ($options as $k => $v) {
                $nk = $this->normalizeKey((string) $k);
                if ($nk === '') {
                    continue;
                }
                if (!empty($validKeys) && !in_array($nk, $validKeys, true)) {
                    continue;
                }
                $normalizedOptions[$nk] = (string) $v;
            }

            $normalizedOptions = array_filter($normalizedOptions, fn ($v) => trim((string) $v) !== '');

            if (!empty($validKeys) && count($normalizedOptions) === 0) {
                $isActive = false;
            }

            if ($isActive && !empty($normalKeys)) {
                $hasAllNormal = count(array_diff($normalKeys, array_keys($normalizedOptions))) === 0;
                if (!$hasAllNormal) {
                    $isActive = false;
                }
            }

            if ($isActive && !empty($exclusiveKeys)) {
                $pickedExclusive = array_values(array_intersect($exclusiveKeys, array_keys($normalizedOptions)));
                if (count($pickedExclusive) > 1) {
                    $isActive = false;
                }
            }

            $regularPrice = $row['regular_price'] ?? null;
            $regularPrice = ($regularPrice !== null && $regularPrice !== '') ? (float) $regularPrice : null;

            $stock = $row['stock'] ?? null;
            $stock = ($stock !== null && $stock !== '') ? (int) $stock : null;

            $data = [
                'product_id' => $product->id,
                'price' => $price,
                'regular_price' => $regularPrice,
                'stock' => $stock,
                'is_active' => $isActive,
                'options' => $normalizedOptions,
            ];

            $model = $variantId > 0 ? $existing->get($variantId) : null;
            if ($model) {
                $model->update($data);
            } else {
                $created = ProductVariant::create($data);
            }
        }
    }

    private function syncFormFields(Product $product, mixed $fieldsInput): void
    {
        if (!is_array($fieldsInput)) {
            $fieldsInput = [];
        }

        $existing = $product->formFields()->get()->keyBy('id');
        $keepIds = [];

        foreach ($fieldsInput as $row) {
            if (!is_array($row)) {
                continue;
            }

            $label = trim((string) ($row['label'] ?? ''));
            $key = $this->normalizeKey((string) ($row['key'] ?? ''));
            if ($label === '' || $key === '') {
                continue;
            }

            $type = (string) ($row['type'] ?? 'text');
            if (!in_array($type, ['text', 'email', 'password', 'number', 'textarea'], true)) {
                $type = 'text';
            }

            $fieldId = isset($row['id']) ? (int) $row['id'] : 0;
            $isRequired = (bool) ($row['is_required'] ?? false);
            $sortOrder = (int) ($row['sort_order'] ?? 0);
            $placeholder = trim((string) ($row['placeholder'] ?? ''));
            $placeholder = $placeholder !== '' ? $placeholder : null;

            $data = [
                'product_id' => $product->id,
                'label' => $label,
                'key' => $key,
                'type' => $type,
                'placeholder' => $placeholder,
                'is_required' => $isRequired,
                'sort_order' => $sortOrder,
            ];

            $model = $fieldId > 0 ? $existing->get($fieldId) : null;
            if ($model) {
                $model->update($data);
                $keepIds[] = $model->id;
            } else {
                $created = ProductFormField::create($data);
                $keepIds[] = $created->id;
            }
        }

        $product->formFields()->whereNotIn('id', $keepIds)->delete();
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        return redirect()->route('admin.products.index')->with('success', 'Product deleted successfully.');
    }
}
