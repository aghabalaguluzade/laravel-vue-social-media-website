<?php

namespace App\Http\Requests;

use App\Http\Enums\GroupUserStatus;
use App\Models\GroupUser;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rules\File;

class StorePostRequest extends FormRequest
{
    public static array $extensions = [
        'jpg', 'jpeg', 'png', 'gif', 'webp',
        'mp3', 'wav', 'mp4',
        "doc", "docx", "pdf", "csv", "xls", "xlsx",
        "zip"
    ];

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'body' => ['nullable', 'string'],
            'attachments' => [
                'array',
                'max:50',
                function($attribute, $value, $fail) {
                    $totalSize = collect($value)->sum(fn(UploadedFile $file) => $file->getSize());
                    if($totalSize > 1 * 1024 * 1024 * 1024) {
                        $fail('The total size of all files must not exceed 1GB');
                    }
                },
            ],
            'attachments.*' => [
                'file',
                File::types(self::$extensions),
            ],
            'user_id' => ['numeric'],
            'group_id' => ['nullable', 'exists:groups,id', function($attribute, $value, \Closure $fail) {
                $groupUser = GroupUser::where('user_id', auth()->id())
                    ->where('group_id', $value)
                    ->where('status', GroupUserStatus::APPROVED->value)
                    ->exists();

                if(!$groupUser) {
                    $fail('You don\'t have permission to create post in this group');
                }
            }]
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'user_id' => auth()->user()->id,
            'body' => $this->input('body') ?: ''
        ]);
    }

    public function message() {
        return [
            'attachments.*.file' => 'Each attachment must be a file.',
            'attachments.*.mimes' => 'Invalid file type for attachments.'
        ];
    }
}