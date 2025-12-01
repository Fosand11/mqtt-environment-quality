<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSensorDataRequest extends FormRequest
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
            'temperature' => ['required', 'numeric', 'between:-50,100'],
            'humidity' => ['required', 'numeric', 'between:0,100'],
            'air_quality' => ['nullable', 'numeric', 'between:0,100'],
            'timestamp' => ['nullable', 'date'],
            'alert' => ['nullable', 'array'],
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
            'temperature.required' => __('validation.required', ['attribute' => __('attributes.temperature')]),
            'temperature.numeric' => __('validation.numeric', ['attribute' => __('attributes.temperature')]),
            'temperature.between' => __('validation.between.numeric', ['attribute' => __('attributes.temperature'), 'min' => -50, 'max' => 100]),
            'humidity.required' => __('validation.required', ['attribute' => __('attributes.humidity')]),
            'humidity.numeric' => __('validation.numeric', ['attribute' => __('attributes.humidity')]),
            'humidity.between' => __('validation.between.numeric', ['attribute' => __('attributes.humidity'), 'min' => 0, 'max' => 100]),
            'air_quality.numeric' => __('validation.numeric', ['attribute' => __('attributes.air_quality')]),
            'air_quality.between' => __('validation.between.numeric', ['attribute' => __('attributes.air_quality'), 'min' => 0, 'max' => 100]),
            'timestamp.date' => __('validation.date', ['attribute' => __('attributes.timestamp')]),
            'alert.array' => __('validation.array', ['attribute' => __('attributes.alert')]),
        ];
    }
}
