<?php

namespace App\Models;

use App\Enums\UnitTypeEnum;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $guarded = [];

    protected $casts = [
        'unit_type' => UnitTypeEnum::class,
    ];

    protected $appends = [
        'unit_type_label',
    ];

    public function getUnitTypeLabelAttribute(): ?string
    {
        return $this->unit_type?->label();
    }
}
