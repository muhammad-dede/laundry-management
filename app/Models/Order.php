<?php

namespace App\Models;

use App\Enums\OrderStatusEnum;
use App\Enums\PaymentStatusEnum;
use App\Enums\PaymentTypeEnum;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $guarded = [];

    protected $casts = [
        'payment_type' => PaymentTypeEnum::class,
        'payment_status' => PaymentStatusEnum::class,
        'order_status' => OrderStatusEnum::class,
    ];

    protected $appends = [
        'payment_type_label',
        'payment_status_label',
        'order_status_label',
    ];

    public function getPaymentTypeLabelAttribute(): ?string
    {
        return $this->payment_type?->label();
    }

    public function getPaymentStatusLabelAttribute(): ?string
    {
        return $this->payment_status?->label();
    }

    public function getOrderStatusLabelAttribute(): ?string
    {
        return $this->order_status?->label();
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class);
    }

    public function orderStatusHistories()
    {
        return $this->hasMany(OrderStatusHistory::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    public function orderPickup()
    {
        return $this->hasOne(OrderPickup::class);
    }

    public function orderDelivery()
    {
        return $this->hasOne(OrderDelivery::class);
    }
}
