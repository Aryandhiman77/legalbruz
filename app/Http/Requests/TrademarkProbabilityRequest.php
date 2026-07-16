<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TrademarkProbabilityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'keyword' => ['required', 'string', 'max:255'],
            'class' => ['nullable', 'string', 'max:100'],
            'proposed_description' => ['nullable', 'string', 'max:2000'],
            'data' => ['required', 'array', 'min:1', 'max:100'],
            'data.*.application_id' => ['nullable', 'string', 'max:100'],
            'data.*.trademark_name' => ['required', 'string', 'max:255'],
            'data.*.status' => ['nullable', 'string', 'max:100'],
            'data.*.class' => ['nullable', 'string', 'max:100'],
            'data.*.type' => ['nullable', 'string', 'max:100'],
            'data.*.proprietor' => ['nullable', 'string', 'max:500'],
            'data.*.description' => ['nullable', 'string'],
        ];
    }
}
