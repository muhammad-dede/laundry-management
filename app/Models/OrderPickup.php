<?php

namespace App\Models;

use App\Enums\PickupStatusEnum;
use Illuminate\Database\Eloquent\Model;

class OrderPickup extends Model
{
    protected $guarded = [];

    protected $casts = [
        'pickup_status' => PickupStatusEnum::class,
    ];

    protected $appends = [
        'pickup_status_label',
    ];

    public function getPickupStatusLabelAttribute(): ?string
    {
        return $this->pickup_status?->label();
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
