<?php

namespace App\Enums;

enum PickupStatusEnum: string
{
    case PENDING = 'PENDING';
    case ASSIGNED = 'ASSIGNED';
    case ON_THE_WAY = 'ON_THE_WAY';
    case PICKED_UP = 'PICKED_UP';
    case RECEIVED = 'RECEIVED';
    case CANCELLED = 'CANCELLED';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Menunggu Penugasan',
            self::ASSIGNED => 'Kurir Ditugaskan',
            self::ON_THE_WAY => 'Dalam Perjalanan',
            self::PICKED_UP => 'Cucian Sudah Dijemput',
            self::RECEIVED => 'Cucian Diterima Laundry',
            self::CANCELLED => 'Dibatalkan',
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
