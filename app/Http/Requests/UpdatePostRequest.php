<?php

namespace App\Http\Requests;

use App\Http\Requests\StorePostRequest;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePostRequest extends StorePostRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $post = $this->route('post');

        return $post->user_id == auth()->user()->id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = parent::rules();
        unset($rules['group_id']);

        return array_merge($rules, [
            'deleted_file_ids' => 'array',
            'deleted_file_ids.*' => 'numeric'
        ]);
    }
}
