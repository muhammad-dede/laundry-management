<?php

namespace App\Http\Requests\Order;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFromPickupRequest extends FormRequest
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
            'notes' => ['nullable', 'string', 'max:1000'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'pickup_fee' => ['nullable', 'numeric', 'min:0'],
            'order_detail' => ['required', 'array', 'min:1'],
            'order_detail.*.service_id' => [
                'required',
                'exists:services,id',
            ],
            'order_detail.*.service_name' => [
                'required',
                'string',
                'max:255',
            ],
            'order_detail.*.service_unit_type' => [
                'required',
                'string',
                'max:50',
            ],
            'order_detail.*.service_estimated_days' => [
                'required',
                'integer',
                'min:0',
            ],
            'order_detail.*.quantity' => [
                'required',
                'numeric',
                'min:1',
            ],
            'order_detail.*.price' => [
                'required',
                'numeric',
                'min:0',
            ],
            'order_detail.*.subtotal' => [
                'required',
                'numeric',
                'min:0',
            ],
            'delivery_required' => ['required', 'boolean'],
            'delivery_fee' => [
                Rule::requiredIf(request()->boolean('delivery_required')),
                'numeric',
                'min:0',
            ],
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
            'notes' => 'Catatan',
            'discount' => 'Diskon',
            'pickup_fee' => 'Biaya Pengambilan',
            'order_detail' => 'Layanan',
            'order_detail.*.service_id' => 'Layanan',
            'order_detail.*.service_name' => 'Nama Layanan',
            'order_detail.*.service_unit_type' => 'Tipe Unit',
            'order_detail.*.service_estimated_days' => 'Estimasi Pengerjaan',
            'order_detail.*.quantity' => 'Jumlah',
            'order_detail.*.price' => 'Harga',
            'order_detail.*.subtotal' => 'Subtotal',
            'delivery_fee' => 'Biaya Pengiriman',
        ];
    }
}
