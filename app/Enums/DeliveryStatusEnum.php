<?php

namespace App\Enums;

enum DeliveryStatusEnum: string
{
    case PENDING = 'PENDING';
    case ASSIGNED = 'ASSIGNED';
    case ON_THE_WAY = 'ON_THE_WAY';
    case DELIVERED = 'DELIVERED';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::ASSIGNED => 'Kurir Ditugaskan',
            self::ON_THE_WAY => 'Dalam Perjalanan',
            self::DELIVERED => 'Terkirim',
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
