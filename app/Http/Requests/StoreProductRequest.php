<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules.
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'external_id' => 'nullable|string|max:255',
            'original_link' => 'nullable|url|max:2048',
            'category_id' => 'nullable|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'source_id' => 'nullable|exists:sources,id',
            // URL-based images (legacy/API)
            'images' => 'nullable|array',
            'images.*.url' => 'required|url',
            'images.*.type' => 'required|in:original,qc,user_upload',
            // File uploads from admin panel
            'images_upload' => 'nullable|array',
            'images_upload.*' => 'image|mimes:jpeg,jpg,png,webp,gif|max:5120',
        ];
    }

    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Product name is required.',
            'original_link.url' => 'Invalid URL format.',
            'category_id.exists' => 'The selected category does not exist.',
            'brand_id.exists' => 'The selected brand does not exist.',
            'source_id.exists' => 'The selected marketplace does not exist.',
            'images_upload.*.image' => 'Each file must be an image.',
            'images_upload.*.mimes' => 'Allowed formats: JPEG, PNG, WebP, GIF.',
            'images_upload.*.max' => 'Maximum file size is 5MB.',
        ];
    }
}
