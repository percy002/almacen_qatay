<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

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
            'sku' => ['required', 'string', 'max:80', 'unique:product_variants,sku,' . ($variant?->id ?? 'NULL')],
            'current_stock' => ['required', 'integer', 'min:0'],
        ];
    }
}
