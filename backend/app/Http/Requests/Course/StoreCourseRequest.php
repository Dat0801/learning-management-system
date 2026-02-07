<?php

namespace App\Http\Requests\Course;

use Illuminate\Foundation\Http\FormRequest;

class StoreCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && in_array($this->user()->role, ['instructor', 'admin']);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string'],
            'description' => ['nullable', 'string'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'status' => ['nullable', 'in:draft,published,archived'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'thumbnail' => ['nullable', 'image|mimes:jpeg,png,jpg,gif,webp|max:5120'],
            'lessons' => ['nullable', 'array'],
            'lessons.*.title' => ['required', 'string'],
            'lessons.*.content' => ['nullable', 'string'],
            'lessons.*.video_url' => ['nullable', 'string'],
            'lessons.*.duration' => ['nullable', 'string'],
            'lessons.*.is_preview' => ['boolean'],
            'lessons.*.order' => ['integer'],
        ];
    }
}
