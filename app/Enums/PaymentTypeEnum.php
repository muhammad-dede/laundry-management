<?php

namespace App\Enums;

enum PaymentTypeEnum: string
{
    case FULL_PAYMENT = 'FULL_PAYMENT';
    case PAY_LATER = 'PAY_LATER';

    public function label(): string
    {
        return match ($this) {
            self::FULL_PAYMENT => 'Bayar Lunas',
            self::PAY_LATER => 'Bayar di Akhir',
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
