<?php

namespace App\Http\Requests;

use App\Models\BookingRequest;
use App\Support\Phone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['phone' => Phone::normalizeEgyptianMobile($this->input('phone')) ?? $this->input('phone')]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'phone' => ['required', 'regex:/^01[0125]\d{8}$/'],
            'area_id' => ['nullable', Rule::exists('areas', 'id')->where('is_published', true)],
            'area_text' => ['nullable', 'string', 'max:120'],
            'service_id' => ['nullable', Rule::exists('services', 'id')->where('is_published', true)],
            'ac_type' => ['nullable', Rule::in(array_keys(BookingRequest::AC_TYPES))],
            'preferred_date' => ['nullable', 'date', 'after_or_equal:today'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'اكتب اسمك من فضلك.',
            'phone.required' => 'اكتب رقم موبايلك علشان نكلمك.',
            'phone.regex' => 'رقم الموبايل لازم يكون مصري ومكوّن من 11 رقم ويبدأ بـ 01.',
            'preferred_date.after_or_equal' => 'اختار ميعاد من النهارده أو بعده.',
        ];
    }
}
