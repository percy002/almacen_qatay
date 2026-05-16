<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductVariantRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $variant = $this->route('variant');

        return $this->user()?->can('update', $variant?->product ?? null);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $variant = $this->route('variant');

        return [
            'variant_name' => ['required', 'string', 'max:100'],
            'sku' => ['nullable', 'string', 'max:80', Rule::unique('product_variants', 'sku')->ignore($variant?->id)],
            'min_stock' => ['required', 'integer', 'min:0'],
            'original_images' => ['nullable', 'array', 'max:3'],
            'original_images.*' => ['nullable', 'string'],
            'current_images' => ['nullable', 'array', 'max:3'],
            'current_images.*' => ['nullable', 'string'],
            'images' => ['nullable', 'array', 'max:3'],
            'images.*' => ['nullable', 'image', 'max:2048'],
        ];
    }
}
