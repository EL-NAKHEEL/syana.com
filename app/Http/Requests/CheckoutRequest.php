<?php

namespace App\Http\Requests;

use App\Payments\PaymentGateways;
use App\Support\Phone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CheckoutRequest extends FormRequest
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
            'area_text' => ['nullable', 'required_without:area_id', 'string', 'max:120'],
            'address' => ['required', 'string', 'min:5', 'max:500'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'payment_method' => ['required', Rule::in(array_keys(app(PaymentGateways::class)->available()))],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'اكتب اسمك من فضلك.',
            'phone.required' => 'اكتب رقم موبايلك علشان نأكد الطلب.',
            'phone.regex' => 'رقم الموبايل لازم يكون مصري ومكوّن من 11 رقم ويبدأ بـ 01.',
            'area_text.required_without' => 'اكتب المنطقة.',
            'address.required' => 'اكتب العنوان بالتفصيل.',
        ];
    }
}
