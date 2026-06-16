<?php

namespace App\Enums;

enum PaymentMethodEnum: string
{
    case CASH = 'CASH';
    case QRIS = 'QRIS';
    case TRANSFER = 'TRANSFER';

    public function label(): string
    {
        return match ($this) {
            self::CASH => 'CASH',
            self::QRIS => 'QRIS',
            self::TRANSFER => 'TRANSFER',
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
