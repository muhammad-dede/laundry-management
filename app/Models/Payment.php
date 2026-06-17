<?php

namespace App\Models;

use App\Enums\PaymentMethodEnum;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $guarded = [];

    protected $casts = [
        'payment_method' => PaymentMethodEnum::class,
    ];

    protected $appends = [
        'payment_method_label',
    ];

    public function getPaymentMethodLabelAttribute(): ?string
    {
        return $this->payment_method?->label();
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
