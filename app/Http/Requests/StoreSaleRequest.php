<?php

namespace App\Http\Requests;

use App\Models\Sale;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'payment_method' => ['required', Rule::in(array_keys(Sale::PAYMENT_METHODS))],
            'paid' => ['nullable', 'required_if:payment_method,cash', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'Keranjang masih kosong. Pilih minimal satu produk.',
            'items.min' => 'Keranjang masih kosong. Pilih minimal satu produk.',
            'paid.required_if' => 'Masukkan jumlah pembayaran tunai.',
        ];
    }
}
