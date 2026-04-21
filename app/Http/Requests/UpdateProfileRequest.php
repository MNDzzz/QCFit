<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'name'             => 'required|min:3',
            'alias'            => 'nullable|string|max:50|unique:users,alias,' . auth()->id(),
            'email'            => 'required|email|unique:users,email,' . auth()->id(),
            'bio'              => 'nullable|string|max:500',
            'avatar'           => 'nullable|image|max:2048',
            'agent_preference' => 'nullable|string|in:cnfans,mulebuy,hoobuy',
        ];
    }
}
