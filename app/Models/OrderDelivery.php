<?php

namespace App\Models;

use App\Enums\DeliveryStatusEnum;
use Illuminate\Database\Eloquent\Model;

class OrderDelivery extends Model
{
    protected $guarded = [];

    protected $casts = [
        'delivery_status' => DeliveryStatusEnum::class,
    ];

    protected $appends = [
        'delivery_status_label',
    ];

    public function getDeliveryStatusLabelAttribute(): ?string
    {
        return $this->delivery_status?->label();
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function courier()
    {
        return $this->belongsTo(User::class, 'courier_id');
    }
}
