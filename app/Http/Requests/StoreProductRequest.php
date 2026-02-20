<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    /**
     * Determinar si el usuario está autorizado.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Reglas de validación.
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'external_id' => 'required|string|max:255',
            'original_link' => 'required|url',
            'category_id' => 'nullable|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'source_id' => 'nullable|exists:sources,id',
            'images' => 'nullable|array',
            'images.*.url' => 'required|url',
            'images.*.type' => 'required|in:original,qc,user_upload',
        ];
    }

    /**
     * Mensajes en español.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'el nombre es obligatorio.',
            'external_id.required' => 'El ID externo es necesario para identificar el producto en el marketplace.',
            'original_link.required' => 'El enlace original es obligatorio.',
            'original_link.url' => 'Formato de enlace inválido.',
            'category_id.exists' => 'La categoría seleccionada no existe.',
            'brand_id.exists' => 'La marca seleccionada no existe.',
            'source_id.exists' => 'El marketplace seleccionado no existe.',
        ];
    }
}
