<?php

namespace App\Enum;

enum DreamFulfillmentStatus: string
{
    case RESERVED = 'reserved';
    case ORDERED = 'ordered';
    case DELIVERED = 'delivered';
    case CONFIRMED = 'confirmed';
    case CANCELLED = 'cancelled';
    case PENDING = 'pending';

    public static function choices(): array
    {
        return [
            'Oczekujące' => self::PENDING->value,
            'Zarezerwowane' => self::RESERVED->value,
            'Zamówione' => self::ORDERED->value,
            'Dostarczone' => self::DELIVERED->value,
            'Potwierdzone' => self::CONFIRMED->value,
            'Anulowane' => self::CANCELLED->value,
        ];
    }

    public function label(): string
    {
        return match($this) {
            self::RESERVED => 'Zarezerwowane',
            self::ORDERED => 'Zamówione',
            self::DELIVERED => 'Dostarczone',
            self::CONFIRMED => 'Potwierdzone',
            self::CANCELLED => 'Anulowane',
            self::PENDING => 'Oczekujące',
        };
    }
}
