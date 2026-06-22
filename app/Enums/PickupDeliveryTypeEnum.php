<?php

namespace App\Enums;

enum PickupDeliveryTypeEnum: string
{
    case SELF = 'SELF';
    case PICKUP = 'PICKUP';
    case DELIVERY = 'DELIVERY';
    case PICKUP_DELIVERY = 'PICKUP_DELIVERY';

    public function label(): string
    {
        return match ($this) {
            self::SELF => 'Datang Langsung',
            self::PICKUP => 'Pickup',
            self::DELIVERY => 'Delivery',
            self::PICKUP_DELIVERY => 'Pickup & Delivery'
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->map(fn($item) => [
                'value' => $item->value,
                'label' => $item->label(),
            ])
            ->values()
            ->toArray();
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
