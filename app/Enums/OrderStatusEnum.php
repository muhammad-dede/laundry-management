<?php

namespace App\Enums;

enum OrderStatusEnum: string
{
    case WAITING_PICKUP = 'WAITING_PICKUP';
    case QUEUED = 'QUEUED';
    case PROCESS = 'PROCESS';
    case READY = 'READY';
    case ON_DELIVERY = 'ON_DELIVERY';
    case COMPLETED = 'COMPLETED';

    public function label(): string
    {
        return match ($this) {
            self::WAITING_PICKUP => 'Menunggu Pengambilan',
            self::QUEUED => 'Antrian',
            self::PROCESS => 'Proses',
            self::READY => 'Siap Diambil',
            self::ON_DELIVERY => 'Dalam Pengiriman',
            self::COMPLETED => 'Selesai',
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
