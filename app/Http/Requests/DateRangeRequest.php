<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DateRangeRequest extends FormRequest
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
            'start_date' => ['required', 'date', 'before_or_equal:end_date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
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
            'start_date.required' => __('validation.required', ['attribute' => __('attributes.start_date')]),
            'start_date.date' => __('validation.date', ['attribute' => __('attributes.start_date')]),
            'start_date.before_or_equal' => __('validation.before_or_equal', ['attribute' => __('attributes.start_date'), 'date' => __('attributes.end_date')]),
            'end_date.required' => __('validation.required', ['attribute' => __('attributes.end_date')]),
            'end_date.date' => __('validation.date', ['attribute' => __('attributes.end_date')]),
            'end_date.after_or_equal' => __('validation.after_or_equal', ['attribute' => __('attributes.end_date'), 'date' => __('attributes.start_date')]),
        ];
    }
}
