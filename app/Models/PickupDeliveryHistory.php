<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PickupDeliveryHistory extends Model
{
    protected $guarded = [];

    public function pickupDelivery()
    {
        return $this->belongsTo(PickupDelivery::class, 'pickup_delivery_id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
