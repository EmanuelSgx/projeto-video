<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VideoUploadRequest extends FormRequest
{
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
            'video' => [
                'required',
                'file',
                'mimes:mp4,mov,avi,webm,wmv',
                'max:102400', // 100MB in KB
            ],
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'video.required' => 'Video file is required.',
            'video.file' => 'The uploaded file must be a valid file.',
            'video.mimes' => 'Video must be of type: mp4, mov, avi, webm, wmv.',
            'video.max' => 'Video file size cannot exceed 100MB.',
        ];
    }
}
