<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Solo admin puede actualizar productos
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
        $product = $this->route('product');

        return [
            'name' => ['required', 'string', 'max:200'],
            'internal_code' => ['nullable', 'string', 'max:50', 'unique:products,internal_code,'.($product?->id ?? 'NULL')],
            'description' => ['nullable', 'string'],
            'min_stock' => ['required', 'integer', 'min:0'],
            'status' => ['required', 'in:activo,inactivo'],
        ];
    }
}
