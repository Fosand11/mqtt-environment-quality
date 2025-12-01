<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MetricsRequest extends FormRequest
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
            'hours' => ['nullable', 'integer', 'min:1', 'max:720'],
            'days' => ['nullable', 'integer', 'min:1', 'max:365'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'hours.integer' => __('validation.integer', ['attribute' => __('attributes.hours')]),
            'hours.min' => __('validation.min.numeric', ['attribute' => __('attributes.hours'), 'min' => 1]),
            'hours.max' => __('validation.max.numeric', ['attribute' => __('attributes.hours'), 'max' => 720]),
            'days.integer' => __('validation.integer', ['attribute' => __('attributes.days')]),
            'days.min' => __('validation.min.numeric', ['attribute' => __('attributes.days'), 'min' => 1]),
            'days.max' => __('validation.max.numeric', ['attribute' => __('attributes.days'), 'max' => 365]),
        ];
    }
}
