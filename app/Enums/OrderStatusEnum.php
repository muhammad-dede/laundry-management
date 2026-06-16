<?php

namespace App\Enums;

enum OrderStatusEnum: string
{
    case QUEUED = 'QUEUED';
    case PROCESS = 'PROCESS';
    case READY = 'READY';
    case COMPLETED = 'COMPLETED';

    public function label(): string
    {
        return match ($this) {
            self::QUEUED => 'Antrian',
            self::PROCESS => 'Proses',
            self::READY => 'Siap Diambil',
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
