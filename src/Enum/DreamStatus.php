<?php

namespace App\Enum;

enum DreamStatus: string
{
    case PENDING = 'pending';
    case VERIFIED = 'verified';
    case IN_PROGRESS = 'in_progress';
    case FULFILLED = 'fulfilled';
    case CANCELLED = 'cancelled';

    public static function choices(): array
    {
        return [
            'Oczekujące' => self::PENDING->value,
            'Zweryfikowane' => self::VERIFIED->value,
            'W realizacji' => self::IN_PROGRESS->value,
            'Zrealizowane' => self::FULFILLED->value,
            'Anulowane' => self::CANCELLED->value,
        ];
    }

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Oczekujące',
            self::VERIFIED => 'Zweryfikowane',
            self::IN_PROGRESS => 'W realizacji',
            self::FULFILLED => 'Zrealizowane',
            self::CANCELLED => 'Anulowane',
        };
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
