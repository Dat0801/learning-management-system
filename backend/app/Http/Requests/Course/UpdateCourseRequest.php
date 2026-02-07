<?php

namespace App\Http\Requests\Course;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string'],
            'description' => ['sometimes', 'nullable', 'string'],
            'price' => ['sometimes', 'numeric', 'min:0'],
            'status' => ['sometimes', 'in:draft,published,archived'],
            'thumbnail' => ['sometimes', 'nullable', 'image|mimes:jpeg,png,jpg,gif,webp|max:5120'],
        ];
    }
}
