<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Dipakai untuk tambah dan ubah produk. */
class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // akses dibatasi oleh middleware role di routes
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
            'barcode' => $this->filled('barcode') ? trim($this->barcode) : null,
        ]);
    }

    public function rules(): array
    {
        $id = $this->route('product')?->id;

        return [
            'category_id' => ['nullable', 'exists:categories,id'],
            'sku' => ['required', 'string', 'max:50', Rule::unique('products', 'sku')->ignore($id)],
            'barcode' => ['nullable', 'string', 'max:50', Rule::unique('products', 'barcode')->ignore($id)],
            'name' => ['required', 'string', 'max:255'],
            'brand' => ['nullable', 'string', 'max:100'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'min_stock' => ['required', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ];
    }
}
