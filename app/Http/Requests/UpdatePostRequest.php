<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Public update allowed for this project. Change if you add auth.
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        // Use "sometimes" so partial updates are allowed on PATCH/PUT
        return [
            'title' => 'sometimes|required|string|max:255',
            'content' => 'sometimes|required|string',
            'category' => 'sometimes|nullable|string|max:100',
            'excerpt' => 'sometimes|nullable|string|max:500',
            'tags' => 'sometimes|nullable|string|max:255',
            'read_time' => 'sometimes|nullable|integer|min:1',
            'views' => 'sometimes|nullable|integer|min:0',
        ];
    }
}