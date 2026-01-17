<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * StoreOutfitRequest
 * 
 * Clase de validación para la creación de outfits.
 * Valida los datos del canvas incluyendo productos y sus posiciones.
 */
class StoreOutfitRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado para hacer esta petición.
     * Solo usuarios autenticados pueden crear outfits.
     */
    public function authorize(): bool
    {
        return true; // La autenticación se maneja en el middleware de la ruta
    }

    /**
     * Reglas de validación para crear un outfit.
     * Valida el título y los items del canvas con sus atributos pivote.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Datos básicos del outfit
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'thumbnail_url' => 'nullable|url',

            // Items del canvas (productos con posiciones)
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.x' => 'required|numeric',
            'items.*.y' => 'required|numeric',
            'items.*.rotation' => 'required|numeric',
            'items.*.scaleX' => 'required|numeric',
            'items.*.scaleY' => 'required|numeric',
            'items.*.zIndex' => 'required|integer|min:0',
            'items.*.isFlipped' => 'required|boolean',
            'items.*.imageId' => 'nullable|exists:product_images,id',
        ];
    }

    /**
     * Mensajes personalizados de error en español.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'El título del outfit es obligatorio.',
            'title.max' => 'El título no puede tener más de 255 caracteres.',
            'items.required' => 'Debes añadir al menos un producto al outfit.',
            'items.min' => 'El outfit debe tener al menos un producto.',
            'items.*.product_id.required' => 'Cada item debe tener un producto asociado.',
            'items.*.product_id.exists' => 'El producto seleccionado no existe.',
            'items.*.x.required' => 'La posición X es obligatoria.',
            'items.*.y.required' => 'La posición Y es obligatoria.',
            'items.*.imageId.exists' => 'La imagen seleccionada no existe.',
        ];
    }
}
