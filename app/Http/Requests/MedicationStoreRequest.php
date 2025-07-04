<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MedicationStoreRequest extends FormRequest
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
             'medication_name' => ['required', 'string', 'max:255'], // 必須かつ文字列、最大255文字
            'dosage' => ['nullable', 'string', 'max:255'],        // オプションかつ文字列、最大255文字
            'notes' => ['nullable', 'string', 'max:1000'],         // オプションかつ文字列、最大1000文字 (textカラム用)
            'effect' => ['nullable', 'string', 'max:1000'],        // オプションかつ文字列、最大1000文字
            'side_effects' => ['nullable', 'string', 'max:1000'],  // オプションかつ文字列、最大1000文字
        ];
    }
}
