<?php

namespace App\Http\Requests;

use App\Models\Product;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Solo admin puede crear productos
        return $this->user()?->can('create', Product::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:200'],
            'internal_code' => ['nullable', 'string', 'max:50', 'unique:products,internal_code'],
            'description' => ['nullable', 'string'],
            'min_stock' => ['required', 'integer', 'min:0'],
            'status' => ['required', 'in:activo,inactivo'],
        ];
    }
}
