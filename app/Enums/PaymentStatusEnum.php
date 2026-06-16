<?php

namespace App\Enums;

enum PaymentStatusEnum: string
{
    case UNPAID = 'UNPAID';
    case PAID = 'PAID';

    public function label(): string
    {
        return match ($this) {
            self::UNPAID => 'Belum Lunas',
            self::PAID => 'Lunas',
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
