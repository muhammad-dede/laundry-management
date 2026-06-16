<?php

namespace App\Http\Requests\Order;

use App\Enums\PaymentMethodEnum;
use App\Enums\PaymentTypeEnum;
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
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:20'],
            'customer_address' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'payment_type' => [
                'required',
                'in:' . implode(',', PaymentTypeEnum::values())
            ],
            'payment_method' => [
                'nullable',
                'required_if:payment_type,' . PaymentTypeEnum::FULL_PAYMENT->value,
                'in:' . implode(',', PaymentMethodEnum::values()),
            ],
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
            'customer_name' => 'Nama Pelanggan',
            'customer_phone' => 'No. Telepon',
            'customer_address' => 'Alamat',
            'notes' => 'Catatan',
            'discount' => 'Diskon',
            'payment_type' => 'Jenis Pembayaran',
            'payment_method' => 'Metode Pembayaran',
            'order_detail' => 'Layanan',
            'order_detail.*.service_id' => 'Layanan',
            'order_detail.*.service_name' => 'Nama Layanan',
            'order_detail.*.service_unit_type' => 'Tipe Unit',
            'order_detail.*.service_estimated_days' => 'Estimasi Pengerjaan',
            'order_detail.*.quantity' => 'Jumlah',
            'order_detail.*.price' => 'Harga',
            'order_detail.*.subtotal' => 'Subtotal',
        ];
    }
}
