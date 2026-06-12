<?php

namespace App\Http\Requests\Service;

use App\Enums\UnitTypeEnum;
use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'unit_type' => ['required', 'string', 'in:' . implode(',', UnitTypeEnum::values())],
            'price' => ['required', 'numeric', 'min:0'],
            'estimated_days' => ['required', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'Nama',
            'unit_type' => 'Jenis Unit',
            'price' => 'Harga',
            'estimated_days' => 'Estimasi Hari',
            'is_active' => 'Status',
        ];
    }
}
