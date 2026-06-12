<?php

namespace App\Enums;

enum UnitTypeEnum: string
{
    case KG = 'KG';
    case PCS = 'PCS';
    case SET = 'SET';
    case PAIR = 'PAIR';
    case SHEET = 'SHEET';
    case METER = 'METER';
    case UNIT = 'UNIT';
    case PACKAGE = 'PACKAGE';
    case TRIP = 'TRIP';
    case DAY = 'DAY';
    case MONTH = 'MONTH';
    case TIME = 'TIME';

    public function label(): string
    {
        return match ($this) {
            self::KG => 'Kg',
            self::PCS => 'PCS',
            self::SET => 'Set',
            self::PAIR => 'Pasang',
            self::SHEET => 'Lembar',
            self::METER => 'Meter',
            self::UNIT => 'Unit',
            self::PACKAGE => 'Paket',
            self::TRIP => 'Rit',
            self::DAY => 'Hari',
            self::MONTH => 'Bulan',
            self::TIME => 'Kali',
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
