<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:80'],
            'rating' => ['required', 'integer', 'between:1,5'],
            'body' => ['required', 'string', 'min:20', 'max:2000'],
            'area_id' => ['nullable', Rule::exists('areas', 'id')->where('is_published', true)],
            'service_id' => ['nullable', Rule::exists('services', 'id')->where('is_published', true)],
            'product_id' => ['nullable', Rule::exists('products', 'id')->where('is_published', true)],
            'temp_before' => ['nullable', 'integer', 'between:10,55'],
            'temp_after' => ['nullable', 'integer', 'between:10,55'],
            'video_url' => ['nullable', 'url', 'regex:#^https://(www\.)?(youtube\.com|youtu\.be)/#'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'اكتب اسمك.',
            'rating.required' => 'اختار التقييم من 1 لـ 5.',
            'body.required' => 'اكتب رأيك.',
            'body.min' => 'اكتب رأيك بتفاصيل أكتر شوية (20 حرف على الأقل).',
            'video_url.regex' => 'رابط الفيديو لازم يكون من يوتيوب.',
        ];
    }
}
