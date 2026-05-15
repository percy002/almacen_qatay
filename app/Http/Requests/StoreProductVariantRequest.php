<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreProductVariantRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Solo admin puede crear variantes
        $product = $this->route('product');

        return $this->user()?->can('update', $product);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'variant_name' => ['required', 'string', 'max:100'],
            'sku' => ['nullable', 'string', 'max:80', 'unique:product_variants,sku'],
            'images' => ['required', 'array', 'min:1', 'max:3'],
            'images.*' => ['nullable', 'image', 'max:2048'],
        ];
    }
}
